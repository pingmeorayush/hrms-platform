<?php

namespace App\Modules\ReportingAnalytics\Services;

use App\Models\ReportDataset;
use App\Models\SavedReportView;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * @phpstan-type SavedViewPayload array{
 *   dataset_key?: string,
 *   name?: string,
 *   description?: string|null,
 *   status?: string,
 *   share_scope?: string,
 *   shared_role_names?: array<int, string>,
 *   filters?: array<string, mixed>,
 *   filter_operators?: array<string, string>,
 *   sort_by?: string,
 *   sort_direction?: string,
 *   drilldown_path?: string,
 *   presentation_preferences?: array<string, mixed>
 * }
 */
class ReportingSavedViewService
{
    public function __construct(
        private readonly ReportingAccessScopeService $accessScopeService,
        private readonly ReportingQueryService $reportingQueryService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function searchViews(User $actor, array $filters): LengthAwarePaginator
    {
        $query = SavedReportView::query()
            ->with(['reportDataset', 'owner', 'createdBy', 'updatedBy'])
            ->where('company_id', $actor->company_id)
            ->orderByDesc('updated_at')
            ->orderByDesc('id');

        if (is_string($filters['dataset_key'] ?? null) && $filters['dataset_key'] !== '') {
            $query->whereHas('reportDataset', fn ($builder) => $builder->where('key', $filters['dataset_key']));
        }

        if (is_string($filters['status'] ?? null) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        } elseif (! $this->canManageAllViews($actor)) {
            $query->where('status', 'active');
        }

        if (is_string($filters['share_scope'] ?? null) && $filters['share_scope'] !== '') {
            $query->where('share_scope', $filters['share_scope']);
        }

        if ((bool) ($filters['owned_by_me'] ?? false)) {
            $query->where('owner_user_id', $actor->id);
        } elseif (! $this->canManageAllViews($actor)) {
            if (! ((bool) ($filters['include_shared'] ?? true))) {
                $query->where('owner_user_id', $actor->id);
            } else {
                $query->where(function ($builder) use ($actor): void {
                    $builder->where('owner_user_id', $actor->id)
                        ->orWhere('share_scope', 'company')
                        ->orWhere('share_scope', 'roles');
                });
            }
        }

        $views = $query->get()
            ->filter(fn (SavedReportView $view): bool => $this->isVisibleToActor($actor, $view))
            ->map(function (SavedReportView $view) use ($actor): SavedReportView {
                $validationActor = $this->validationSubjectForView($actor, $view);

                return $this->attachValidationState($view, $validationActor);
            })
            ->values();

        $perPage = is_numeric($filters['per_page'] ?? null) ? (int) $filters['per_page'] : 20;
        $page = is_numeric($filters['page'] ?? null) ? (int) $filters['page'] : 1;
        $paginator = $this->paginateCollection($views, $perPage, $page);

        $this->auditLogger->record(
            eventType: 'reporting.saved_view.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'result_count' => $paginator->count(),
            ],
            entityType: 'saved_report_view',
            entityId: null,
        );

        return $paginator;
    }

    /**
     * @param  SavedViewPayload  $payload
     */
    public function createView(User $actor, array $payload): SavedReportView
    {
        $dataset = $this->accessScopeService->resolveAccessibleDataset($actor, $payload['dataset_key']);
        $normalized = $this->normalizeSavedViewPayload($payload, $dataset, $actor);

        $view = SavedReportView::query()->create([
            'company_id' => $actor->company_id,
            'report_dataset_id' => $dataset->id,
            'owner_user_id' => $actor->id,
            'view_uuid' => (string) Str::uuid(),
            'name' => $normalized['name'],
            'description' => $normalized['description'] ?? null,
            'status' => 'active',
            'share_scope' => $normalized['share_scope'],
            'shared_role_names' => $normalized['shared_role_names'],
            'filters' => $normalized['filters'],
            'filter_operators' => $normalized['filter_operators'],
            'sort_by' => $normalized['sort_by'],
            'sort_direction' => $normalized['sort_direction'],
            'drilldown_path' => $normalized['drilldown_path'],
            'presentation_preferences' => $normalized['presentation_preferences'],
            'created_by_user_id' => $actor->id,
            'updated_by_user_id' => $actor->id,
        ]);

        $this->auditLogger->record(
            eventType: 'reporting.saved_view.created',
            actor: $actor,
            metadata: [
                'saved_report_view_id' => $view->id,
                'report_dataset_id' => $dataset->id,
                'dataset_key' => $dataset->key,
                'share_scope' => $view->share_scope,
            ],
            entityType: 'saved_report_view',
            entityId: (string) $view->id,
        );

        return $this->attachValidationState(
            $view->load(['reportDataset', 'owner', 'createdBy', 'updatedBy']),
            $actor,
        );
    }

    public function showView(User $actor, int $savedReportViewId): SavedReportView
    {
        $view = SavedReportView::query()
            ->with(['reportDataset', 'owner', 'createdBy', 'updatedBy'])
            ->where('company_id', $actor->company_id)
            ->findOrFail($savedReportViewId);

        if (! $this->canAccessView($actor, $view)) {
            throw new AuthorizationException('You are not allowed to view this saved report view.');
        }

        $validationActor = $this->validationSubjectForView($actor, $view);
        $view = $this->attachValidationState($view, $validationActor);

        if (! $this->canManageAllViews($actor) && $view->owner_user_id !== $actor->id && ($view->validation_state['status'] ?? null) !== 'valid') {
            throw new AuthorizationException('This saved report view is no longer available in your current reporting scope.');
        }

        $this->auditLogger->record(
            eventType: 'reporting.saved_view.viewed',
            actor: $actor,
            metadata: [
                'saved_report_view_id' => $view->id,
                'report_dataset_id' => $view->report_dataset_id,
                'share_scope' => $view->share_scope,
            ],
            entityType: 'saved_report_view',
            entityId: (string) $view->id,
        );

        return $view;
    }

    /**
     * @param  SavedViewPayload  $payload
     */
    public function updateView(User $actor, int $savedReportViewId, array $payload): SavedReportView
    {
        $view = SavedReportView::query()
            ->with(['owner', 'reportDataset'])
            ->where('company_id', $actor->company_id)
            ->findOrFail($savedReportViewId);

        $this->assertCanManageView($actor, $view);

        $effectiveDataset = array_key_exists('dataset_key', $payload)
            ? $this->accessScopeService->resolveAccessibleDataset($this->validationSubjectForView($actor, $view), $payload['dataset_key'])
            : $view->reportDataset;

        if (! $effectiveDataset instanceof ReportDataset) {
            throw ValidationException::withMessages([
                'dataset_key' => ['The saved report view must reference a valid reporting dataset.'],
            ]);
        }

        $normalized = $this->normalizeSavedViewPayload(
            $payload + [
                'name' => $payload['name'] ?? $view->name,
            ],
            $effectiveDataset,
            $this->validationSubjectForView($actor, $view),
            $view,
        );

        $view->forceFill([
            'report_dataset_id' => $effectiveDataset->id,
            'name' => $normalized['name'],
            'description' => array_key_exists('description', $payload) ? $normalized['description'] : $view->description,
            'status' => $payload['status'] ?? $view->status,
            'share_scope' => $normalized['share_scope'],
            'shared_role_names' => $normalized['shared_role_names'],
            'filters' => $normalized['filters'],
            'filter_operators' => $normalized['filter_operators'],
            'sort_by' => $normalized['sort_by'],
            'sort_direction' => $normalized['sort_direction'],
            'drilldown_path' => $normalized['drilldown_path'],
            'presentation_preferences' => $normalized['presentation_preferences'],
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'reporting.saved_view.updated',
            actor: $actor,
            metadata: [
                'saved_report_view_id' => $view->id,
                'report_dataset_id' => $view->report_dataset_id,
                'status' => $view->status,
                'share_scope' => $view->share_scope,
            ],
            entityType: 'saved_report_view',
            entityId: (string) $view->id,
        );

        return $this->attachValidationState(
            $view->refresh()->load(['reportDataset', 'owner', 'createdBy', 'updatedBy']),
            $this->validationSubjectForView($actor, $view),
        );
    }

    public function archiveView(User $actor, int $savedReportViewId): SavedReportView
    {
        $view = SavedReportView::query()
            ->with(['owner', 'reportDataset'])
            ->where('company_id', $actor->company_id)
            ->findOrFail($savedReportViewId);

        $this->assertCanManageView($actor, $view);

        $view->forceFill([
            'status' => 'archived',
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'reporting.saved_view.archived',
            actor: $actor,
            metadata: [
                'saved_report_view_id' => $view->id,
                'report_dataset_id' => $view->report_dataset_id,
            ],
            entityType: 'saved_report_view',
            entityId: (string) $view->id,
        );

        return $this->attachValidationState(
            $view->refresh()->load(['reportDataset', 'owner', 'createdBy', 'updatedBy']),
            $this->validationSubjectForView($actor, $view),
        );
    }

    public function validationStateForActor(User $actor, SavedReportView $view): array
    {
        if ($view->status !== 'active') {
            return [
                'status' => 'blocked',
                'reason' => 'The saved report view is archived.',
            ];
        }

        try {
            $this->reportingQueryService->query(
                $actor,
                $view->reportDataset->key,
                $this->queryPayloadForView($view) + ['page' => 1, 'per_page' => 1],
                false,
            );

            return [
                'status' => 'valid',
                'reason' => null,
            ];
        } catch (Throwable $exception) {
            return [
                'status' => 'blocked',
                'reason' => $this->flattenExceptionMessage($exception),
            ];
        }
    }

    private function attachValidationState(SavedReportView $view, User $actor): SavedReportView
    {
        $view->setAttribute('validation_state', $this->validationStateForActor($actor, $view));

        return $view;
    }

    private function canManageAllViews(User $actor): bool
    {
        return $actor->canAny(['reporting.manage', 'reporting.certify']);
    }

    private function canAccessView(User $actor, SavedReportView $view): bool
    {
        return $this->canManageAllViews($actor)
            || $view->owner_user_id === $actor->id
            || $this->isSharedToActor($actor, $view);
    }

    private function isVisibleToActor(User $actor, SavedReportView $view): bool
    {
        if (! $this->canAccessView($actor, $view)) {
            return false;
        }

        if ($this->canManageAllViews($actor) || $view->owner_user_id === $actor->id) {
            return true;
        }

        return ($this->validationStateForActor($actor, $view)['status'] ?? null) === 'valid';
    }

    private function isSharedToActor(User $actor, SavedReportView $view): bool
    {
        if ($view->status !== 'active') {
            return false;
        }

        if ($view->share_scope === 'company') {
            return true;
        }

        if ($view->share_scope !== 'roles') {
            return false;
        }

        $actorRoleNames = $actor->getRoleNames()->all();
        $sharedRoleNames = collect($view->shared_role_names ?? [])
            ->filter(fn (mixed $value): bool => is_string($value) && $value !== '')
            ->values()
            ->all();

        return array_intersect($actorRoleNames, $sharedRoleNames) !== [];
    }

    private function validationSubjectForView(User $actor, SavedReportView $view): User
    {
        if ($this->canManageAllViews($actor) && $view->owner) {
            return $view->owner;
        }

        return $actor;
    }

    /**
     * @param  SavedViewPayload  $payload
     * @return array{
     *   name: string,
     *   description: string|null,
     *   share_scope: string,
     *   shared_role_names: array<int, string>|null,
     *   filters: array<string, mixed>,
     *   filter_operators: array<string, string>,
     *   sort_by: string|null,
     *   sort_direction: string|null,
     *   drilldown_path: string|null,
     *   presentation_preferences: array<string, mixed>
     * }
     */
    private function normalizeSavedViewPayload(array $payload, ReportDataset $dataset, User $validationActor, ?SavedReportView $existing = null): array
    {
        $shareScope = $payload['share_scope'] ?? $existing?->share_scope ?? 'private';
        $sharedRoleNames = $shareScope === 'roles'
            ? array_values(array_unique(array_filter($payload['shared_role_names'] ?? $existing?->shared_role_names ?? [])))
            : null;

        if ($shareScope === 'roles' && $sharedRoleNames === []) {
            throw ValidationException::withMessages([
                'shared_role_names' => ['At least one shared role is required for role-scoped saved report views.'],
            ]);
        }

        $normalized = [
            'name' => (string) ($payload['name'] ?? $existing?->name),
            'description' => array_key_exists('description', $payload) ? $payload['description'] : $existing?->description,
            'share_scope' => $shareScope,
            'shared_role_names' => $sharedRoleNames,
            'filters' => $payload['filters'] ?? $existing?->filters ?? [],
            'filter_operators' => $payload['filter_operators'] ?? $existing?->filter_operators ?? [],
            'sort_by' => $payload['sort_by'] ?? $existing?->sort_by,
            'sort_direction' => $payload['sort_direction'] ?? $existing?->sort_direction,
            'drilldown_path' => $payload['drilldown_path'] ?? $existing?->drilldown_path,
            'presentation_preferences' => $payload['presentation_preferences'] ?? $existing?->presentation_preferences ?? [],
        ];

        $this->reportingQueryService->query(
            $validationActor,
            $dataset->key,
            $this->queryPayloadFromArray($normalized) + ['page' => 1, 'per_page' => 1],
            false,
        );

        return $normalized;
    }

    /**
     * @param  array{
     *   filters?: array<string, mixed>,
     *   filter_operators?: array<string, string>,
     *   sort_by?: string|null,
     *   sort_direction?: string|null,
     *   drilldown_path?: string|null
     * }  $payload
     * @return array{
     *   filters?: array<string, mixed>,
     *   filter_operators?: array<string, string>,
     *   sort_by?: string,
     *   sort_direction?: string,
     *   drilldown_path?: string
     * }
     */
    private function queryPayloadFromArray(array $payload): array
    {
        return Arr::where(
            Arr::only($payload, ['filters', 'filter_operators', 'sort_by', 'sort_direction', 'drilldown_path']),
            fn (mixed $value): bool => $value !== null,
        );
    }

    /**
     * @return array{
     *   filters?: array<string, mixed>,
     *   filter_operators?: array<string, string>,
     *   sort_by?: string,
     *   sort_direction?: string,
     *   drilldown_path?: string
     * }
     */
    private function queryPayloadForView(SavedReportView $view): array
    {
        return $this->queryPayloadFromArray([
            'filters' => $view->filters ?? [],
            'filter_operators' => $view->filter_operators ?? [],
            'sort_by' => $view->sort_by,
            'sort_direction' => $view->sort_direction,
            'drilldown_path' => $view->drilldown_path,
        ]);
    }

    private function assertCanManageView(User $actor, SavedReportView $view): void
    {
        if ($this->canManageAllViews($actor) || $view->owner_user_id === $actor->id) {
            return;
        }

        throw new AuthorizationException('You are not allowed to modify this saved report view.');
    }

    private function flattenExceptionMessage(Throwable $exception): string
    {
        if ($exception instanceof ValidationException) {
            foreach ($exception->errors() as $messages) {
                if (is_array($messages) && $messages !== []) {
                    return (string) $messages[0];
                }
            }
        }

        return $exception->getMessage();
    }

    /**
     * @param  Collection<int, SavedReportView>  $items
     */
    private function paginateCollection(Collection $items, int $perPage, int $page): LengthAwarePaginator
    {
        $page = max($page, 1);
        $perPage = max($perPage, 1);

        return new Paginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );
    }
}
