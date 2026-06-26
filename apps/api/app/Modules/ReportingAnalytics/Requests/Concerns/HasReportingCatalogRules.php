<?php

namespace App\Modules\ReportingAnalytics\Requests\Concerns;

use Illuminate\Validation\Rule;

trait HasReportingCatalogRules
{
    /**
     * @return list<string>
     */
    protected function reportingDomains(): array
    {
        return [
            'workforce',
            'attendance',
            'leave',
            'payroll',
            'recruitment',
            'performance',
            'learning',
            'operations',
            'cross_domain',
        ];
    }

    /**
     * @return list<string>
     */
    protected function certificationStatuses(): array
    {
        return ['draft', 'under_review', 'certified', 'deprecated'];
    }

    /**
     * @return list<string>
     */
    protected function fieldTypes(): array
    {
        return ['string', 'number', 'currency', 'percentage', 'date', 'datetime', 'boolean', 'status'];
    }

    /**
     * @return list<string>
     */
    protected function filterTypes(): array
    {
        return ['string', 'number', 'currency', 'percentage', 'date', 'datetime', 'boolean', 'status', 'entity'];
    }

    /**
     * @return list<string>
     */
    protected function filterOperators(): array
    {
        return ['eq', 'neq', 'in', 'not_in', 'contains', 'starts_with', 'ends_with', 'lt', 'lte', 'gt', 'gte', 'between', 'date_between'];
    }

    /**
     * @return list<string>
     */
    protected function maskingStrategies(): array
    {
        return ['none', 'redact', 'partial', 'aggregate_only'];
    }

    /**
     * @return list<string>
     */
    protected function reportExportFormats(): array
    {
        return ['csv', 'json'];
    }

    /**
     * @return list<string>
     */
    protected function reportExportExecutionModes(): array
    {
        return ['auto', 'sync', 'async'];
    }

    /**
     * @return list<string>
     */
    protected function reportExportDeliveryTargets(): array
    {
        return ['requestor_download'];
    }

    /**
     * @return list<string>
     */
    protected function reportExportStatuses(): array
    {
        return ['queued', 'processing', 'completed', 'failed', 'expired'];
    }

    /**
     * @return list<string>
     */
    protected function savedViewShareScopes(): array
    {
        return ['private', 'roles', 'company'];
    }

    /**
     * @return list<string>
     */
    protected function savedViewStatuses(): array
    {
        return ['active', 'archived'];
    }

    /**
     * @return list<string>
     */
    protected function reportSubscriptionStatuses(): array
    {
        return ['active', 'paused', 'blocked', 'revoked'];
    }

    /**
     * @return list<string>
     */
    protected function reportSubscriptionChannels(): array
    {
        return ['in_app_notification'];
    }

    /**
     * @return list<string>
     */
    protected function reportSubscriptionDeliveryTargets(): array
    {
        return ['owner_only'];
    }

    /**
     * @return list<string>
     */
    protected function reportSubscriptionFrequencies(): array
    {
        return ['daily', 'weekly', 'monthly'];
    }

    protected function catalogKeyRegex(): string
    {
        return '/^[a-z0-9_.-]+$/';
    }

    protected function catalogKeyRule(): array
    {
        return ['string', 'max:64', 'regex:'.$this->catalogKeyRegex()];
    }

    protected function sourceReferenceRules(string $prefix): array
    {
        return [
            $prefix => ['required', 'array', 'min:1'],
            $prefix.'.*.module' => ['required', 'string', 'max:64'],
            $prefix.'.*.entity' => ['required', 'string', 'max:128'],
            $prefix.'.*.field' => ['nullable', 'string', 'max:128'],
            $prefix.'.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function approvedFieldRules(string $prefix): array
    {
        return [
            $prefix => ['required', 'array', 'min:1'],
            $prefix.'.*.key' => ['required', ...$this->catalogKeyRule()],
            $prefix.'.*.label' => ['required', 'string', 'max:120'],
            $prefix.'.*.type' => ['required', Rule::in($this->fieldTypes())],
            $prefix.'.*.description' => ['nullable', 'string', 'max:500'],
            $prefix.'.*.sensitive' => ['required', 'boolean'],
            $prefix.'.*.masking_strategy' => ['nullable', Rule::in($this->maskingStrategies())],
        ];
    }

    protected function approvedFilterRules(string $prefix): array
    {
        return [
            $prefix => ['sometimes', 'array'],
            $prefix.'.*.key' => ['required', ...$this->catalogKeyRule()],
            $prefix.'.*.label' => ['required', 'string', 'max:120'],
            $prefix.'.*.type' => ['required', Rule::in($this->filterTypes())],
            $prefix.'.*.required' => ['required', 'boolean'],
            $prefix.'.*.operators' => ['required', 'array', 'min:1'],
            $prefix.'.*.operators.*' => ['required', Rule::in($this->filterOperators())],
        ];
    }

    protected function drilldownPathRules(string $prefix): array
    {
        return [
            $prefix => ['sometimes', 'array'],
            $prefix.'.*.key' => ['required', ...$this->catalogKeyRule()],
            $prefix.'.*.label' => ['required', 'string', 'max:120'],
            $prefix.'.*.target_dataset_key' => ['nullable', ...$this->catalogKeyRule()],
            $prefix.'.*.description' => ['nullable', 'string', 'max:500'],
            $prefix.'.*.allowed_filter_keys' => ['sometimes', 'array'],
            $prefix.'.*.allowed_filter_keys.*' => ['required', ...$this->catalogKeyRule()],
        ];
    }

    protected function maskingPostureRules(string $prefix): array
    {
        return [
            $prefix => ['required', 'array'],
            $prefix.'.default_strategy' => ['required', Rule::in($this->maskingStrategies())],
            $prefix.'.sensitive_field_keys' => ['sometimes', 'array'],
            $prefix.'.sensitive_field_keys.*' => ['required', ...$this->catalogKeyRule()],
            $prefix.'.notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
