<?php

namespace App\Modules\AIAssistant\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiRecommendationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->ai_conversation_id,
            'scenario' => $this->scenario,
            'title' => $this->title,
            'summary' => $this->summary,
            'rationale' => $this->rationale ?? [],
            'confidence_score' => $this->confidence_score,
            'suggested_actions' => $this->suggested_actions ?? [],
            'supporting_citations' => $this->formatCitations($this->supporting_citations ?? []),
            'status' => $this->status,
            'human_review_required' => $this->human_review_required,
            'decision' => $this->decision,
            'decision_notes' => $this->decision_notes,
            'decided_at' => $this->decided_at?->toIso8601String(),
            'metadata' => $this->metadata ?? (object) [],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'employee' => $this->whenLoaded('employee', function (): ?array {
                if (! $this->employee) {
                    return null;
                }

                return [
                    'id' => $this->employee->id,
                    'employee_code' => $this->employee->employee_code,
                    'full_name' => $this->employee->full_name,
                    'email' => $this->employee->email,
                ];
            }),
            'decided_by' => $this->whenLoaded('decidedBy', fn (): ?array => $this->decidedBy
                ? [
                    'id' => $this->decidedBy->id,
                    'name' => $this->decidedBy->name,
                ]
                : null),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $citations
     * @return list<array<string, mixed>>
     */
    private function formatCitations(array $citations): array
    {
        return collect($citations)
            ->values()
            ->map(function (array $citation, int $index): array {
                $type = (string) ($citation['type'] ?? 'governed_record');
                $rank = (int) ($citation['rank'] ?? ($index + 1));
                $baseScore = match ($type) {
                    'attendance_record' => 0.93,
                    'learning_assignment_target' => 0.91,
                    'policy_acknowledgement' => 0.88,
                    default => 0.82,
                };

                return [
                    'type' => $type,
                    'label' => (string) ($citation['label'] ?? 'Governed source'),
                    'reference' => (string) ($citation['reference'] ?? 'Source reference'),
                    'excerpt' => (string) ($citation['excerpt'] ?? 'Governed source excerpt'),
                    'entity_type' => (string) ($citation['entity_type'] ?? 'record'),
                    'entity_id' => (int) ($citation['entity_id'] ?? 0),
                    'route' => (string) ($citation['route'] ?? '/'),
                    'rank' => $rank,
                    'relevance_score' => isset($citation['relevance_score'])
                        ? (float) $citation['relevance_score']
                        : max(0.55, round($baseScore - (($rank - 1) * 0.04), 2)),
                    'evidence_strength' => (string) ($citation['evidence_strength'] ?? ($rank === 1 ? 'primary' : 'supporting')),
                    'freshness_label' => (string) ($citation['freshness_label'] ?? $this->resolveCitationFreshnessLabel($type, $rank)),
                    'captured_at' => $citation['captured_at'] ?? null,
                ];
            })
            ->all();
    }

    private function resolveCitationFreshnessLabel(string $type, int $rank): string
    {
        return match ($type) {
            'attendance_record' => $rank === 1 ? 'current_attendance_window' : 'recent_attendance_window',
            'learning_assignment_target' => 'active_learning_target',
            'policy_acknowledgement' => 'pending_acknowledgement',
            default => 'governed_record',
        };
    }
}
