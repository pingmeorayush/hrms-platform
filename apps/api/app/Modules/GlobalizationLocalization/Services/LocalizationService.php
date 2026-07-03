<?php

namespace App\Modules\GlobalizationLocalization\Services;

use App\Models\Company;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class LocalizationService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @return array{
     *   effective_settings: array<string, mixed>,
     *   tenant_defaults: array<string, mixed>,
     *   supported: array<string, mixed>
     * }
     */
    public function configurationForUser(User $user): array
    {
        $user->loadMissing('company');
        $tenantDefaults = $this->tenantDefaults($user->company);

        return [
            'effective_settings' => $this->effectiveSettingsForUser($user),
            'tenant_defaults' => $tenantDefaults,
            'supported' => $this->supportedOptions(),
        ];
    }

    /**
     * @return array{
     *   country_code: string,
     *   locale: string,
     *   language: string,
     *   timezone: string,
     *   currency: string,
     *   time_format: string,
     *   week_start: string,
     *   expansion_country_codes: list<string>
     * }
     */
    public function tenantDefaults(?Company $company): array
    {
        $fallback = config('regionalization.fallback');
        $countryCode = strtoupper((string) ($company?->country_code ?: $fallback['country_code']));
        $preset = $this->countryPreset($countryCode);

        return [
            'country_code' => $countryCode,
            'locale' => (string) ($company?->locale ?: $preset['locale'] ?? $fallback['locale']),
            'language' => (string) ($company?->language ?: $preset['language'] ?? $fallback['language']),
            'timezone' => (string) ($company?->timezone ?: $preset['timezone'] ?? $fallback['timezone']),
            'currency' => (string) ($company?->currency ?: $preset['currency'] ?? $fallback['currency']),
            'time_format' => (string) ($company?->time_format ?: $preset['time_format'] ?? $fallback['time_format']),
            'week_start' => (string) ($preset['week_start'] ?? $fallback['week_start']),
            'expansion_country_codes' => $this->normalizeCountryCodes($company?->expansion_country_codes),
        ];
    }

    /**
     * @return array{
     *   country_code: string,
     *   locale: string,
     *   language: string,
     *   timezone: string,
     *   currency: string,
     *   time_format: string,
     *   week_start: string,
     *   expansion_country_codes: list<string>,
     *   source: array{
     *     locale: string,
     *     language: string,
     *     timezone: string,
     *     currency: string,
     *     time_format: string
     *   }
     * }
     */
    public function effectiveSettingsForUser(User $user): array
    {
        $user->loadMissing('company');
        $tenantDefaults = $this->tenantDefaults($user->company);

        return [
            'country_code' => $tenantDefaults['country_code'],
            'locale' => $user->locale ?: $tenantDefaults['locale'],
            'language' => $user->language ?: $tenantDefaults['language'],
            'timezone' => $user->timezone ?: $tenantDefaults['timezone'],
            'currency' => $user->currency ?: $tenantDefaults['currency'],
            'time_format' => $user->time_format ?: $tenantDefaults['time_format'],
            'week_start' => $tenantDefaults['week_start'],
            'expansion_country_codes' => $tenantDefaults['expansion_country_codes'],
            'source' => [
                'locale' => $user->locale ? 'user' : 'tenant',
                'language' => $user->language ? 'user' : 'tenant',
                'timezone' => $user->timezone ? 'user' : 'tenant',
                'currency' => $user->currency ? 'user' : 'tenant',
                'time_format' => $user->time_format ? 'user' : 'tenant',
            ],
        ];
    }

    /**
     * @return array{
     *   countries: list<array<string, mixed>>,
     *   languages: list<array<string, string>>,
     *   locales: list<array<string, string>>,
     *   currencies: list<array<string, string>>,
     *   time_formats: list<array<string, string>>
     * }
     */
    public function supportedOptions(): array
    {
        $countries = collect(config('regionalization.countries'))
            ->map(function (array $preset, string $countryCode): array {
                return [
                    'code' => $countryCode,
                    'label' => (string) $preset['label'],
                    'locale' => (string) $preset['locale'],
                    'language' => (string) $preset['language'],
                    'timezone' => (string) $preset['timezone'],
                    'currency' => (string) $preset['currency'],
                    'time_format' => (string) $preset['time_format'],
                    'week_start' => (string) $preset['week_start'],
                    'launch_default' => (bool) ($preset['launch_default'] ?? false),
                    'expansion_placeholder' => (bool) ($preset['expansion_placeholder'] ?? false),
                ];
            })
            ->values()
            ->all();

        $languages = collect($countries)
            ->map(fn (array $country): array => [
                'code' => $country['language'],
                'label' => $this->languageLabel($country['language']),
            ])
            ->unique('code')
            ->values()
            ->all();

        $locales = collect($countries)
            ->map(fn (array $country): array => [
                'code' => $country['locale'],
                'label' => sprintf('%s (%s)', $country['locale'], $country['label']),
            ])
            ->unique('code')
            ->values()
            ->all();

        $currencies = collect($countries)
            ->map(fn (array $country): array => [
                'code' => $country['currency'],
                'label' => sprintf('%s (%s)', $country['currency'], $country['label']),
            ])
            ->unique('code')
            ->values()
            ->all();

        /** @var list<array{code: string, label: string}> $timeFormats */
        $timeFormats = config('regionalization.time_formats');

        return [
            'countries' => $countries,
            'languages' => $languages,
            'locales' => $locales,
            'currencies' => $currencies,
            'time_formats' => $timeFormats,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *   effective_settings: array<string, mixed>,
     *   tenant_defaults: array<string, mixed>,
     *   supported: array<string, mixed>
     * }
     */
    public function updatePreferences(User $user, array $payload, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        return DB::transaction(function () use ($user, $payload, $ipAddress, $userAgent): array {
            $user->loadMissing('company');
            $before = $this->effectiveSettingsForUser($user);

            $normalizedPayload = $this->normalizeUserPreferencePayload($payload);
            if ($normalizedPayload !== []) {
                $user->fill($normalizedPayload);
                $user->save();
            }

            $user->refresh()->loadMissing('company');
            $configuration = $this->configurationForUser($user);

            if ($normalizedPayload !== []) {
                $this->auditLogger->record(
                    eventType: 'localization.preferences.updated',
                    actor: $user,
                    metadata: [
                        'before' => $before,
                        'after' => $configuration['effective_settings'],
                        'updated_fields' => array_keys($normalizedPayload),
                    ],
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                    entityType: 'user',
                    entityId: (string) $user->id,
                );
            }

            return $configuration;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function countryPreset(string $countryCode): array
    {
        /** @var array<string, array<string, mixed>> $countries */
        $countries = config('regionalization.countries');

        return $countries[$countryCode] ?? config('regionalization.countries.'.config('regionalization.fallback.country_code'), []);
    }

    /**
     * @param  mixed  $value
     * @return list<string>
     */
    private function normalizeCountryCodes(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(fn (mixed $item): bool => is_string($item) && $item !== '')
            ->map(fn (string $item): string => strtoupper($item))
            ->unique()
            ->values()
            ->all();
    }

    private function languageLabel(string $languageCode): string
    {
        return match ($languageCode) {
            'de' => 'German',
            default => 'English',
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizeUserPreferencePayload(array $payload): array
    {
        $normalized = [];

        foreach (['locale', 'language', 'timezone', 'currency', 'time_format'] as $field) {
            if (! array_key_exists($field, $payload)) {
                continue;
            }

            $value = $payload[$field];

            if ($value === null) {
                $normalized[$field] = null;

                continue;
            }

            if (! is_string($value)) {
                continue;
            }

            $trimmed = trim($value);

            $normalized[$field] = $trimmed === '' ? null : $trimmed;
        }

        if (array_key_exists('currency', $normalized) && is_string($normalized['currency'])) {
            $normalized['currency'] = strtoupper($normalized['currency']);
        }

        if (array_key_exists('language', $normalized) && is_string($normalized['language'])) {
            $normalized['language'] = strtolower($normalized['language']);
        }

        return $normalized;
    }
}
