<?php

namespace App\Modules\IntegrationsPlatform\Resources\Concerns;

trait FormatsIntegrationResourceData
{
    /**
     * @param  array<string, mixed>|null  $headers
     * @return array<string, mixed>
     */
    protected function redactHeaders(?array $headers): array
    {
        if (! is_array($headers)) {
            return [];
        }

        $redacted = [];

        foreach ($headers as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            $redacted[$key] = str_contains($normalizedKey, 'authorization')
                || str_contains($normalizedKey, 'token')
                || str_contains($normalizedKey, 'secret')
                || str_contains($normalizedKey, 'signature')
                    ? '[redacted]'
                    : $value;
        }

        return $redacted;
    }

    protected function maskSecret(?string $secret): ?string
    {
        if (! is_string($secret) || $secret === '') {
            return null;
        }

        $suffix = substr($secret, -4);

        return '••••'.$suffix;
    }
}
