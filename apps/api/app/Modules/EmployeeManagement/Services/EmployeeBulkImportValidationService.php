<?php

namespace App\Modules\EmployeeManagement\Services;

use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator as LaravelValidator;

/**
 * @phpstan-type BulkImportRowData array<string, mixed>
 * @phpstan-type BulkImportParsedRow array{row_number: int, data: BulkImportRowData}
 * @phpstan-type BulkImportDuplicateContext array{
 *   emails: array<string, int>,
 *   employee_codes: array<string, int>
 * }
 * @phpstan-type BulkImportValidationRowResult array{
 *   row_number: int,
 *   status: string,
 *   errors: array<string, array<int, string>>
 * }
 * @phpstan-type BulkImportValidationSummary array{
 *   processed: int,
 *   success_count: int,
 *   failed_count: int,
 *   rows: list<BulkImportValidationRowResult>
 * }
 */
class EmployeeBulkImportValidationService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly EmployeeCreationRules $employeeCreationRules,
        private readonly EmployeeCodeService $employeeCodeService,
    ) {}

    /**
     * @return list<BulkImportParsedRow>
     */
    public function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'rb');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => ['The uploaded CSV file could not be read.'],
            ]);
        }

        $headerRow = fgetcsv($handle);

        if ($headerRow === false) {
            fclose($handle);

            throw ValidationException::withMessages([
                'file' => ['The uploaded CSV file must include a header row.'],
            ]);
        }

        $headers = array_map(fn ($header): string => $this->normalizeHeader((string) $header), $headerRow);
        $rows = [];
        $rowNumber = 1;

        while (($csvRow = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $row = [];

            foreach ($headers as $index => $header) {
                $row[$header] = $csvRow[$index] ?? null;
            }

            $normalized = $this->normalizeRow($row);

            if ($this->isBlankRow($normalized)) {
                continue;
            }

            $rows[] = [
                'row_number' => $rowNumber,
                'data' => $normalized,
            ];
        }

        fclose($handle);

        if ($rows === []) {
            throw ValidationException::withMessages([
                'file' => ['The uploaded CSV file must include at least one non-empty data row.'],
            ]);
        }

        return $rows;
    }

    /**
     * @param  list<BulkImportRowData|BulkImportParsedRow>  $rows
     * @return BulkImportValidationSummary
     */
    public function validateRows(User $actor, array $rows, string $source = 'rows'): array
    {
        $preparedRows = $this->prepareRows($rows);
        $companyId = $actor->company_id;
        $duplicateContext = $this->buildDuplicateContext($preparedRows);

        $results = $preparedRows
            ->map(function (array $row) use ($companyId): array {
                $validator = Validator::make(
                    $row['data'],
                    $this->employeeCreationRules->rulesForCompany($companyId),
                );

                return [
                    'row_number' => $row['row_number'],
                    'validator' => $validator,
                    'data' => $row['data'],
                ];
            })
            ->map(function (array $row) use ($companyId, $duplicateContext): array {
                /** @var LaravelValidator $validator */
                $validator = $row['validator'];
                $data = $row['data'];

                $validator->after(function (LaravelValidator $validator) use ($companyId, $data, $duplicateContext): void {
                    $this->employeeCreationRules->applyCodePolicyValidation($validator, $companyId, $data);
                    $this->applyImportDuplicateValidation($validator, $data, $duplicateContext, $companyId);
                });

                $validator->fails();

                return [
                    'row_number' => $row['row_number'],
                    'status' => $validator->errors()->isEmpty() ? 'valid' : 'invalid',
                    'errors' => $validator->errors()->toArray(),
                ];
            })
            ->values();

        $processed = $results->count();
        $successCount = $results->where('status', 'valid')->count();
        $failedCount = $processed - $successCount;

        $this->auditLogger->record(
            eventType: 'employee.bulk_import.validated',
            actor: $actor,
            metadata: [
                'source' => $source,
                'processed' => $processed,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
            ],
            entityType: 'employee_bulk_import',
            entityId: (string) $actor->company_id,
        );

        return [
            'processed' => $processed,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'rows' => $results->all(),
        ];
    }

    /**
     * @param  list<BulkImportRowData|BulkImportParsedRow>  $rows
     * @return Collection<int, BulkImportParsedRow>
     */
    private function prepareRows(array $rows): Collection
    {
        return collect($rows)
            ->values()
            ->map(function (array $row, int $index): array {
                if (array_key_exists('row_number', $row) && array_key_exists('data', $row)) {
                    return [
                        'row_number' => (int) $row['row_number'],
                        'data' => $this->normalizeRow($row['data']),
                    ];
                }

                return [
                    'row_number' => $index + 1,
                    'data' => $this->normalizeRow($row),
                ];
            })
            ->filter(fn (array $row): bool => ! $this->isBlankRow($row['data']))
            ->values();
    }

    /**
     * @param  Collection<int, BulkImportParsedRow>  $rows
     * @return BulkImportDuplicateContext
     */
    private function buildDuplicateContext(Collection $rows): array
    {
        return [
            'emails' => $rows
                ->map(fn (array $row): ?string => $this->normalizeEmail($row['data']['email'] ?? null))
                ->filter()
                ->countBy()
                ->all(),
            'employee_codes' => $rows
                ->map(fn (array $row): ?string => $this->normalizeEmployeeCode($row['data']['employee_code'] ?? null))
                ->filter()
                ->countBy()
                ->all(),
        ];
    }

    /**
     * @param  BulkImportRowData  $data
     * @param  BulkImportDuplicateContext  $duplicateContext
     */
    private function applyImportDuplicateValidation(
        LaravelValidator $validator,
        array $data,
        array $duplicateContext,
        int $companyId,
    ): void {
        $email = $this->normalizeEmail($data['email'] ?? null);

        if ($email !== null && ($duplicateContext['emails'][$email] ?? 0) > 1) {
            $validator->errors()->add('email', 'Email is duplicated within the import payload.');
        }

        $employeeCode = $this->normalizeEmployeeCode($data['employee_code'] ?? null);

        if (
            $employeeCode !== null
            && $this->employeeCodeService->isManualMode($companyId)
            && ($duplicateContext['employee_codes'][$employeeCode] ?? 0) > 1
        ) {
            $validator->errors()->add('employee_code', 'Employee code is duplicated within the import payload.');
        }
    }

    /**
     * @param  array<array-key, mixed>  $row
     * @return BulkImportRowData
     */
    private function normalizeRow(array $row): array
    {
        return collect($row)
            ->mapWithKeys(function ($value, $key): array {
                $normalizedKey = is_string($key) ? $this->normalizeHeader($key) : (string) $key;

                if (is_string($value)) {
                    $value = trim($value);
                    $value = $value === '' ? null : $value;
                }

                return [$normalizedKey => $value];
            })
            ->all();
    }

    private function normalizeHeader(string $header): string
    {
        return (string) Str::of($header)
            ->trim()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_');
    }

    private function normalizeEmail(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return Str::lower(trim($value));
    }

    private function normalizeEmployeeCode(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return Str::upper(trim($value));
    }

    /**
     * @param  BulkImportRowData  $row
     */
    private function isBlankRow(array $row): bool
    {
        return collect($row)
            ->filter(fn ($value) => ! ($value === null || $value === ''))
            ->isEmpty();
    }
}
