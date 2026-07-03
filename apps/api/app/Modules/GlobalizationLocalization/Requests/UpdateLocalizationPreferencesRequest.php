<?php

namespace App\Modules\GlobalizationLocalization\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocalizationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $supportedCountries = collect((array) config('regionalization.countries', []));
        $supportedLocales = $supportedCountries->pluck('locale')->unique()->values()->all();
        $supportedLanguages = $supportedCountries->pluck('language')->unique()->values()->all();
        $supportedCurrencies = $supportedCountries->pluck('currency')->unique()->values()->all();
        $supportedTimeFormats = collect((array) config('regionalization.time_formats', []))
            ->pluck('code')
            ->values()
            ->all();

        return [
            'locale' => ['sometimes', 'nullable', 'string', 'max:10', Rule::in($supportedLocales)],
            'language' => ['sometimes', 'nullable', 'string', 'max:10', Rule::in($supportedLanguages)],
            'timezone' => ['sometimes', 'nullable', 'timezone:all'],
            'currency' => ['sometimes', 'nullable', 'string', 'size:3', Rule::in($supportedCurrencies)],
            'time_format' => ['sometimes', 'nullable', 'string', 'max:10', Rule::in($supportedTimeFormats)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['locale', 'language', 'timezone', 'currency', 'time_format'] as $field) {
            if (! $this->exists($field)) {
                continue;
            }

            $value = $this->input($field);

            if ($value === null) {
                $normalized[$field] = null;

                continue;
            }

            if (is_string($value)) {
                $trimmed = trim($value);
                $normalized[$field] = $trimmed === '' ? null : $trimmed;
            }
        }

        if (array_key_exists('currency', $normalized) && is_string($normalized['currency'])) {
            $normalized['currency'] = strtoupper($normalized['currency']);
        }

        if (array_key_exists('language', $normalized) && is_string($normalized['language'])) {
            $normalized['language'] = strtolower($normalized['language']);
        }

        if (array_key_exists('time_format', $normalized) && is_string($normalized['time_format'])) {
            $normalized['time_format'] = trim($normalized['time_format']);
        }

        $this->merge($normalized);
    }
}
