<?php

namespace App\Modules\IntegrationsPlatform\Requests\Concerns;

trait HasIntegrationRules
{
    /**
     * @return array<int, string>
     */
    protected function connectionStatuses(): array
    {
        return config('integrations.status_options.connection', ['draft', 'active', 'paused']);
    }

    /**
     * @return array<int, string>
     */
    protected function subscriptionStatuses(): array
    {
        return config('integrations.status_options.subscription', ['active', 'paused', 'disabled']);
    }

    /**
     * @return array<int, string>
     */
    protected function syncJobStatuses(): array
    {
        return [
            ...config('integrations.status_options.job', ['queued', 'running', 'failed', 'completed']),
            'retried',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function systemKeys(): array
    {
        return collect(config('integrations.systems', []))
            ->pluck('key')
            ->filter(fn (mixed $value): bool => is_string($value) && $value !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function eventKeys(): array
    {
        return collect(config('integrations.events', []))
            ->pluck('key')
            ->filter(fn (mixed $value): bool => is_string($value) && $value !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function connectionDirections(): array
    {
        return ['inbound', 'outbound', 'bidirectional'];
    }

    /**
     * @return array<int, string>
     */
    protected function subscriptionDirections(): array
    {
        return ['inbound', 'outbound'];
    }

    /**
     * @return array<int, string>
     */
    protected function authModes(): array
    {
        return ['none', 'bearer', 'hmac_sha256'];
    }
}
