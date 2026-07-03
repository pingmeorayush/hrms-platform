<?php

namespace App\Modules\OrganizationManagement\Requests;

use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyProfileRequest extends FormRequest
{
    use AuthorizesRoutePermissions;

    public function authorize(): bool
    {
        return $this->authorizeFromRoutePermissions();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        $supportedCountries = array_keys((array) config('regionalization.countries', []));
        $supportedLocales = collect((array) config('regionalization.countries', []))
            ->pluck('locale')
            ->unique()
            ->values()
            ->all();
        $supportedLanguages = collect((array) config('regionalization.countries', []))
            ->pluck('language')
            ->unique()
            ->values()
            ->all();
        $supportedCurrencies = collect((array) config('regionalization.countries', []))
            ->pluck('currency')
            ->unique()
            ->values()
            ->all();
        $supportedTimeFormats = collect((array) config('regionalization.time_formats', []))
            ->pluck('code')
            ->all();

        return [
            'name' => ['required', 'string', 'max:255'],
            'subscription_plan' => ['nullable', 'string', 'max:100'],
            'timezone' => ['required', 'timezone:all'],
            'currency' => ['required', 'string', 'size:3', Rule::in($supportedCurrencies)],
            'country_code' => ['required', 'string', 'size:2', Rule::in($supportedCountries)],
            'locale' => ['required', 'string', 'max:10', Rule::in($supportedLocales)],
            'language' => ['required', 'string', 'max:10', Rule::in($supportedLanguages)],
            'time_format' => ['required', 'string', 'max:10', Rule::in($supportedTimeFormats)],
            'expansion_country_codes' => ['sometimes', 'array'],
            'expansion_country_codes.*' => ['required', 'string', 'size:2', Rule::in($supportedCountries)],
        ];
    }
}
