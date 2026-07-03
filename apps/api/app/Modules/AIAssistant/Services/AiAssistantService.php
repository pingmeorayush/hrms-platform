<?php

namespace App\Modules\AIAssistant\Services;

use App\Models\AuditLog;
use App\Models\AiConversation;
use App\Models\AiInteraction;
use App\Models\AiRecommendation;
use App\Models\AttendanceRecord;
use App\Models\Document;
use App\Models\Employee;
use App\Models\LearningAssignmentTarget;
use App\Models\LeaveBalance;
use App\Models\Payslip;
use App\Models\PolicyAcknowledgement;
use App\Models\User;
use App\Modules\AIAssistant\Resources\AiConversationResource;
use App\Modules\AIAssistant\Resources\AiInteractionResource;
use App\Modules\AIAssistant\Resources\AiRecommendationResource;
use App\Modules\AttendanceManagement\Services\AttendanceAccessScopeService;
use App\Modules\EmployeeManagement\Services\EmployeeSelfServiceAccessScopeService;
use App\Modules\EmployeeManagement\Services\PolicyAcknowledgementService;
use App\Modules\LearningManagement\Services\LearningAccessScopeService;
use App\Modules\LeaveManagement\Services\LeaveBalanceAccessScopeService;
use App\Modules\PayrollManagement\Services\PayslipAccessScopeService;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AiAssistantService
{
    public function __construct(
        private readonly EmployeeSelfServiceAccessScopeService $selfServiceAccessScopeService,
        private readonly LeaveBalanceAccessScopeService $leaveBalanceAccessScopeService,
        private readonly AttendanceAccessScopeService $attendanceAccessScopeService,
        private readonly PayslipAccessScopeService $payslipAccessScopeService,
        private readonly PolicyAcknowledgementService $policyAcknowledgementService,
        private readonly LearningAccessScopeService $learningAccessScopeService,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @return list<string>
     */
    public static function supportedUseCaseKeys(): array
    {
        return [
            'leave_balance',
            'attendance_summary',
            'payslip_summary',
            'policy_document',
            'learning_summary',
        ];
    }

    /**
     * @return list<string>
     */
    public static function approvedRecommendationScenarioKeys(): array
    {
        return [
            'learning_next_best_action',
            'policy_acknowledgement_follow_up',
            'attendance_follow_up',
        ];
    }

    public function workspace(User $actor): array
    {
        $linkedEmployee = $this->selfServiceAccessScopeService->findLinkedEmployee($actor);
        $interactions = AiInteraction::query()
            ->with('conversation')
            ->where('company_id', $actor->company_id)
            ->where('user_id', $actor->id)
            ->orderByDesc('responded_at')
            ->orderByDesc('id')
            ->get();
        $recentInteractions = $interactions->take(8);

        $recommendations = AiRecommendation::query()
            ->with(['employee', 'decidedBy'])
            ->where('company_id', $actor->company_id)
            ->where('user_id', $actor->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
        $recentRecommendations = $recommendations->take(8);
        $summary = $this->buildWorkspaceSummary($interactions, $recommendations);

        $this->auditLogger->record(
            eventType: 'ai.assistant.workspace.viewed',
            actor: $actor,
            metadata: [
                'interaction_count' => $summary['interaction_count'],
                'recommendation_count' => $summary['recommendation_count'],
                'linked_employee_id' => $linkedEmployee?->id,
            ],
            entityType: 'ai_workspace',
        );

        $auditLogs = $this->aiAuditLogQuery($actor)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return [
            'disclosure' => 'Responses are AI-generated from governed tenant data and must not be used to auto-execute critical HR actions.',
            'persona' => $this->resolvePersona($actor, $linkedEmployee),
            'linked_employee' => $linkedEmployee ? $this->mapEmployeeSummary($linkedEmployee) : null,
            'subject_options' => $this->loadSubjectOptions($actor),
            'capabilities' => [
                'supported_use_cases' => $this->supportedUseCases(),
                'approved_recommendation_scenarios' => $this->approvedRecommendationScenarios(),
                'guardrails' => $this->guardrailMessages(),
            ],
            'permissions' => [
                'can_chat' => $actor->can('ai.view') || $actor->can('ai.recommend'),
                'can_generate_recommendations' => $actor->can('ai.recommend'),
            ],
            'summary' => $summary,
            'review_analytics' => $this->buildReviewAnalytics($interactions, $recommendations, $auditLogs),
            'audit_timeline' => $auditLogs
                ->take(8)
                ->map(fn (AuditLog $auditLog): array => $this->mapAuditTimelineEvent($auditLog))
                ->values()
                ->all(),
            'recent_interactions' => AiInteractionResource::collection($recentInteractions)->resolve(),
            'recent_recommendations' => AiRecommendationResource::collection($recentRecommendations)->resolve(),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function chat(User $actor, array $attributes): array
    {
        $question = trim((string) $attributes['question']);
        $useCase = $this->resolveUseCase($question, $attributes['use_case'] ?? null);
        $subjectEmployee = $this->resolveSubjectEmployee(
            $actor,
            isset($attributes['subject_employee_id']) ? (int) $attributes['subject_employee_id'] : null,
            $this->domainForUseCase($useCase),
        );
        $conversation = $this->resolveConversation(
            $actor,
            isset($attributes['conversation_id']) ? (int) $attributes['conversation_id'] : null,
            $attributes['persona'] ?? null,
            $question,
        );

        $response = $this->isMutationOrApprovalRequest($question)
            ? $this->buildMutationGuardrail($question)
            : $this->buildUseCaseResponse($actor, $useCase, $question, $subjectEmployee);

        $interaction = AiInteraction::query()->create([
            'company_id' => $actor->company_id,
            'ai_conversation_id' => $conversation->id,
            'user_id' => $actor->id,
            'interaction_type' => (string) $response['interaction_type'],
            'use_case' => (string) $response['use_case'],
            'question' => $question,
            'answer' => (string) $response['answer'],
            'status' => (string) $response['status'],
            'confidence_score' => $response['confidence_score'],
            'citations' => $response['citations'],
            'guardrails' => $response['guardrails'],
            'metadata' => $response['metadata'],
            'responded_at' => now(),
        ]);

        $conversation->forceFill([
            'last_interacted_at' => $interaction->responded_at,
            'title' => $conversation->title ?: Str::limit($question, 120, ''),
        ])->save();

        $this->auditLogger->record(
            eventType: 'ai.interaction.generated',
            actor: $actor,
            metadata: [
                'conversation_id' => $conversation->id,
                'interaction_id' => $interaction->id,
                'use_case' => $interaction->use_case,
                'status' => $interaction->status,
                'subject_employee_id' => $subjectEmployee?->id,
            ],
            entityType: 'ai_interaction',
            entityId: (string) $interaction->id,
        );

        $interaction->load('conversation');

        return [
            'conversation' => (new AiConversationResource($conversation))->resolve(),
            'interaction' => (new AiInteractionResource($interaction))->resolve(),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function generateRecommendation(User $actor, array $attributes): array
    {
        $scenario = (string) $attributes['scenario'];
        $subjectEmployee = $this->resolveSubjectEmployee(
            $actor,
            isset($attributes['subject_employee_id']) ? (int) $attributes['subject_employee_id'] : null,
            $this->domainForScenario($scenario),
        );

        $conversationId = isset($attributes['conversation_id']) ? (int) $attributes['conversation_id'] : null;
        $conversation = $conversationId ? $this->resolveConversation($actor, $conversationId, null, null) : null;

        $definition = match ($scenario) {
            'learning_next_best_action' => $this->buildLearningRecommendation($actor, $subjectEmployee, $attributes['context_note'] ?? null),
            'policy_acknowledgement_follow_up' => $this->buildPolicyRecommendation($actor, $subjectEmployee, $attributes['context_note'] ?? null),
            default => $this->buildAttendanceRecommendation($actor, $subjectEmployee, $attributes['context_note'] ?? null),
        };

        $recommendation = AiRecommendation::query()->create([
            'company_id' => $actor->company_id,
            'ai_conversation_id' => $conversation?->id,
            'user_id' => $actor->id,
            'employee_id' => $subjectEmployee?->id,
            'scenario' => $scenario,
            'title' => $definition['title'],
            'summary' => $definition['summary'],
            'rationale' => $definition['rationale'],
            'confidence_score' => $definition['confidence_score'],
            'suggested_actions' => $definition['suggested_actions'],
            'supporting_citations' => $definition['supporting_citations'],
            'status' => 'pending_review',
            'human_review_required' => true,
            'metadata' => $definition['metadata'],
        ]);

        $this->auditLogger->record(
            eventType: 'ai.recommendation.generated',
            actor: $actor,
            metadata: [
                'recommendation_id' => $recommendation->id,
                'scenario' => $recommendation->scenario,
                'employee_id' => $recommendation->employee_id,
            ],
            entityType: 'ai_recommendation',
            entityId: (string) $recommendation->id,
        );

        $recommendation->load(['employee', 'decidedBy']);

        return (new AiRecommendationResource($recommendation))->resolve();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function recordRecommendationDecision(User $actor, int $recommendationId, array $attributes): array
    {
        $recommendation = $this->resolveOwnedRecommendation($actor, $recommendationId);
        $decision = (string) $attributes['decision'];

        $recommendation->forceFill([
            'decision' => $decision,
            'status' => $decision,
            'decision_notes' => $this->nullableString($attributes['decision_notes'] ?? null),
            'decided_at' => now(),
            'decided_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'ai.recommendation.decision_recorded',
            actor: $actor,
            metadata: [
                'recommendation_id' => $recommendation->id,
                'scenario' => $recommendation->scenario,
                'decision' => $decision,
            ],
            entityType: 'ai_recommendation',
            entityId: (string) $recommendation->id,
        );

        return (new AiRecommendationResource($recommendation->fresh(['employee', 'decidedBy'])))->resolve();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function recordInteractionFeedback(User $actor, int $interactionId, array $attributes): array
    {
        $interaction = $this->resolveOwnedInteraction($actor, $interactionId);

        $interaction->forceFill([
            'feedback_rating' => $attributes['rating'] ?? null,
            'feedback_sentiment' => (string) $attributes['sentiment'],
            'feedback_notes' => $this->nullableString($attributes['notes'] ?? null),
        ])->save();

        $this->auditLogger->record(
            eventType: 'ai.interaction.feedback_recorded',
            actor: $actor,
            metadata: [
                'interaction_id' => $interaction->id,
                'rating' => $interaction->feedback_rating,
                'sentiment' => $interaction->feedback_sentiment,
            ],
            entityType: 'ai_interaction',
            entityId: (string) $interaction->id,
        );

        return (new AiInteractionResource($interaction->fresh('conversation')))->resolve();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function supportedUseCases(): array
    {
        return [
            [
                'key' => 'leave_balance',
                'label' => 'Leave balance and booking posture',
                'description' => 'Summarizes available leave, booked leave, and the most constrained leave pool for an accessible employee.',
                'examples' => ['How many leave days do I have left?', 'Show my annual leave balance.'],
            ],
            [
                'key' => 'attendance_summary',
                'label' => 'Attendance and time posture',
                'description' => 'Explains today or recent attendance, late arrivals, and missing checkout patterns from governed attendance records.',
                'examples' => ['Did I check in today?', 'Summarize my recent attendance posture.'],
            ],
            [
                'key' => 'payslip_summary',
                'label' => 'Payslip and payroll release summary',
                'description' => 'Surfaces the latest finalized payslip references and net-pay posture without modifying payroll.',
                'examples' => ['Show my last payslip.', 'What was my latest net salary?'],
            ],
            [
                'key' => 'policy_document',
                'label' => 'Policy and document acknowledgement summary',
                'description' => 'Lists pending policy acknowledgements and searchable policy documents available in the current session scope.',
                'examples' => ['Which policies still need my acknowledgement?', 'Find remote work policy documents.'],
            ],
            [
                'key' => 'learning_summary',
                'label' => 'Learning assignment posture',
                'description' => 'Summarizes assigned, overdue, and completed learning work for an accessible employee.',
                'examples' => ['What training is still due?', 'Summarize my learning assignments.'],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function approvedRecommendationScenarios(): array
    {
        return [
            [
                'key' => 'learning_next_best_action',
                'label' => 'Learning next-best action',
                'description' => 'Suggests the next governed learning step and never auto-assigns or completes training.',
                'human_review_required' => true,
            ],
            [
                'key' => 'policy_acknowledgement_follow_up',
                'label' => 'Policy acknowledgement follow-up',
                'description' => 'Highlights pending policy acknowledgements and records a human decision before any manual follow-up.',
                'human_review_required' => true,
            ],
            [
                'key' => 'attendance_follow_up',
                'label' => 'Attendance follow-up',
                'description' => 'Suggests a human review path for lateness or missing checkout patterns without editing attendance.',
                'human_review_required' => true,
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function guardrailMessages(): array
    {
        return [
            'Critical approvals, payroll changes, employee status changes, and compensation changes are not executed by the assistant in v1.',
            'Responses stay limited to the current tenant and the requesting session permission scope.',
            'Recommendations remain review-only until a human explicitly accepts or rejects them.',
            'Citations expose source context so operators can verify why the assistant responded the way it did.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolvePersona(User $actor, ?Employee $linkedEmployee): array
    {
        $roles = $actor->getRoleNames();

        if ($roles->contains('recruiter')) {
            return ['key' => 'recruiter_copilot', 'label' => 'Recruiter Copilot'];
        }

        if ($roles->contains('hr.admin') || $roles->contains('tenant.admin')) {
            return ['key' => 'hr_copilot', 'label' => 'HR Copilot'];
        }

        if ($roles->contains('manager')) {
            return ['key' => 'manager_copilot', 'label' => 'Manager Copilot'];
        }

        if ($roles->contains('learning.admin')) {
            return ['key' => 'learning_copilot', 'label' => 'Learning Copilot'];
        }

        if ($roles->contains('platform.super_admin')) {
            return ['key' => 'platform_copilot', 'label' => 'Platform Copilot'];
        }

        return [
            'key' => $linkedEmployee ? 'employee_copilot' : 'assistant',
            'label' => $linkedEmployee ? 'Employee Copilot' : 'Phoenix Assistant',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function loadSubjectOptions(User $actor): array
    {
        $linkedEmployee = $this->selfServiceAccessScopeService->findLinkedEmployee($actor);

        if ($actor->can('employee.manage') || $actor->can('payroll.view') || $actor->can('learning.assign')) {
            return Employee::query()
                ->where('company_id', $actor->company_id)
                ->where('employment_status', 'active')
                ->orderBy('first_name')
                ->orderBy('id')
                ->limit(25)
                ->get()
                ->map(fn (Employee $employee): array => $this->mapEmployeeSummary($employee))
                ->all();
        }

        if (! $linkedEmployee) {
            return [];
        }

        $employees = Employee::query()
            ->where('company_id', $actor->company_id)
            ->where(function (Builder $builder) use ($actor, $linkedEmployee): void {
                $builder->where('id', $linkedEmployee->id);

                if ($actor->hasRole('manager') || $actor->can('leave.approve') || $actor->can('attendance.approve')) {
                    $builder->orWhere('manager_id', $linkedEmployee->id);
                }
            })
            ->orderBy('first_name')
            ->orderBy('id')
            ->get();

        return $employees
            ->map(fn (Employee $employee): array => $this->mapEmployeeSummary($employee))
            ->all();
    }

    private function resolveConversation(
        User $actor,
        ?int $conversationId,
        mixed $persona = null,
        ?string $fallbackTitle = null,
    ): AiConversation {
        if ($conversationId !== null) {
            $conversation = AiConversation::query()
                ->where('company_id', $actor->company_id)
                ->where('user_id', $actor->id)
                ->find($conversationId);

            if (! $conversation) {
                throw new NotFoundHttpException;
            }

            return $conversation;
        }

        $resolvedPersona = $persona ? trim((string) $persona) : (string) $this->resolvePersona($actor, $this->selfServiceAccessScopeService->findLinkedEmployee($actor))['key'];

        return AiConversation::query()->create([
            'company_id' => $actor->company_id,
            'user_id' => $actor->id,
            'title' => Str::limit($fallbackTitle ?? 'New AI conversation', 120, ''),
            'persona' => $resolvedPersona === '' ? 'assistant' : $resolvedPersona,
            'status' => 'active',
            'metadata' => [
                'created_from' => 'api',
            ],
            'last_interacted_at' => now(),
        ]);
    }

    private function resolveUseCase(string $question, mixed $explicitUseCase): string
    {
        if (is_string($explicitUseCase) && in_array($explicitUseCase, self::supportedUseCaseKeys(), true)) {
            return $explicitUseCase;
        }

        $normalized = Str::lower($question);
        $containsLateWord = preg_match('/\blate\b/', $normalized) === 1;

        return match (true) {
            str_contains($normalized, 'leave') || str_contains($normalized, 'balance') || str_contains($normalized, 'day off') => 'leave_balance',
            str_contains($normalized, 'attendance') || str_contains($normalized, 'check in') || str_contains($normalized, 'check-in')
                || $containsLateWord || str_contains($normalized, 'overtime') || str_contains($normalized, 'worked hours') => 'attendance_summary',
            str_contains($normalized, 'payslip') || str_contains($normalized, 'salary slip') || str_contains($normalized, 'net pay')
                || str_contains($normalized, 'payroll') => 'payslip_summary',
            str_contains($normalized, 'policy') || str_contains($normalized, 'document') || str_contains($normalized, 'handbook')
                || str_contains($normalized, 'acknowledge') => 'policy_document',
            str_contains($normalized, 'learning') || str_contains($normalized, 'training') || str_contains($normalized, 'course')
                || str_contains($normalized, 'certification') => 'learning_summary',
            default => 'unsupported',
        };
    }

    private function isMutationOrApprovalRequest(string $question): bool
    {
        $normalized = Str::lower($question);

        foreach ([
            'approve',
            'reject',
            'terminate',
            'promote',
            'transfer',
            'increase salary',
            'change compensation',
            'update payroll',
            'process payroll',
            'apply leave',
            'submit leave',
            'execute',
            'delete',
        ] as $keyword) {
            if (str_contains($normalized, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMutationGuardrail(string $question): array
    {
        return [
            'interaction_type' => 'guardrail',
            'use_case' => 'guardrail',
            'answer' => 'This assistant can explain governed data and propose review-only recommendations, but it does not approve, mutate, or auto-execute critical HR actions in v1. Use the linked operational workspace for the controlled action path.',
            'status' => 'guardrailed',
            'confidence_score' => 0.99,
            'citations' => [],
            'guardrails' => [
                [
                    'code' => 'approval_required',
                    'message' => 'Human approval is required for critical actions, and no backend mutation was executed.',
                ],
                [
                    'code' => 'read_only_v1',
                    'message' => 'Supported v1 experiences are limited to read-focused questions and review-only recommendations.',
                ],
            ],
            'metadata' => [
                'question' => $question,
                'supported_use_cases' => self::supportedUseCaseKeys(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildUseCaseResponse(User $actor, string $useCase, string $question, ?Employee $subjectEmployee): array
    {
        return match ($useCase) {
            'leave_balance' => $this->buildLeaveBalanceResponse($actor, $subjectEmployee),
            'attendance_summary' => $this->buildAttendanceResponse($actor, $question, $subjectEmployee),
            'payslip_summary' => $this->buildPayslipResponse($actor, $subjectEmployee),
            'policy_document' => $this->buildPolicyResponse($actor, $question, $subjectEmployee),
            'learning_summary' => $this->buildLearningResponse($actor, $subjectEmployee),
            default => $this->buildUnsupportedResponse($question),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLeaveBalanceResponse(User $actor, ?Employee $subjectEmployee): array
    {
        if (! $subjectEmployee) {
            return $this->buildMissingSubjectResponse('leave balance', '/self-service/profile');
        }

        $balances = $this->leaveBalanceAccessScopeService
            ->balancesQuery($actor, ['leaveType'])
            ->where('employee_id', $subjectEmployee->id)
            ->orderByDesc('available_days')
            ->orderBy('id')
            ->get();

        if ($balances->isEmpty()) {
            return [
                'interaction_type' => 'answer',
                'use_case' => 'leave_balance',
                'answer' => "{$subjectEmployee->full_name} does not have any governed leave balance records available in the current scope yet.",
                'status' => 'answered',
                'confidence_score' => 0.78,
                'citations' => [],
                'guardrails' => [],
                'metadata' => [
                    'subject_employee_id' => $subjectEmployee->id,
                    'balance_count' => 0,
                ],
            ];
        }

        /** @var LeaveBalance $primaryBalance */
        $primaryBalance = $balances->first();
        $availableDays = round((float) $balances->sum('available_days'), 2);
        $bookedDays = round((float) $balances->sum('booked_days'), 2);
        $usedDays = round((float) $balances->sum('used_days'), 2);

        return [
            'interaction_type' => 'answer',
            'use_case' => 'leave_balance',
            'answer' => "{$subjectEmployee->full_name} currently has {$availableDays} available leave day(s) across {$balances->count()} governed balance record(s). The largest available pool is {$primaryBalance->leaveType?->name} with ".round((float) $primaryBalance->available_days, 2)." day(s) available, while {$bookedDays} day(s) are already booked and {$usedDays} day(s) have been used in the active period.",
            'status' => 'answered',
            'confidence_score' => 0.92,
            'citations' => $balances
                ->take(3)
                ->map(fn (LeaveBalance $balance): array => [
                    'type' => 'leave_balance',
                    'label' => ($balance->leaveType?->name ?? 'Leave type').' balance',
                    'reference' => 'Leave balance #'.$balance->id,
                    'excerpt' => 'Available '.round((float) $balance->available_days, 2).' day(s), booked '.round((float) $balance->booked_days, 2).' day(s), used '.round((float) $balance->used_days, 2).' day(s).',
                    'entity_type' => 'leave_balance',
                    'entity_id' => $balance->id,
                    'route' => '/leave/requests',
                ])
                ->all(),
            'guardrails' => [],
            'metadata' => [
                'subject_employee_id' => $subjectEmployee->id,
                'balance_count' => $balances->count(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAttendanceResponse(User $actor, string $question, ?Employee $subjectEmployee): array
    {
        if (! $subjectEmployee) {
            return $this->buildMissingSubjectResponse('attendance', '/attendance/my-attendance/history');
        }

        $query = $this->attendanceAccessScopeService
            ->attendanceRecordsQuery($actor, ['employee', 'shift'])
            ->where('employee_id', $subjectEmployee->id);

        $normalizedQuestion = Str::lower($question);

        if (str_contains($normalizedQuestion, 'today')) {
            /** @var AttendanceRecord|null $todayRecord */
            $todayRecord = (clone $query)
                ->whereDate('attendance_date', now()->toDateString())
                ->latest('attendance_date')
                ->latest('id')
                ->first();

            if (! $todayRecord) {
                return [
                    'interaction_type' => 'answer',
                    'use_case' => 'attendance_summary',
                    'answer' => "No attendance record for {$subjectEmployee->full_name} has been captured for today yet.",
                    'status' => 'answered',
                    'confidence_score' => 0.83,
                    'citations' => [],
                    'guardrails' => [],
                    'metadata' => [
                        'subject_employee_id' => $subjectEmployee->id,
                        'window' => 'today',
                    ],
                ];
            }

            return [
                'interaction_type' => 'answer',
                'use_case' => 'attendance_summary',
                'answer' => "{$subjectEmployee->full_name} has a {$todayRecord->primary_status} attendance record for {$todayRecord->attendance_date?->toDateString()}. Check-in is ".($todayRecord->check_in_at?->toDateTimeString() ?? 'not captured yet').', check-out is '.($todayRecord->check_out_at?->toDateTimeString() ?? 'still open').', and worked minutes currently total '.(int) ($todayRecord->worked_minutes ?? 0).'.',
                'status' => 'answered',
                'confidence_score' => 0.91,
                'citations' => [[
                    'type' => 'attendance_record',
                    'label' => 'Today attendance',
                    'reference' => 'Attendance record #'.$todayRecord->id,
                    'excerpt' => 'Status '.$todayRecord->primary_status.', worked '.(int) ($todayRecord->worked_minutes ?? 0).' minute(s).',
                    'entity_type' => 'attendance_record',
                    'entity_id' => $todayRecord->id,
                    'route' => '/attendance/my-attendance/history',
                ]],
                'guardrails' => [],
                'metadata' => [
                    'subject_employee_id' => $subjectEmployee->id,
                    'window' => 'today',
                ],
            ];
        }

        $records = (clone $query)
            ->whereDate('attendance_date', '>=', now()->subDays(13)->toDateString())
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->get();

        if ($records->isEmpty()) {
            return [
                'interaction_type' => 'answer',
                'use_case' => 'attendance_summary',
                'answer' => "No recent attendance records for {$subjectEmployee->full_name} are available in the last 14 days.",
                'status' => 'answered',
                'confidence_score' => 0.77,
                'citations' => [],
                'guardrails' => [],
                'metadata' => [
                    'subject_employee_id' => $subjectEmployee->id,
                    'window' => '14_days',
                ],
            ];
        }

        $lateCount = $records->where('is_late', true)->count();
        $openCheckoutCount = $records->filter(fn (AttendanceRecord $record): bool => $record->check_in_at !== null && $record->check_out_at === null)->count();
        $overtimeMinutes = (int) $records->sum('overtime_minutes');

        return [
            'interaction_type' => 'answer',
            'use_case' => 'attendance_summary',
            'answer' => "In the last 14 days, {$subjectEmployee->full_name} has {$records->count()} attendance record(s), {$lateCount} late arrival(s), {$openCheckoutCount} open checkout record(s), and {$overtimeMinutes} total overtime minute(s) captured in governed attendance data.",
            'status' => 'answered',
            'confidence_score' => 0.9,
            'citations' => $records
                ->take(3)
                ->map(fn (AttendanceRecord $record): array => [
                    'type' => 'attendance_record',
                    'label' => $record->attendance_date?->toDateString() ?? 'Attendance day',
                    'reference' => 'Attendance record #'.$record->id,
                    'excerpt' => 'Status '.$record->primary_status.', worked '.(int) ($record->worked_minutes ?? 0).' minute(s), overtime '.(int) ($record->overtime_minutes ?? 0).' minute(s).',
                    'entity_type' => 'attendance_record',
                    'entity_id' => $record->id,
                    'route' => '/attendance/my-attendance/history',
                ])
                ->all(),
            'guardrails' => [],
            'metadata' => [
                'subject_employee_id' => $subjectEmployee->id,
                'window' => '14_days',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayslipResponse(User $actor, ?Employee $subjectEmployee): array
    {
        if (! $subjectEmployee) {
            return $this->buildMissingSubjectResponse('payslip', '/payroll/my-pay');
        }

        $payslips = $this->payslipAccessScopeService
            ->payslipsQuery($actor, ['payrollRun.payrollPeriod'])
            ->where('employee_id', $subjectEmployee->id)
            ->orderByDesc('generated_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        if ($payslips->isEmpty()) {
            return [
                'interaction_type' => 'answer',
                'use_case' => 'payslip_summary',
                'answer' => "No finalized payslips for {$subjectEmployee->full_name} are visible in the current payroll scope yet.",
                'status' => 'answered',
                'confidence_score' => 0.79,
                'citations' => [],
                'guardrails' => [],
                'metadata' => [
                    'subject_employee_id' => $subjectEmployee->id,
                    'payslip_count' => 0,
                ],
            ];
        }

        /** @var Payslip $latestPayslip */
        $latestPayslip = $payslips->first();

        return [
            'interaction_type' => 'answer',
            'use_case' => 'payslip_summary',
            'answer' => "The latest finalized payslip visible for {$subjectEmployee->full_name} is {$latestPayslip->slip_number} dated {$latestPayslip->payroll_date?->toDateString()}, with net salary {$latestPayslip->currency} ".number_format((float) $latestPayslip->net_salary, 2).". {$payslips->count()} recent finalized payslip reference(s) are available in this session.",
            'status' => 'answered',
            'confidence_score' => 0.93,
            'citations' => $payslips
                ->map(fn (Payslip $payslip): array => [
                    'type' => 'payslip',
                    'label' => $payslip->slip_number,
                    'reference' => 'Payslip #'.$payslip->id,
                    'excerpt' => 'Period '.$payslip->start_date?->toDateString().' to '.$payslip->end_date?->toDateString().', net salary '.$payslip->currency.' '.number_format((float) $payslip->net_salary, 2).'.',
                    'entity_type' => 'payslip',
                    'entity_id' => $payslip->id,
                    'route' => '/payroll/my-pay',
                ])
                ->all(),
            'guardrails' => [],
            'metadata' => [
                'subject_employee_id' => $subjectEmployee->id,
                'payslip_count' => $payslips->count(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPolicyResponse(User $actor, string $question, ?Employee $subjectEmployee): array
    {
        $searchTerms = collect(preg_split('/\s+/', Str::lower($question)) ?: [])
            ->map(fn (string $term): string => trim($term))
            ->filter(fn (string $term): bool => strlen($term) > 3)
            ->values();

        $policyDocuments = Document::query()
            ->where('company_id', $actor->company_id)
            ->where('repository_scope', 'policy')
            ->when(
                $searchTerms->isNotEmpty(),
                function (Builder $builder) use ($searchTerms): void {
                    $builder->where(function (Builder $query) use ($searchTerms): void {
                        foreach ($searchTerms as $term) {
                            $query->orWhere('title', 'like', '%'.$term.'%')
                                ->orWhere('notes', 'like', '%'.$term.'%')
                                ->orWhere('original_file_name', 'like', '%'.$term.'%');
                        }
                    });
                },
            )
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $acknowledgements = $subjectEmployee
            ? PolicyAcknowledgement::query()
                ->where('company_id', $actor->company_id)
                ->where('employee_id', $subjectEmployee->id)
                ->with('document')
                ->orderByRaw("case when status = 'assigned' then 0 else 1 end")
                ->orderBy('due_date')
                ->orderByDesc('id')
                ->get()
            : collect();

        if ($policyDocuments->isEmpty() && $acknowledgements->isEmpty()) {
            return [
                'interaction_type' => 'answer',
                'use_case' => 'policy_document',
                'answer' => 'No policy documents or acknowledgement records matching this request are visible in the current session scope.',
                'status' => 'answered',
                'confidence_score' => 0.74,
                'citations' => [],
                'guardrails' => [],
                'metadata' => [
                    'policy_document_count' => 0,
                    'acknowledgement_count' => 0,
                ],
            ];
        }

        $pendingAcknowledgements = $acknowledgements->where('status', 'assigned')->count();
        $matchedPolicyCount = $policyDocuments->count();
        $firstPolicy = $policyDocuments->first();

        $acknowledgementText = $subjectEmployee
            ? " {$subjectEmployee->full_name} also has {$pendingAcknowledgements} pending policy acknowledgement(s)."
            : '';

        return [
            'interaction_type' => 'answer',
            'use_case' => 'policy_document',
            'answer' => "{$matchedPolicyCount} policy document(s) match the current request. The most recent visible match is ".($firstPolicy?->title ?? 'a policy document').'.'.$acknowledgementText,
            'status' => 'answered',
            'confidence_score' => 0.86,
            'citations' => $policyDocuments
                ->map(fn (Document $document): array => [
                    'type' => 'policy_document',
                    'label' => $document->title,
                    'reference' => 'Document #'.$document->id,
                    'excerpt' => $document->notes ?: 'Policy document available for governed review.',
                    'entity_type' => 'document',
                    'entity_id' => $document->id,
                    'route' => '/self-service/documents',
                ])
                ->take(3)
                ->values()
                ->merge(
                    $acknowledgements->take(2)->map(fn (PolicyAcknowledgement $acknowledgement): array => [
                        'type' => 'policy_acknowledgement',
                        'label' => $acknowledgement->policy_title,
                        'reference' => 'Policy acknowledgement #'.$acknowledgement->id,
                        'excerpt' => 'Status '.$acknowledgement->status.', due '.($acknowledgement->due_date?->toDateString() ?? 'not scheduled').'.',
                        'entity_type' => 'policy_acknowledgement',
                        'entity_id' => $acknowledgement->id,
                        'route' => '/self-service/documents',
                    ]),
                )
                ->all(),
            'guardrails' => [],
            'metadata' => [
                'policy_document_count' => $matchedPolicyCount,
                'acknowledgement_count' => $acknowledgements->count(),
                'subject_employee_id' => $subjectEmployee?->id,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLearningResponse(User $actor, ?Employee $subjectEmployee): array
    {
        if (! $subjectEmployee) {
            return $this->buildMissingSubjectResponse('learning assignment', '/learning/my-learning');
        }

        $targets = $this->learningAccessScopeService
            ->targetsQuery($actor)
            ->where('employee_id', $subjectEmployee->id)
            ->orderBy('due_on')
            ->orderByDesc('id')
            ->get();

        if ($targets->isEmpty()) {
            return [
                'interaction_type' => 'answer',
                'use_case' => 'learning_summary',
                'answer' => "No learning assignments for {$subjectEmployee->full_name} are currently visible in this session scope.",
                'status' => 'answered',
                'confidence_score' => 0.76,
                'citations' => [],
                'guardrails' => [],
                'metadata' => [
                    'subject_employee_id' => $subjectEmployee->id,
                    'target_count' => 0,
                ],
            ];
        }

        $assignedCount = $targets->where('status', 'assigned')->count();
        $inProgressCount = $targets->where('status', 'in_progress')->count();
        $completedCount = $targets->where('status', 'completed')->count();
        $overdueCount = $targets->filter(
            fn (LearningAssignmentTarget $target): bool => $target->due_on !== null
                && $target->due_on->isPast()
                && $target->status !== 'completed',
        )->count();

        return [
            'interaction_type' => 'answer',
            'use_case' => 'learning_summary',
            'answer' => "{$subjectEmployee->full_name} has {$targets->count()} governed learning assignment target(s): {$assignedCount} assigned, {$inProgressCount} in progress, {$completedCount} completed, and {$overdueCount} overdue.",
            'status' => 'answered',
            'confidence_score' => 0.9,
            'citations' => $targets
                ->take(3)
                ->map(fn (LearningAssignmentTarget $target): array => [
                    'type' => 'learning_assignment_target',
                    'label' => $target->item?->title ?? 'Learning assignment',
                    'reference' => 'Learning target #'.$target->id,
                    'excerpt' => 'Status '.$target->status.', due '.($target->due_on?->toDateString() ?? 'not scheduled').', progress '.$target->completion_progress_percent.'%.',
                    'entity_type' => 'learning_assignment_target',
                    'entity_id' => $target->id,
                    'route' => '/learning/my-learning',
                ])
                ->all(),
            'guardrails' => [],
            'metadata' => [
                'subject_employee_id' => $subjectEmployee->id,
                'target_count' => $targets->count(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildUnsupportedResponse(string $question): array
    {
        return [
            'interaction_type' => 'guardrail',
            'use_case' => 'unsupported',
            'answer' => 'This v1 assistant currently supports governed leave, attendance, payslip, policy, and learning posture questions only. Rephrase the request into one of those areas or use the targeted workspace for the action you need.',
            'status' => 'guardrailed',
            'confidence_score' => 0.82,
            'citations' => [],
            'guardrails' => [
                [
                    'code' => 'use_case_not_supported',
                    'message' => 'The requested use case is outside the approved Sprint 10 assistant baseline.',
                ],
            ],
            'metadata' => [
                'question' => $question,
                'supported_use_cases' => self::supportedUseCaseKeys(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMissingSubjectResponse(string $label, string $route): array
    {
        return [
            'interaction_type' => 'guardrail',
            'use_case' => 'missing_subject',
            'answer' => 'A linked or explicitly selected employee profile is required for this '.$label.' question, and the current session does not resolve one yet.',
            'status' => 'guardrailed',
            'confidence_score' => 0.95,
            'citations' => [],
            'guardrails' => [[
                'code' => 'employee_context_required',
                'message' => 'Choose an employee context or use a linked self-service profile before retrying this query.',
                'action_path' => $route,
            ]],
            'metadata' => [
                'required_route' => $route,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLearningRecommendation(User $actor, ?Employee $subjectEmployee, mixed $contextNote): array
    {
        if (! $subjectEmployee) {
            return [
                'title' => 'Link or select an employee before generating learning guidance',
                'summary' => 'The assistant cannot produce governed learning next steps without a resolved employee profile.',
                'rationale' => ['Learning recommendations need an employee profile and scoped assignment posture before they can be reviewed.'],
                'confidence_score' => 0.55,
                'suggested_actions' => [[
                    'type' => 'open_route',
                    'label' => 'Open learning workspace',
                    'path' => '/learning/my-learning',
                    'requires_confirmation' => false,
                ]],
                'supporting_citations' => [],
                'metadata' => [
                    'context_note' => $this->nullableString($contextNote),
                ],
            ];
        }

        $targets = $this->learningAccessScopeService
            ->targetsQuery($actor)
            ->where('employee_id', $subjectEmployee->id)
            ->orderBy('due_on')
            ->orderByDesc('id')
            ->get();

        $overdueTargets = $targets->filter(
            fn (LearningAssignmentTarget $target): bool => $target->due_on !== null
                && $target->due_on->isPast()
                && $target->status !== 'completed',
        )->values();

        $priorityTargets = ($overdueTargets->isNotEmpty() ? $overdueTargets : $targets->where('status', '!=', 'completed'))
            ->take(2)
            ->values();

        $route = $actor->can('learning.assign') || $actor->can('learning.manage') ? '/learning/assignments' : '/learning/my-learning';

        return [
            'title' => 'Review the next learning step for '.$subjectEmployee->full_name,
            'summary' => $priorityTargets->isNotEmpty()
                ? 'Prioritize '.implode(' and ', $priorityTargets->map(fn (LearningAssignmentTarget $target): string => $target->item?->title ?? 'the pending learning target')->all()).' before opening new learning work.'
                : 'No blocked learning targets were found, so the next step is to review the active learning workspace and confirm whether a new course assignment is actually needed.',
            'rationale' => array_values(array_filter([
                $targets->isNotEmpty() ? $targets->count().' governed learning target(s) are currently visible for review.' : 'No governed learning targets are currently visible.',
                $overdueTargets->isNotEmpty() ? $overdueTargets->count().' target(s) are overdue and should be reviewed before adding more work.' : 'No overdue targets were detected in the visible assignment set.',
                $contextNote ? 'Operator context note: '.trim((string) $contextNote) : null,
            ])),
            'confidence_score' => $overdueTargets->isNotEmpty() ? 0.91 : 0.73,
            'suggested_actions' => [[
                'type' => 'open_route',
                'label' => 'Open governed learning workspace',
                'path' => $route,
                'requires_confirmation' => true,
                'notes' => 'No training is auto-assigned by this recommendation. Review and assign manually in the learning module if still appropriate.',
            ]],
            'supporting_citations' => $priorityTargets->map(fn (LearningAssignmentTarget $target): array => [
                'type' => 'learning_assignment_target',
                'label' => $target->item?->title ?? 'Learning target',
                'reference' => 'Learning target #'.$target->id,
                'excerpt' => 'Status '.$target->status.', due '.($target->due_on?->toDateString() ?? 'not scheduled').', progress '.$target->completion_progress_percent.'%.',
                'entity_type' => 'learning_assignment_target',
                'entity_id' => $target->id,
                'route' => $route,
            ])->all(),
            'metadata' => [
                'subject_employee_id' => $subjectEmployee->id,
                'context_note' => $this->nullableString($contextNote),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPolicyRecommendation(User $actor, ?Employee $subjectEmployee, mixed $contextNote): array
    {
        $route = $actor->can('employee.manage') ? '/operations/documents' : '/self-service/documents';

        if (! $subjectEmployee) {
            return [
                'title' => 'Resolve an employee context before policy follow-up',
                'summary' => 'The assistant needs a governed employee context before it can recommend policy acknowledgement follow-up.',
                'rationale' => ['Policy follow-up remains employee-scoped in the current assistant baseline.'],
                'confidence_score' => 0.58,
                'suggested_actions' => [[
                    'type' => 'open_route',
                    'label' => 'Open policy workspace',
                    'path' => $route,
                    'requires_confirmation' => false,
                ]],
                'supporting_citations' => [],
                'metadata' => [
                    'context_note' => $this->nullableString($contextNote),
                ],
            ];
        }

        $acknowledgements = PolicyAcknowledgement::query()
            ->where('company_id', $actor->company_id)
            ->where('employee_id', $subjectEmployee->id)
            ->with('document')
            ->orderByRaw("case when status = 'assigned' then 0 else 1 end")
            ->orderBy('due_date')
            ->orderByDesc('id')
            ->get();

        $pending = $acknowledgements->where('status', 'assigned')->values();

        return [
            'title' => 'Review policy acknowledgement follow-up for '.$subjectEmployee->full_name,
            'summary' => $pending->isNotEmpty()
                ? $pending->count().' policy acknowledgement(s) remain open. Review the due items and record any manual follow-up outside the assistant.'
                : 'No open policy acknowledgements are currently visible, so the next step is only to confirm policy posture in the document workspace if needed.',
            'rationale' => array_values(array_filter([
                $acknowledgements->count().' acknowledgement record(s) were reviewed in the current session scope.',
                $pending->isNotEmpty() ? $pending->count().' item(s) remain assigned and unacknowledged.' : 'All visible acknowledgement records are already closed.',
                $contextNote ? 'Operator context note: '.trim((string) $contextNote) : null,
            ])),
            'confidence_score' => $pending->isNotEmpty() ? 0.89 : 0.71,
            'suggested_actions' => [[
                'type' => 'open_route',
                'label' => 'Open policy document workspace',
                'path' => $route,
                'requires_confirmation' => true,
                'notes' => 'No acknowledgement is auto-submitted by the assistant. Use the governed workspace to review or acknowledge manually.',
            ]],
            'supporting_citations' => $pending
                ->take(3)
                ->map(fn (PolicyAcknowledgement $acknowledgement): array => [
                    'type' => 'policy_acknowledgement',
                    'label' => $acknowledgement->policy_title,
                    'reference' => 'Policy acknowledgement #'.$acknowledgement->id,
                    'excerpt' => 'Status '.$acknowledgement->status.', due '.($acknowledgement->due_date?->toDateString() ?? 'not scheduled').'.',
                    'entity_type' => 'policy_acknowledgement',
                    'entity_id' => $acknowledgement->id,
                    'route' => $route,
                ])
                ->all(),
            'metadata' => [
                'subject_employee_id' => $subjectEmployee->id,
                'context_note' => $this->nullableString($contextNote),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAttendanceRecommendation(User $actor, ?Employee $subjectEmployee, mixed $contextNote): array
    {
        $route = $actor->can('attendance.approve') || $actor->can('attendance.edit')
            ? '/attendance/operational-review'
            : '/attendance/my-attendance/history';

        if (! $subjectEmployee) {
            return [
                'title' => 'Resolve an employee context before attendance follow-up',
                'summary' => 'The assistant needs a governed employee context before it can recommend attendance review next steps.',
                'rationale' => ['Attendance follow-up recommendations stay employee-scoped and review-only in v1.'],
                'confidence_score' => 0.57,
                'suggested_actions' => [[
                    'type' => 'open_route',
                    'label' => 'Open attendance workspace',
                    'path' => $route,
                    'requires_confirmation' => false,
                ]],
                'supporting_citations' => [],
                'metadata' => [
                    'context_note' => $this->nullableString($contextNote),
                ],
            ];
        }

        $records = $this->attendanceAccessScopeService
            ->attendanceRecordsQuery($actor, ['employee'])
            ->where('employee_id', $subjectEmployee->id)
            ->whereDate('attendance_date', '>=', now()->subDays(13)->toDateString())
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->get();

        $flaggedRecords = $records
            ->filter(fn (AttendanceRecord $record): bool => $record->is_late || ($record->check_in_at !== null && $record->check_out_at === null))
            ->values();

        return [
            'title' => 'Review attendance follow-up for '.$subjectEmployee->full_name,
            'summary' => $flaggedRecords->isNotEmpty()
                ? $flaggedRecords->count().' attendance record(s) need manual review for lateness or missing checkout posture before anyone makes a correction.'
                : 'No urgent lateness or missing checkout pattern was detected in the recent visible attendance window.',
            'rationale' => array_values(array_filter([
                $records->count().' recent attendance record(s) were reviewed.',
                $flaggedRecords->isNotEmpty() ? $flaggedRecords->count().' record(s) matched manual-review criteria.' : 'No record currently matches the manual-review criteria used by the v1 assistant.',
                $contextNote ? 'Operator context note: '.trim((string) $contextNote) : null,
            ])),
            'confidence_score' => $flaggedRecords->isNotEmpty() ? 0.9 : 0.7,
            'suggested_actions' => [[
                'type' => 'open_route',
                'label' => 'Open attendance review workspace',
                'path' => $route,
                'requires_confirmation' => true,
                'notes' => 'No correction request or attendance change is submitted automatically. Review the governed attendance workflow manually.',
            ]],
            'supporting_citations' => $flaggedRecords
                ->take(3)
                ->map(fn (AttendanceRecord $record): array => [
                    'type' => 'attendance_record',
                    'label' => $record->attendance_date?->toDateString() ?? 'Attendance record',
                    'reference' => 'Attendance record #'.$record->id,
                    'excerpt' => 'Status '.$record->primary_status.', late '.($record->is_late ? 'yes' : 'no').', checkout '.($record->check_out_at ? 'captured' : 'open').'.',
                    'entity_type' => 'attendance_record',
                    'entity_id' => $record->id,
                    'route' => $route,
                ])
                ->all(),
            'metadata' => [
                'subject_employee_id' => $subjectEmployee->id,
                'context_note' => $this->nullableString($contextNote),
            ],
        ];
    }

    private function resolveOwnedRecommendation(User $actor, int $recommendationId): AiRecommendation
    {
        $recommendation = AiRecommendation::query()
            ->where('company_id', $actor->company_id)
            ->where('user_id', $actor->id)
            ->find($recommendationId);

        if (! $recommendation) {
            throw new NotFoundHttpException;
        }

        return $recommendation;
    }

    private function resolveOwnedInteraction(User $actor, int $interactionId): AiInteraction
    {
        $interaction = AiInteraction::query()
            ->where('company_id', $actor->company_id)
            ->where('user_id', $actor->id)
            ->find($interactionId);

        if (! $interaction) {
            throw new NotFoundHttpException;
        }

        return $interaction;
    }

    private function domainForUseCase(string $useCase): string
    {
        return match ($useCase) {
            'leave_balance' => 'leave',
            'attendance_summary' => 'attendance',
            'payslip_summary' => 'payslip',
            'policy_document' => 'policy',
            'learning_summary' => 'learning',
            default => 'employee',
        };
    }

    private function domainForScenario(string $scenario): string
    {
        return match ($scenario) {
            'learning_next_best_action' => 'learning',
            'policy_acknowledgement_follow_up' => 'policy',
            default => 'attendance',
        };
    }

    private function resolveSubjectEmployee(User $actor, ?int $employeeId, string $domain): ?Employee
    {
        $accessibleEmployeeIds = $this->accessibleEmployeeIdsForDomain($actor, $domain);
        $linkedEmployee = $this->selfServiceAccessScopeService->findLinkedEmployee($actor);

        if ($employeeId === null) {
            if ($linkedEmployee && ($accessibleEmployeeIds === null || in_array($linkedEmployee->id, $accessibleEmployeeIds, true))) {
                return $linkedEmployee;
            }

            return null;
        }

        if ($accessibleEmployeeIds !== null && ! in_array($employeeId, $accessibleEmployeeIds, true)) {
            throw new NotFoundHttpException;
        }

        return Employee::query()
            ->where('company_id', $actor->company_id)
            ->findOrFail($employeeId);
    }

    /**
     * @return list<int>|null
     */
    private function accessibleEmployeeIdsForDomain(User $actor, string $domain): ?array
    {
        $linkedEmployee = $this->selfServiceAccessScopeService->findLinkedEmployee($actor);

        return match ($domain) {
            'leave' => $this->managerOrTenantScopeIds(
                $actor,
                $linkedEmployee,
                $this->leaveBalanceAccessScopeService->canViewAllTenantBalances($actor),
                $actor->can('leave.approve'),
            ),
            'attendance' => $this->managerOrTenantScopeIds(
                $actor,
                $linkedEmployee,
                $this->attendanceAccessScopeService->canViewAllTenantAttendance($actor),
                $actor->can('attendance.approve'),
            ),
            'payslip' => $this->managerOrTenantScopeIds(
                $actor,
                $linkedEmployee,
                $this->payslipAccessScopeService->canViewAllTenantPayslips($actor),
                false,
            ),
            'policy' => $this->managerOrTenantScopeIds(
                $actor,
                $linkedEmployee,
                $actor->can('employee.manage'),
                false,
            ),
            'learning' => $this->managerOrTenantScopeIds(
                $actor,
                $linkedEmployee,
                $actor->can('learning.manage') || $actor->can('learning.assign'),
                $actor->hasRole('manager'),
            ),
            default => $this->managerOrTenantScopeIds($actor, $linkedEmployee, $actor->can('employee.manage'), false),
        };
    }

    /**
     * @return list<int>|null
     */
    private function managerOrTenantScopeIds(
        User $actor,
        ?Employee $linkedEmployee,
        bool $tenantWide,
        bool $includeDirectReports,
    ): ?array {
        if ($tenantWide) {
            return null;
        }

        if (! $linkedEmployee) {
            return [];
        }

        if (! $includeDirectReports) {
            return [$linkedEmployee->id];
        }

        return collect([$linkedEmployee->id])
            ->merge(
                Employee::query()
                    ->where('company_id', $actor->company_id)
                    ->where('manager_id', $linkedEmployee->id)
                    ->pluck('id'),
            )
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, AiInteraction>  $interactions
     * @param  Collection<int, AiRecommendation>  $recommendations
     * @return array<string, int|string|null>
     */
    private function buildWorkspaceSummary(Collection $interactions, Collection $recommendations): array
    {
        $feedbackRecordedCount = $interactions->filter(
            fn (AiInteraction $interaction): bool => $interaction->feedback_sentiment !== null
                || $interaction->feedback_rating !== null
                || $interaction->feedback_notes !== null,
        )->count();

        return [
            'interaction_count' => $interactions->count(),
            'recommendation_count' => $recommendations->count(),
            'pending_recommendation_count' => $recommendations->where('status', 'pending_review')->count(),
            'answered_interaction_count' => $interactions->where('status', 'answered')->count(),
            'guardrailed_interaction_count' => $interactions->where('status', 'guardrailed')->count(),
            'feedback_recorded_count' => $feedbackRecordedCount,
            'accepted_recommendation_count' => $recommendations->where('status', 'accepted')->count(),
            'rejected_recommendation_count' => $recommendations->where('status', 'rejected')->count(),
            'last_interaction_at' => $interactions->first()?->responded_at?->toIso8601String(),
        ];
    }

    /**
     * @param  Collection<int, AiInteraction>  $interactions
     * @param  Collection<int, AiRecommendation>  $recommendations
     * @param  Collection<int, AuditLog>  $auditLogs
     * @return array<string, array<string, int|float|string|null>>
     */
    private function buildReviewAnalytics(
        Collection $interactions,
        Collection $recommendations,
        Collection $auditLogs,
    ): array {
        $answeredInteractions = $interactions->where('status', 'answered');
        $citedAnswers = $answeredInteractions->filter(
            fn (AiInteraction $interaction): bool => count($interaction->citations ?? []) > 0,
        );
        $feedbackRatings = $interactions
            ->pluck('feedback_rating')
            ->filter(fn (mixed $rating): bool => $rating !== null)
            ->map(fn (mixed $rating): float => (float) $rating);
        $pendingRecommendations = $recommendations
            ->where('status', 'pending_review')
            ->sortBy('created_at')
            ->values();
        $decidedRecommendations = $recommendations
            ->filter(fn (AiRecommendation $recommendation): bool => $recommendation->decided_at !== null)
            ->sortByDesc('decided_at')
            ->values();
        $citationCoveragePercent = $answeredInteractions->isEmpty()
            ? 0.0
            : round(($citedAnswers->count() / $answeredInteractions->count()) * 100, 1);

        return [
            'answer_quality' => [
                'answered_count' => $answeredInteractions->count(),
                'cited_answer_count' => $citedAnswers->count(),
                'citation_coverage_percent' => $citationCoveragePercent,
                'feedback_recorded_count' => $interactions->filter(
                    fn (AiInteraction $interaction): bool => $interaction->feedback_sentiment !== null
                        || $interaction->feedback_rating !== null
                        || $interaction->feedback_notes !== null,
                )->count(),
                'average_feedback_rating' => $feedbackRatings->isNotEmpty() ? round($feedbackRatings->avg(), 2) : null,
                'positive_feedback_count' => $interactions->where('feedback_sentiment', 'positive')->count(),
                'negative_feedback_count' => $interactions->where('feedback_sentiment', 'negative')->count(),
                'guardrailed_interaction_count' => $interactions->where('status', 'guardrailed')->count(),
            ],
            'recommendation_queue' => [
                'pending_review_count' => $pendingRecommendations->count(),
                'accepted_count' => $recommendations->where('status', 'accepted')->count(),
                'rejected_count' => $recommendations->where('status', 'rejected')->count(),
                'stale_pending_review_count' => $pendingRecommendations->filter(
                    fn (AiRecommendation $recommendation): bool => $recommendation->created_at !== null
                        && $recommendation->created_at->lte(now()->subDay()),
                )->count(),
                'oldest_pending_created_at' => $pendingRecommendations->first()?->created_at?->toIso8601String(),
                'latest_decision_at' => $decidedRecommendations->first()?->decided_at?->toIso8601String(),
            ],
            'audit_activity' => [
                'event_count' => $auditLogs->count(),
                'workspace_view_count' => $auditLogs->where('event_type', 'ai.assistant.workspace.viewed')->count(),
                'interaction_generated_count' => $auditLogs->where('event_type', 'ai.interaction.generated')->count(),
                'feedback_event_count' => $auditLogs->where('event_type', 'ai.interaction.feedback_recorded')->count(),
                'recommendation_generated_count' => $auditLogs->where('event_type', 'ai.recommendation.generated')->count(),
                'recommendation_decision_count' => $auditLogs->where('event_type', 'ai.recommendation.decision_recorded')->count(),
                'last_event_at' => $auditLogs->first()?->created_at?->toIso8601String(),
            ],
        ];
    }

    /**
     * @return Builder<AuditLog>
     */
    private function aiAuditLogQuery(User $actor): Builder
    {
        return AuditLog::query()
            ->where('company_id', $actor->company_id)
            ->where('user_id', $actor->id)
            ->whereIn('event_type', [
                'ai.assistant.workspace.viewed',
                'ai.interaction.generated',
                'ai.interaction.feedback_recorded',
                'ai.recommendation.generated',
                'ai.recommendation.decision_recorded',
            ]);
    }

    /**
     * @return array<string, array<string, mixed>|int|string|null>
     */
    private function mapAuditTimelineEvent(AuditLog $auditLog): array
    {
        $metadata = $auditLog->metadata ?? [];

        return [
            'id' => $auditLog->id,
            'event_type' => $auditLog->event_type,
            'label' => $this->auditEventLabel($auditLog->event_type),
            'summary' => $this->auditEventSummary($auditLog->event_type, $metadata),
            'entity_type' => $auditLog->entity_type,
            'entity_id' => $auditLog->entity_id,
            'created_at' => $auditLog->created_at?->toIso8601String(),
            'metadata' => $metadata,
        ];
    }

    private function auditEventLabel(string $eventType): string
    {
        return match ($eventType) {
            'ai.assistant.workspace.viewed' => 'Workspace viewed',
            'ai.interaction.generated' => 'Answer generated',
            'ai.interaction.feedback_recorded' => 'Feedback recorded',
            'ai.recommendation.generated' => 'Recommendation prepared',
            'ai.recommendation.decision_recorded' => 'Recommendation decision recorded',
            default => 'AI audit event',
        };
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function auditEventSummary(string $eventType, array $metadata): string
    {
        return match ($eventType) {
            'ai.assistant.workspace.viewed' => sprintf(
                'Loaded %d interaction(s) and %d recommendation(s) into the governed assistant workspace.',
                (int) ($metadata['interaction_count'] ?? 0),
                (int) ($metadata['recommendation_count'] ?? 0),
            ),
            'ai.interaction.generated' => sprintf(
                'Generated a %s response for the %s use case.',
                (string) ($metadata['status'] ?? 'reviewable'),
                (string) ($metadata['use_case'] ?? 'assistant'),
            ),
            'ai.interaction.feedback_recorded' => sprintf(
                'Recorded %s feedback%s.',
                (string) ($metadata['sentiment'] ?? 'quality'),
                isset($metadata['rating']) && $metadata['rating'] !== null ? ' with rating '.(int) $metadata['rating'] : '',
            ),
            'ai.recommendation.generated' => sprintf(
                'Prepared a %s recommendation for human review.',
                (string) ($metadata['scenario'] ?? 'governed'),
            ),
            'ai.recommendation.decision_recorded' => sprintf(
                'Recorded an %s decision for the %s recommendation.',
                (string) ($metadata['decision'] ?? 'approved'),
                (string) ($metadata['scenario'] ?? 'governed'),
            ),
            default => 'Captured an AI governance event.',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function mapEmployeeSummary(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'employee_code' => $employee->employee_code,
            'full_name' => $employee->full_name,
            'email' => $employee->email,
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $resolved = trim((string) ($value ?? ''));

        return $resolved === '' ? null : $resolved;
    }
}
