# Globalization and Localization

## Purpose

The Globalization and Localization module enables PhoenixHRMS to operate across countries, regions, languages, currencies, time zones, and localized compliance contexts.

## Business Value

- Supports international workforce operations
- Improves employee experience in regional contexts
- Reduces localization rework during expansion
- Creates a consistent path for country-specific configuration

## In Scope

- Country and region configuration
- Multi-language support and translation management
- Locale-aware formatting for dates, times, numbers, and currency
- Time zone support and conversions
- Multi-currency handling and exchange rate support
- Holiday management and working-week configuration
- Country-specific compliance and payroll configuration

## Out Of Scope

- Immigration legal services
- International tax filing
- External mobility vendors

## Primary Actors

- Tenant Administrator
- HR
- Payroll
- Localization Administrator

## Core Workflows

- Country, region, and legal-entity setup
- Language and translation catalog management
- Locale-aware rendering in UI, reports, communications, and AI responses
- Holiday and working-week configuration by region or location
- Country-specific compliance profile assignment

## Key Rules

- Timestamps should be stored in UTC and displayed in tenant or user context
- Language fallback behavior must be defined
- Currency and rounding behavior must support compensation and reporting use cases
- Country-specific regulatory settings must be isolated and configurable

## Core Entities

- `countries`
- `regions`
- `locales`
- `translation_catalogs`
- `holiday_calendars`
- `exchange_rates`
- `compliance_profiles`

## Primary APIs

- APIs are not explicitly enumerated in the current OpenAPI draft and should be added during implementation planning

## Dependencies

- Tenant and organization foundations
- Payroll, mobile, notifications, reporting, and document consumers

## Related Sprints

- [Sprint 09: Mobile, Integrations, and Globalization](../sprints/sprint-09-mobile-integrations-globalization.md)

## Source Specs

- `docs/files/PhoenixHRMS Globalization & Localization Platform Specification.txt`
