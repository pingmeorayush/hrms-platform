<?php

namespace App\Modules\RecruitmentManagement\Services;

use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\JobRequisition;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CandidateService
{
    public function __construct(
        private readonly CandidateAccessScopeService $accessScopeService,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Candidate>
     */
    public function search(User $actor, array $filters): LengthAwarePaginator
    {
        $query = $this->accessScopeService
            ->candidatesQuery($actor)
            ->when(
                array_key_exists('job_requisition_id', $filters),
                fn (Builder $builder) => $builder->where('job_requisition_id', $filters['job_requisition_id']),
            )
            ->when(
                array_key_exists('recruiter_user_id', $filters),
                fn (Builder $builder) => $builder->where('recruiter_user_id', $filters['recruiter_user_id']),
            )
            ->when(
                array_key_exists('current_stage', $filters),
                fn (Builder $builder) => $builder->where('current_stage', $filters['current_stage']),
            )
            ->when(
                array_key_exists('status', $filters),
                fn (Builder $builder) => $builder->where('status', $filters['status']),
            )
            ->when(
                array_key_exists('q', $filters),
                function (Builder $builder) use ($filters): void {
                    $term = trim((string) $filters['q']);

                    $builder->where(function (Builder $searchQuery) use ($term): void {
                        $searchQuery->where('candidate_code', 'like', '%'.$term.'%')
                            ->orWhere('first_name', 'like', '%'.$term.'%')
                            ->orWhere('last_name', 'like', '%'.$term.'%')
                            ->orWhere('email', 'like', '%'.$term.'%')
                            ->orWhere('current_company', 'like', '%'.$term.'%')
                            ->orWhere('current_title', 'like', '%'.$term.'%');
                    });
                },
            )
            ->orderByDesc('updated_at')
            ->orderByDesc('id');

        $results = $query->paginate($filters['per_page'] ?? 15);

        $this->auditLogger->record(
            eventType: 'recruitment.candidate.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'candidate_count' => $results->total(),
            ],
            entityType: 'candidate',
            entityId: null,
        );

        return $results;
    }

    public function findForView(User $actor, int $candidateId): Candidate
    {
        $candidate = $this->accessScopeService->resolveAccessibleCandidate($actor, $candidateId);

        $this->auditLogger->record(
            eventType: 'recruitment.candidate.viewed',
            actor: $actor,
            metadata: [
                'candidate_id' => $candidate->id,
                'job_requisition_id' => $candidate->job_requisition_id,
                'current_stage' => $candidate->current_stage,
                'status' => $candidate->status,
            ],
            entityType: 'candidate',
            entityId: (string) $candidate->id,
        );

        return $candidate;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(User $actor, array $payload): Candidate
    {
        return DB::transaction(function () use ($actor, $payload): Candidate {
            $requisition = $this->resolveApprovedRequisition((int) $payload['job_requisition_id']);
            $normalizedEmail = $this->normalizeEmail((string) $payload['email']);

            $this->assertNoDuplicateEmail($normalizedEmail, null);

            $stage = $payload['current_stage'] ?? 'applied';
            $status = $this->deriveStatusFromStage($stage);

            $candidate = Candidate::query()->create([
                'company_id' => $actor->company_id,
                'job_requisition_id' => $requisition->id,
                'candidate_code' => $this->resolveCandidateCode(),
                'recruiter_user_id' => $payload['recruiter_user_id'] ?? $requisition->recruiter_user_id,
                'first_name' => trim((string) $payload['first_name']),
                'last_name' => array_key_exists('last_name', $payload) ? (trim((string) $payload['last_name']) ?: null) : null,
                'email' => $normalizedEmail,
                'phone' => $payload['phone'] ?? null,
                'source' => $payload['source'],
                'current_stage' => $stage,
                'status' => $status,
                'stage_entered_at' => now(),
                'total_experience_years' => $payload['total_experience_years'] ?? null,
                'notice_period_days' => $payload['notice_period_days'] ?? null,
                'current_company' => $payload['current_company'] ?? null,
                'current_title' => $payload['current_title'] ?? null,
                'summary' => $payload['summary'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $candidate->stageTransitions()->create([
                'company_id' => $actor->company_id,
                'from_stage' => null,
                'to_stage' => $stage,
                'resulting_status' => $status,
                'comment' => 'Initial candidate entry.',
                'transitioned_by_user_id' => $actor->id,
                'transitioned_at' => now(),
            ]);

            $this->auditLogger->record(
                eventType: 'recruitment.candidate.created',
                actor: $actor,
                metadata: [
                    'candidate_id' => $candidate->id,
                    'candidate_code' => $candidate->candidate_code,
                    'job_requisition_id' => $candidate->job_requisition_id,
                    'recruiter_user_id' => $candidate->recruiter_user_id,
                    'current_stage' => $candidate->current_stage,
                    'status' => $candidate->status,
                    'source' => $candidate->source,
                ],
                entityType: 'candidate',
                entityId: (string) $candidate->id,
            );

            return $this->loadCandidate($candidate);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(User $actor, int $candidateId, array $payload): Candidate
    {
        $candidate = $this->accessScopeService->resolveAccessibleCandidate($actor, $candidateId);

        if (! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'candidate' => ['Updating candidate details requires recruitment management permission.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $candidate, $payload): Candidate {
            $before = $candidate->only([
                'recruiter_user_id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'source',
                'total_experience_years',
                'notice_period_days',
                'current_company',
                'current_title',
            ]);

            if (array_key_exists('email', $payload)) {
                $normalizedEmail = $this->normalizeEmail((string) $payload['email']);
                $this->assertNoDuplicateEmail($normalizedEmail, $candidate->id);
                $candidate->email = $normalizedEmail;
            }

            $candidate->fill([
                'recruiter_user_id' => array_key_exists('recruiter_user_id', $payload) ? $payload['recruiter_user_id'] : $candidate->recruiter_user_id,
                'first_name' => array_key_exists('first_name', $payload) ? trim((string) $payload['first_name']) : $candidate->first_name,
                'last_name' => array_key_exists('last_name', $payload) ? (trim((string) $payload['last_name']) ?: null) : $candidate->last_name,
                'phone' => array_key_exists('phone', $payload) ? $payload['phone'] : $candidate->phone,
                'source' => $payload['source'] ?? $candidate->source,
                'total_experience_years' => array_key_exists('total_experience_years', $payload) ? $payload['total_experience_years'] : $candidate->total_experience_years,
                'notice_period_days' => array_key_exists('notice_period_days', $payload) ? $payload['notice_period_days'] : $candidate->notice_period_days,
                'current_company' => array_key_exists('current_company', $payload) ? $payload['current_company'] : $candidate->current_company,
                'current_title' => array_key_exists('current_title', $payload) ? $payload['current_title'] : $candidate->current_title,
                'summary' => array_key_exists('summary', $payload) ? $payload['summary'] : $candidate->summary,
                'notes' => array_key_exists('notes', $payload) ? $payload['notes'] : $candidate->notes,
                'updated_by_user_id' => $actor->id,
            ]);
            $candidate->save();

            $this->auditLogger->record(
                eventType: 'recruitment.candidate.updated',
                actor: $actor,
                metadata: [
                    'candidate_id' => $candidate->id,
                    'before' => $before,
                    'after' => $candidate->only([
                        'recruiter_user_id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone',
                        'source',
                        'total_experience_years',
                        'notice_period_days',
                        'current_company',
                        'current_title',
                    ]),
                ],
                entityType: 'candidate',
                entityId: (string) $candidate->id,
            );

            return $this->loadCandidate($candidate);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function uploadResume(User $actor, int $candidateId, UploadedFile $file, array $payload): CandidateResume
    {
        $candidate = $this->accessScopeService->resolveAccessibleCandidate($actor, $candidateId);

        if (! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'candidate' => ['Uploading candidate resume versions requires recruitment management permission.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $candidate, $file, $payload): CandidateResume {
            CandidateResume::query()
                ->where('candidate_id', $candidate->id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            $disk = (string) config('recruitment.resume_disk', 'local');
            $version = ((int) CandidateResume::query()->where('candidate_id', $candidate->id)->max('version_number')) + 1;
            $storedFileName = (string) Str::uuid().'.'.$file->getClientOriginalExtension();
            $directory = 'companies/'.$candidate->company_id.'/recruitment/candidates/'.$candidate->id.'/resumes';
            $path = $file->storeAs($directory, $storedFileName, ['disk' => $disk]);

            $resume = CandidateResume::query()->create([
                'company_id' => $candidate->company_id,
                'candidate_id' => $candidate->id,
                'version_number' => $version,
                'is_current' => true,
                'original_file_name' => $file->getClientOriginalName(),
                'disk' => $disk,
                'file_path' => $path,
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'file_size_bytes' => $file->getSize(),
                'checksum_sha256' => hash_file('sha256', $file->getRealPath()),
                'notes' => $payload['notes'] ?? null,
                'uploaded_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: 'recruitment.candidate.resume_uploaded',
                actor: $actor,
                metadata: [
                    'candidate_id' => $candidate->id,
                    'candidate_resume_id' => $resume->id,
                    'version_number' => $resume->version_number,
                    'original_file_name' => $resume->original_file_name,
                ],
                entityType: 'candidate_resume',
                entityId: (string) $resume->id,
            );

            return $resume->load('uploader');
        });
    }

    public function downloadResume(User $actor, int $candidateId, int $candidateResumeId): StreamedResponse
    {
        $candidate = $this->accessScopeService->resolveAccessibleCandidate($actor, $candidateId);
        $resume = $candidate->resumes()->findOrFail($candidateResumeId);

        $this->auditLogger->record(
            eventType: 'recruitment.candidate.resume_downloaded',
            actor: $actor,
            metadata: [
                'candidate_id' => $candidate->id,
                'candidate_resume_id' => $resume->id,
                'version_number' => $resume->version_number,
                'original_file_name' => $resume->original_file_name,
            ],
            entityType: 'candidate_resume',
            entityId: (string) $resume->id,
        );

        return Storage::disk($resume->disk)->download($resume->file_path, $resume->original_file_name);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function transitionStage(User $actor, int $candidateId, array $payload): Candidate
    {
        $candidate = $this->accessScopeService->resolveAccessibleCandidate($actor, $candidateId);

        if (! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'candidate' => ['Changing candidate pipeline stage requires recruitment management permission.'],
            ]);
        }

        if ($candidate->current_stage === $payload['to_stage']) {
            throw ValidationException::withMessages([
                'to_stage' => ['The candidate is already in the requested stage.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $candidate, $payload): Candidate {
            $resultingStatus = $this->deriveStatusFromStage($payload['to_stage']);

            $candidate->stageTransitions()->create([
                'company_id' => $candidate->company_id,
                'from_stage' => $candidate->current_stage,
                'to_stage' => $payload['to_stage'],
                'resulting_status' => $resultingStatus,
                'comment' => $payload['comment'] ?? null,
                'transitioned_by_user_id' => $actor->id,
                'transitioned_at' => now(),
            ]);

            $candidate->forceFill([
                'current_stage' => $payload['to_stage'],
                'status' => $resultingStatus,
                'stage_entered_at' => now(),
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'recruitment.candidate.stage_transitioned',
                actor: $actor,
                metadata: [
                    'candidate_id' => $candidate->id,
                    'from_stage' => $candidate->getOriginal('current_stage'),
                    'to_stage' => $candidate->current_stage,
                    'resulting_status' => $candidate->status,
                    'comment' => $payload['comment'] ?? null,
                ],
                entityType: 'candidate',
                entityId: (string) $candidate->id,
            );

            return $this->loadCandidate($candidate);
        });
    }

    private function resolveApprovedRequisition(int $jobRequisitionId): JobRequisition
    {
        $requisition = JobRequisition::query()->findOrFail($jobRequisitionId);

        if ($requisition->status !== 'approved') {
            throw ValidationException::withMessages([
                'job_requisition_id' => ['Candidates can only be tracked against approved requisitions in the current recruitment baseline.'],
            ]);
        }

        return $requisition;
    }

    private function assertNoDuplicateEmail(string $email, ?int $ignoreCandidateId): void
    {
        $existingCandidate = Candidate::query()
            ->where('email', $email)
            ->when(
                $ignoreCandidateId !== null,
                fn (Builder $builder) => $builder->where('id', '!=', $ignoreCandidateId),
            )
            ->first();

        if (! $existingCandidate) {
            return;
        }

        throw ValidationException::withMessages([
            'email' => ['A candidate with this email already exists in this tenant (candidate #'.$existingCandidate->id.').'],
        ]);
    }

    private function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }

    private function deriveStatusFromStage(string $stage): string
    {
        return match ($stage) {
            'hired' => 'hired',
            'rejected' => 'rejected',
            'withdrawn' => 'withdrawn',
            default => 'active',
        };
    }

    private function resolveCandidateCode(): string
    {
        $nextOrdinal = ((int) Candidate::query()->lockForUpdate()->max('id')) + 1;

        return sprintf('CAN-%04d', $nextOrdinal);
    }

    private function loadCandidate(Candidate $candidate): Candidate
    {
        return $candidate->load([
            'requisition',
            'recruiter',
            'resumes.uploader',
            'stageTransitions.actor',
        ])->loadCount('resumes');
    }
}
