<?php

namespace App\Modules\Platform\Notifications\Services;

use App\Models\NotificationRecord;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type NotificationTemplateVariables array<string, mixed>
 * @phpstan-type NotificationTemplateOverrides array{
 *   type?: string,
 *   channel?: string,
 *   title?: string,
 *   message?: string,
 *   priority?: string,
 *   deep_link?: string|null
 * }
 * @phpstan-type DirectNotificationPayload array{
 *   type?: string,
 *   channel?: string,
 *   title: string,
 *   message: string,
 *   priority?: string,
 *   deep_link?: string|null,
 *   data?: array<string, mixed>
 * }
 */
class NotificationService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  NotificationTemplateVariables  $variables
     * @param  NotificationTemplateOverrides  $overrides
     */
    public function sendTemplate(
        string $templateKey,
        User $recipient,
        array $variables = [],
        array $overrides = [],
    ): NotificationRecord {
        $template = NotificationTemplate::query()
            ->where(function (Builder $query) use ($recipient): void {
                $query->where('company_id', $recipient->company_id)
                    ->orWhereNull('company_id');
            })
            ->where('key', $templateKey)
            ->where('status', 'active')
            ->orderByDesc('company_id')
            ->first();

        if (! $template) {
            return NotificationRecord::withoutGlobalScopes()->create([
                'company_id' => $recipient->company_id,
                'user_id' => $recipient->id,
                'type' => $overrides['type'] ?? 'system',
                'channel' => $overrides['channel'] ?? 'in_app',
                'title' => $overrides['title'] ?? 'Notification delivery failed',
                'message' => $overrides['message'] ?? 'A required notification template is missing.',
                'priority' => $overrides['priority'] ?? 'high',
                'status' => 'unread',
                'delivery_status' => 'failed',
                'retry_count' => 1,
                'last_error' => "Missing notification template: {$templateKey}",
                'data' => [
                    'template_key' => $templateKey,
                    'template_variables' => $variables,
                    'overrides' => $overrides,
                ],
            ]);
        }

        $payload = [
            'company_name' => $recipient->company->name ?? 'PhoenixHRMS',
        ] + $variables;

        $title = $overrides['title'] ?? $this->render($template->subject ?? Str::headline($template->name), $payload);
        $message = $overrides['message'] ?? $this->render($template->content, $payload);

        $notification = NotificationRecord::withoutGlobalScopes()->create([
            'company_id' => $recipient->company_id,
            'user_id' => $recipient->id,
            'notification_template_id' => $template->id,
            'type' => $overrides['type'] ?? $template->category,
            'channel' => $overrides['channel'] ?? $template->channel,
            'title' => $title,
            'message' => $message,
            'priority' => $overrides['priority'] ?? 'normal',
            'status' => 'unread',
            'delivery_status' => 'delivered',
            'deep_link' => $overrides['deep_link'] ?? null,
            'delivered_at' => now(),
            'data' => [
                'template_key' => $template->key,
                'template_variables' => $variables,
                'overrides' => $overrides,
            ],
        ]);

        $this->auditLogger->record(
            eventType: 'notification.created',
            actor: $recipient,
            metadata: [
                'notification_id' => $notification->id,
                'template_key' => $template->key,
                'channel' => $notification->channel,
            ],
            entityType: 'notification',
            entityId: (string) $notification->id,
        );

        return $notification;
    }

    /**
     * @param  DirectNotificationPayload  $payload
     */
    public function sendDirect(User $recipient, array $payload, ?User $actor = null): NotificationRecord
    {
        $notification = NotificationRecord::withoutGlobalScopes()->create([
            'company_id' => $recipient->company_id,
            'user_id' => $recipient->id,
            'type' => $payload['type'] ?? 'system',
            'channel' => $payload['channel'] ?? 'in_app',
            'title' => $payload['title'],
            'message' => $payload['message'],
            'priority' => $payload['priority'] ?? 'normal',
            'status' => 'unread',
            'delivery_status' => 'delivered',
            'deep_link' => $payload['deep_link'] ?? null,
            'delivered_at' => now(),
            'data' => $payload['data'] ?? [],
        ]);

        $this->auditLogger->record(
            eventType: 'notification.created',
            actor: $actor ?? $recipient,
            metadata: [
                'notification_id' => $notification->id,
                'channel' => $notification->channel,
            ],
            entityType: 'notification',
            entityId: (string) $notification->id,
        );

        return $notification;
    }

    public function markRead(NotificationRecord $notification, User $actor): NotificationRecord
    {
        $notification->forceFill([
            'status' => 'read',
            'read_at' => now(),
        ])->save();

        $this->auditLogger->record(
            eventType: 'notification.read',
            actor: $actor,
            metadata: ['notification_id' => $notification->id],
            entityType: 'notification',
            entityId: (string) $notification->id,
        );

        return $notification->refresh();
    }

    public function retry(NotificationRecord $notification, User $actor): NotificationRecord
    {
        if ($notification->delivery_status !== 'failed') {
            return $notification;
        }

        return DB::transaction(function () use ($notification, $actor): NotificationRecord {
            $data = $notification->data ?? [];
            $templateKey = $data['template_key'] ?? null;

            if (! $templateKey) {
                throw ValidationException::withMessages([
                    'notification' => ['The failed notification does not contain retry metadata.'],
                ]);
            }

            $template = NotificationTemplate::query()
                ->where(function (Builder $query) use ($notification): void {
                    $query->where('company_id', $notification->company_id)
                        ->orWhereNull('company_id');
                })
                ->where('key', $templateKey)
                ->where('status', 'active')
                ->orderByDesc('company_id')
                ->first();

            if (! $template) {
                $notification->increment('retry_count');

                throw ValidationException::withMessages([
                    'notification' => ['The notification template is still unavailable.'],
                ]);
            }

            $variables = $data['template_variables'] ?? [];
            $recipient = $notification->user()->with('company')->firstOrFail();
            $payload = [
                'company_name' => $recipient->company->name ?? 'PhoenixHRMS',
            ] + $variables;

            $notification->forceFill([
                'notification_template_id' => $template->id,
                'title' => $this->render($template->subject ?? $notification->title, $payload),
                'message' => $this->render($template->content, $payload),
                'delivery_status' => 'delivered',
                'last_error' => null,
                'retry_count' => $notification->retry_count + 1,
                'delivered_at' => now(),
            ])->save();

            $this->auditLogger->record(
                eventType: 'notification.retried',
                actor: $actor,
                metadata: ['notification_id' => $notification->id],
                entityType: 'notification',
                entityId: (string) $notification->id,
            );

            return $notification->refresh();
        });
    }

    /**
     * @param  NotificationTemplateVariables  $variables
     */
    private function render(string $content, array $variables): string
    {
        $rendered = $content;

        foreach ($variables as $key => $value) {
            $rendered = str_replace('{{'.$key.'}}', (string) $value, $rendered);
        }

        return preg_replace('/{{\s*[^}]+\s*}}/', '', $rendered) ?? $rendered;
    }
}
