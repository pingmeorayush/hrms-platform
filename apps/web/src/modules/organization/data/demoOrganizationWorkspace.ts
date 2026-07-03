import type { AccessSnapshot } from '../../access/types'
import type { OrganizationWorkspaceData } from '../types'

export function buildDemoOrganizationWorkspace(snapshot: AccessSnapshot | null): OrganizationWorkspaceData {
  const companyName = snapshot?.user.tenant.company_name ?? 'Phoenix Demo Company'
  const timezone = snapshot?.user.tenant.timezone ?? 'Asia/Kolkata'
  const currency = snapshot?.user.tenant.currency ?? 'INR'
  const countryCode = snapshot?.user.tenant.country_code ?? 'IN'
  const locale = snapshot?.user.tenant.locale ?? 'en-IN'
  const language = snapshot?.user.tenant.language ?? 'en'
  const timeFormat = snapshot?.user.tenant.time_format ?? '24h'
  const expansionCountryCodes = snapshot?.user.tenant.expansion_country_codes ?? ['US', 'DE']

  return {
    companyProfile: {
      id: snapshot?.user.tenant.company_id ?? 1,
      uuid: '4f834f72-9fa4-4a38-8f56-d45da7f4ef4f',
      name: companyName,
      slug: companyName.toLowerCase().replace(/\s+/g, '-'),
      status: 'active',
      subscription_plan: snapshot?.user.tenant.subscription_plan ?? 'enterprise',
      timezone,
      currency,
      country_code: countryCode,
      locale,
      language,
      time_format: timeFormat,
      expansion_country_codes: expansionCountryCodes,
      created_at: '2026-01-02T09:00:00+05:30',
      updated_at: '2026-06-02T11:15:00+05:30',
    },
    departments: [
      {
        id: 101,
        code: 'ENG',
        name: 'Engineering',
        description: 'Platform, product, and quality engineering.',
        status: 'active',
        created_at: '2026-01-05T10:00:00+05:30',
        updated_at: '2026-05-19T09:45:00+05:30',
      },
      {
        id: 102,
        code: 'PEO',
        name: 'People Operations',
        description: 'HR operations, talent, and employee experience.',
        status: 'active',
        created_at: '2026-01-05T10:00:00+05:30',
        updated_at: '2026-05-21T13:30:00+05:30',
      },
      {
        id: 103,
        code: 'FIN',
        name: 'Finance',
        description: 'Payroll controls and financial governance.',
        status: 'inactive',
        created_at: '2026-01-06T12:30:00+05:30',
        updated_at: '2026-04-18T16:00:00+05:30',
      },
    ],
    designations: [
      {
        id: 201,
        code: 'SWE2',
        name: 'Software Engineer II',
        description: 'Mid-level engineering contributor role.',
        status: 'active',
        created_at: '2026-01-08T09:30:00+05:30',
        updated_at: '2026-05-20T14:10:00+05:30',
      },
      {
        id: 202,
        code: 'HRBP',
        name: 'HR Business Partner',
        description: 'Business partnering and people operations support.',
        status: 'active',
        created_at: '2026-01-08T09:45:00+05:30',
        updated_at: '2026-05-22T10:00:00+05:30',
      },
    ],
    locations: [
      {
        id: 301,
        code: 'BLR-HQ',
        name: 'Bengaluru HQ',
        timezone,
        currency,
        address_line_1: '48 Residency Road',
        address_line_2: 'Level 6',
        city: 'Bengaluru',
        state: 'Karnataka',
        country: 'India',
        postal_code: '560025',
        status: 'active',
        created_at: '2026-01-10T08:30:00+05:30',
        updated_at: '2026-05-25T18:20:00+05:30',
      },
      {
        id: 302,
        code: 'HYD-OPS',
        name: 'Hyderabad Operations',
        timezone,
        currency,
        address_line_1: '77 Financial District',
        address_line_2: '',
        city: 'Hyderabad',
        state: 'Telangana',
        country: 'India',
        postal_code: '500032',
        status: 'active',
        created_at: '2026-01-11T11:00:00+05:30',
        updated_at: '2026-05-18T15:40:00+05:30',
      },
    ],
    costCenters: [
      {
        id: 401,
        code: 'CC-1001',
        name: 'Platform Delivery',
        description: 'Shared platform engineering and delivery budgets.',
        status: 'active',
        created_at: '2026-01-12T09:15:00+05:30',
        updated_at: '2026-05-19T12:20:00+05:30',
      },
      {
        id: 402,
        code: 'CC-1002',
        name: 'People Operations',
        description: 'Talent, onboarding, and employee operations costs.',
        status: 'active',
        created_at: '2026-01-12T10:00:00+05:30',
        updated_at: '2026-05-21T12:05:00+05:30',
      },
    ],
  }
}
