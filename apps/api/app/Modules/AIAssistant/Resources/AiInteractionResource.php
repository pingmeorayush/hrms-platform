<?php

namespace App\Modules\AIAssistant\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiInteractionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->ai_conversation_id,
            'interaction_type' => $this->interaction_type,
            'use_case' => $this->use_case,
            'question' => $this->question,
            'answer' => $this->answer,
            'status' => $this->status,
            'confidence_score' => $this->confidence_score,
            'citations' => $this->formatCitations($this->citations ?? []),
            'guardrails' => $this->guardrails ?? [],
            'metadata' => $this->metadata ?? (object) [],
            'feedback' => [
                'rating' => $this->feedback_rating,
                'sentiment' => $this->feedback_sentiment,
                'notes' => $this->feedback_notes,
            ],
            'responded_at' => $this->responded_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'conversation' => $this->whenLoaded('conversation', fn (): array => (new AiConversationResource($this->conversation))->resolve()),
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
                    'payslip' => 0.97,
                    'leave_balance' => 0.95,
                    'attendance_record' => 0.94,
                    'learning_assignment_target' => 0.9,
                    'policy_document' => 0.88,
                    'policy_acknowledgement' => 0.84,
                    default => 0.8,
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
            'payslip' => $rank === 1 ? 'current_pay_cycle' : 'recent_pay_cycle',
            'attendance_record' => $rank === 1 ? 'current_attendance_window' : 'recent_attendance_window',
            'leave_balance' => 'active_balance_window',
            'policy_document' => 'current_policy_reference',
            'policy_acknowledgement' => 'pending_acknowledgement',
            'learning_assignment_target' => 'active_learning_target',
            default => 'governed_record',
        };
    }
}
