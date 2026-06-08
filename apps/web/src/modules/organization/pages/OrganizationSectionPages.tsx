import {
  OrganizationCompanyProfileWorkspaceView,
  OrganizationCostCentersWorkspaceView,
  OrganizationLocationsWorkspaceView,
  OrganizationStructureWorkspaceView,
} from '../components/OrganizationAdminWorkspace'
import { OrganizationOverviewPage as OrganizationOverviewPageContent } from './OrganizationOverviewPage'
import { useOrganizationRouteWorkspace } from './useOrganizationRouteWorkspace'

export function OrganizationOverviewPage() {
  return <OrganizationOverviewPageContent />
}

export function OrganizationCompanyProfilePage() {
  const workspace = useOrganizationRouteWorkspace()
  return <OrganizationCompanyProfileWorkspaceView workspace={workspace} />
}

export function OrganizationStructurePage() {
  const workspace = useOrganizationRouteWorkspace()
  return <OrganizationStructureWorkspaceView workspace={workspace} />
}

export function OrganizationLocationsPage() {
  const workspace = useOrganizationRouteWorkspace()
  return <OrganizationLocationsWorkspaceView workspace={workspace} />
}

export function OrganizationCostCentersPage() {
  const workspace = useOrganizationRouteWorkspace()
  return <OrganizationCostCentersWorkspaceView workspace={workspace} />
}
