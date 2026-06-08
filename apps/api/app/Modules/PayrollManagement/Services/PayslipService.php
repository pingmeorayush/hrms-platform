<?php

namespace App\Modules\PayrollManagement\Services;

use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PayslipService
{
    public function __construct(
        private readonly PayslipAccessScopeService $accessScopeService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function searchPayslips(User $actor, array $filters): LengthAwarePaginator
    {
        $payslips = $this->accessScopeService->searchPayslips($actor, $filters);

        $this->auditLogger->record(
            eventType: 'payroll.payslip.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'result_count' => $payslips->count(),
            ],
            entityType: 'payslip',
            entityId: null,
        );

        return $payslips;
    }

    public function generateForRun(User $actor, PayrollRun $run): Collection
    {
        return DB::transaction(function () use ($actor, $run): Collection {
            $run->loadMissing(['company', 'payrollPeriod', 'items.employee']);

            if ($run->status !== 'locked') {
                throw ValidationException::withMessages([
                    'status' => ['Payslips can only be generated for locked payroll runs.'],
                ]);
            }

            $items = $run->items()
                ->with(['employee', 'employeeCompensation'])
                ->where('status', 'calculated')
                ->orderBy('employee_id')
                ->orderBy('id')
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'status' => ['The payroll run must have calculated payroll items before payslips can be generated.'],
                ]);
            }

            $this->purgeRunPayslips($run);

            $generatedAt = now();
            $companySnapshot = [
                'name' => $run->company?->name,
                'currency' => $run->company?->currency,
                'timezone' => $run->company?->timezone,
            ];

            $payslips = $items->map(function (PayrollItem $item) use ($actor, $run, $generatedAt, $companySnapshot): Payslip {
                $employeeSnapshot = [
                    'id' => $item->employee?->id,
                    'employee_code' => $item->employee?->employee_code,
                    'full_name' => $item->employee?->full_name,
                    'email' => $item->employee?->email,
                ];

                $fileName = sprintf(
                    '%s-%s-%s-payslip.html',
                    Str::lower((string) $item->employee?->employee_code),
                    $run->start_date?->format('Ymd'),
                    $run->end_date?->format('Ymd'),
                );

                $renderedContent = view('payroll.payslip', [
                    'companySnapshot' => $companySnapshot,
                    'employeeSnapshot' => $employeeSnapshot,
                    'payslipNumber' => $this->makeSlipNumber($run, $item),
                    'periodName' => $run->payrollPeriod?->name,
                    'startDate' => $run->start_date?->toDateString(),
                    'endDate' => $run->end_date?->toDateString(),
                    'payrollDate' => $run->payrollPeriod?->payroll_date?->toDateString(),
                    'currency' => $run->company?->currency ?? $item->employeeCompensation?->currency ?? 'INR',
                    'grossSalary' => (float) $item->gross_salary,
                    'totalEarnings' => (float) $item->total_earnings,
                    'totalDeductions' => (float) $item->total_deductions,
                    'netSalary' => (float) $item->net_salary,
                    'employerCost' => (float) $item->employer_cost,
                    'earningsBreakdown' => $item->earnings_breakdown ?? [],
                    'deductionsBreakdown' => $item->deductions_breakdown ?? [],
                    'employerContributionBreakdown' => $item->employer_contribution_breakdown ?? [],
                    'generatedAt' => $generatedAt->toIso8601String(),
                ])->render();

                return Payslip::query()->create([
                    'company_id' => $run->company_id,
                    'payroll_run_id' => $run->id,
                    'payroll_period_id' => $run->payroll_period_id,
                    'payroll_item_id' => $item->id,
                    'employee_id' => $item->employee_id,
                    'employee_compensation_id' => $item->employee_compensation_id,
                    'slip_number' => $this->makeSlipNumber($run, $item),
                    'status' => 'generated',
                    'currency' => $run->company?->currency ?? $item->employeeCompensation?->currency ?? 'INR',
                    'start_date' => $run->start_date?->toDateString(),
                    'end_date' => $run->end_date?->toDateString(),
                    'payroll_date' => $run->payrollPeriod?->payroll_date?->toDateString(),
                    'file_name' => $fileName,
                    'gross_salary' => $item->gross_salary,
                    'total_earnings' => $item->total_earnings,
                    'total_deductions' => $item->total_deductions,
                    'net_salary' => $item->net_salary,
                    'employer_cost' => $item->employer_cost,
                    'earnings_breakdown' => $item->earnings_breakdown,
                    'deductions_breakdown' => $item->deductions_breakdown,
                    'employer_contribution_breakdown' => $item->employer_contribution_breakdown,
                    'employee_snapshot' => $employeeSnapshot,
                    'company_snapshot' => $companySnapshot,
                    'rendered_format' => 'html',
                    'rendered_content' => $renderedContent,
                    'checksum_sha256' => hash('sha256', $renderedContent),
                    'generated_at' => $generatedAt,
                    'created_by_user_id' => $actor->id,
                    'updated_by_user_id' => $actor->id,
                ]);
            });

            $this->auditLogger->record(
                eventType: 'payroll.payslip.generated',
                actor: $actor,
                metadata: [
                    'payroll_run_id' => $run->id,
                    'generated_count' => $payslips->count(),
                ],
                entityType: 'payroll_run',
                entityId: (string) $run->id,
            );

            return $payslips->load(['employee', 'payrollRun.payrollPeriod']);
        });
    }

    public function showPayslip(User $actor, int $payslipId): Payslip
    {
        $payslip = $this->accessScopeService->resolveAccessiblePayslip($actor, $payslipId, ['employee', 'payrollRun.payrollPeriod']);

        $this->auditLogger->record(
            eventType: 'payroll.payslip.viewed',
            actor: $actor,
            metadata: [
                'payslip_id' => $payslip->id,
                'payroll_run_id' => $payslip->payroll_run_id,
                'employee_id' => $payslip->employee_id,
            ],
            entityType: 'payslip',
            entityId: (string) $payslip->id,
        );

        return $payslip;
    }

    public function downloadPayslip(User $actor, int $payslipId): Response
    {
        $payslip = $this->accessScopeService->resolveAccessiblePayslip($actor, $payslipId);

        $this->auditLogger->record(
            eventType: 'payroll.payslip.downloaded',
            actor: $actor,
            metadata: [
                'payslip_id' => $payslip->id,
                'payroll_run_id' => $payslip->payroll_run_id,
                'employee_id' => $payslip->employee_id,
                'file_name' => $payslip->file_name,
            ],
            entityType: 'payslip',
            entityId: (string) $payslip->id,
        );

        return response($payslip->rendered_content, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$payslip->file_name.'"',
        ]);
    }

    public function purgeRunPayslips(PayrollRun $run): int
    {
        return Payslip::query()
            ->where('payroll_run_id', $run->id)
            ->delete();
    }

    private function makeSlipNumber(PayrollRun $run, PayrollItem $item): string
    {
        return sprintf('PSL-%d-%s', $run->id, strtoupper((string) $item->employee?->employee_code));
    }
}
