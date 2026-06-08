import { useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useAppSelector } from '../../../app/store/hooks'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { buildDemoOrganizationWorkspace } from '../data/demoOrganizationWorkspace'
import {
  createLocation,
  createOrganizationMaster,
  fetchOrganizationWorkspace,
  updateCompanyProfile,
  updateLocation,
  updateOrganizationMaster,
} from '../api/organizationApi'
import type {
  CompanyProfileFormValues,
  LocationFormValues,
  LocationRecord,
  OrganizationCollection,
  OrganizationMasterFormValues,
  OrganizationMasterRecord,
  OrganizationWorkspaceData,
} from '../types'

const queryScope = 'organization-workspace'

export function useOrganizationWorkspace() {
  const access = useAppSelector((state) => state.access)
  const queryClient = useQueryClient()
  const { snapshot, source } = useAccessSnapshot()
  const [demoData, setDemoData] = useState<OrganizationWorkspaceData>(() =>
    buildDemoOrganizationWorkspace(snapshot),
  )

  const queryKey = useMemo(
    () => [queryScope, access.apiBaseUrl, access.token] as const,
    [access.apiBaseUrl, access.token],
  )

  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0

  const liveQuery = useQuery({
    queryKey,
    queryFn: () => fetchOrganizationWorkspace(access.apiBaseUrl, access.token),
    enabled: liveEnabled,
  })

  const updateCompanyMutation = useMutation({
    mutationFn: (values: CompanyProfileFormValues) =>
      updateCompanyProfile(access.apiBaseUrl, access.token, values),
    onSuccess: (companyProfile) => {
      queryClient.setQueryData<OrganizationWorkspaceData>(queryKey, (current) =>
        current ? { ...current, companyProfile } : current,
      )
    },
  })

  const createMasterMutation = useMutation({
    mutationFn: ({
      collection,
      values,
    }: {
      collection: Exclude<OrganizationCollection, 'locations'>
      values: OrganizationMasterFormValues
    }) => createOrganizationMaster(access.apiBaseUrl, access.token, collection, values),
    onSuccess: (record, variables) => {
      queryClient.setQueryData<OrganizationWorkspaceData>(queryKey, (current) => {
        if (!current) {
          return current
        }

        return {
          ...current,
          [variables.collection]: sortMasterRecords([...current[variables.collection], record]),
        }
      })
    },
  })

  const updateMasterMutation = useMutation({
    mutationFn: ({
      collection,
      id,
      values,
    }: {
      collection: Exclude<OrganizationCollection, 'locations'>
      id: number
      values: OrganizationMasterFormValues
    }) => updateOrganizationMaster(access.apiBaseUrl, access.token, collection, id, values),
    onSuccess: (record, variables) => {
      queryClient.setQueryData<OrganizationWorkspaceData>(queryKey, (current) => {
        if (!current) {
          return current
        }

        return {
          ...current,
          [variables.collection]: sortMasterRecords(
            current[variables.collection].map((item) => (item.id === record.id ? record : item)),
          ),
        }
      })
    },
  })

  const createLocationMutation = useMutation({
    mutationFn: (values: LocationFormValues) => createLocation(access.apiBaseUrl, access.token, values),
    onSuccess: (record) => {
      queryClient.setQueryData<OrganizationWorkspaceData>(queryKey, (current) =>
        current ? { ...current, locations: sortLocations([...current.locations, record]) } : current,
      )
    },
  })

  const updateLocationMutation = useMutation({
    mutationFn: ({ id, values }: { id: number; values: LocationFormValues }) =>
      updateLocation(access.apiBaseUrl, access.token, id, values),
    onSuccess: (record) => {
      queryClient.setQueryData<OrganizationWorkspaceData>(queryKey, (current) =>
        current
          ? {
              ...current,
              locations: sortLocations(current.locations.map((item) => (item.id === record.id ? record : item))),
            }
          : current,
      )
    },
  })

  const data = source === 'demo' ? demoData : liveQuery.data ?? null

  return {
    source,
    data,
    snapshot,
    isLoading: source === 'live' ? liveQuery.isLoading : false,
    error: source === 'live' ? (liveQuery.error as Error | null) ?? null : null,
    canManage: snapshot
      ? snapshot.user.permissions.includes('organization.manage')
      : access.mode === 'demo',
    isSaving:
      updateCompanyMutation.isPending ||
      createMasterMutation.isPending ||
      updateMasterMutation.isPending ||
      createLocationMutation.isPending ||
      updateLocationMutation.isPending,
    async saveCompanyProfile(values: CompanyProfileFormValues) {
      if (source === 'demo') {
        const companyProfile = {
          ...demoData.companyProfile,
          name: values.name.trim(),
          slug: values.name.trim().toLowerCase().replace(/\s+/g, '-'),
          subscription_plan: values.subscription_plan.trim() || null,
          timezone: values.timezone.trim(),
          currency: values.currency.trim().toUpperCase(),
          updated_at: new Date().toISOString(),
        }

        setDemoData((current) => ({ ...current, companyProfile }))
        return companyProfile
      }

      return updateCompanyMutation.mutateAsync(values)
    },
    async saveMasterRecord(
      collection: Exclude<OrganizationCollection, 'locations'>,
      values: OrganizationMasterFormValues,
      id?: number,
    ) {
      if (source === 'demo') {
        const normalized = {
          code: values.code.trim(),
          name: values.name.trim(),
          description: values.description.trim() || null,
          status: values.status,
        }

        if (id) {
          let updatedRecord: OrganizationMasterRecord | null = null

          setDemoData((current) => {
            const items = current[collection].map((item) => {
              if (item.id !== id) {
                return item
              }

              updatedRecord = {
                ...item,
                ...normalized,
                updated_at: new Date().toISOString(),
              }

              return updatedRecord
            })

            return {
              ...current,
              [collection]: sortMasterRecords(items),
            }
          })

          return updatedRecord
        }

        const createdRecord: OrganizationMasterRecord = {
          id: nextId(demoData[collection]),
          ...normalized,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        }

        setDemoData((current) => ({
          ...current,
          [collection]: sortMasterRecords([...current[collection], createdRecord]),
        }))

        return createdRecord
      }

      if (id) {
        return updateMasterMutation.mutateAsync({ collection, id, values })
      }

      return createMasterMutation.mutateAsync({ collection, values })
    },
    async saveLocation(values: LocationFormValues, id?: number) {
      if (source === 'demo') {
        const normalized = normalizeDemoLocation(values)

        if (id) {
          let updatedRecord: LocationRecord | null = null

          setDemoData((current) => {
            const locations = current.locations.map((item) => {
              if (item.id !== id) {
                return item
              }

              updatedRecord = {
                ...item,
                ...normalized,
                updated_at: new Date().toISOString(),
              }

              return updatedRecord
            })

            return {
              ...current,
              locations: sortLocations(locations),
            }
          })

          return updatedRecord
        }

        const createdRecord: LocationRecord = {
          id: nextId(demoData.locations),
          ...normalized,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        }

        setDemoData((current) => ({
          ...current,
          locations: sortLocations([...current.locations, createdRecord]),
        }))

        return createdRecord
      }

      if (id) {
        return updateLocationMutation.mutateAsync({ id, values })
      }

      return createLocationMutation.mutateAsync(values)
    },
  }
}

function nextId(items: Array<{ id: number }>) {
  return items.reduce((max, item) => Math.max(max, item.id), 0) + 1
}

function sortMasterRecords<T extends OrganizationMasterRecord>(items: T[]) {
  return [...items].sort((left, right) => left.name.localeCompare(right.name))
}

function sortLocations(items: LocationRecord[]) {
  return [...items].sort((left, right) => left.name.localeCompare(right.name))
}

function normalizeDemoLocation(values: LocationFormValues) {
  return {
    code: values.code.trim(),
    name: values.name.trim(),
    timezone: values.timezone.trim(),
    currency: values.currency.trim().toUpperCase(),
    address_line_1: values.address_line_1.trim() || null,
    address_line_2: values.address_line_2.trim() || null,
    city: values.city.trim() || null,
    state: values.state.trim() || null,
    country: values.country.trim() || null,
    postal_code: values.postal_code.trim() || null,
    status: values.status,
  }
}
