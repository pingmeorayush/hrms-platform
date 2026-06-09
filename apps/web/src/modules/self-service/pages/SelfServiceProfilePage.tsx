import { Badge } from '../../../shared/ui/badge'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspaceSummaryRow,
  WorkspaceSurface,
} from '../../../shared/ui/workspace'
import { useSelfServiceRouteWorkspace } from './useSelfServiceRouteWorkspace'

export function SelfServiceProfilePage() {
  const workspace = useSelfServiceRouteWorkspace()

  if (workspace.isLoading) {
    return (
      <WorkspacePage>
        <WorkspaceEmptyState
          title="Loading self-service profile"
          copy="We are resolving the linked employee profile and the self-service access contract."
        />
      </WorkspacePage>
    )
  }

  if (workspace.error) {
    return (
      <WorkspacePage>
        <WorkspaceEmptyState
          title="Unable to load the self-service profile"
          copy={workspace.error.message}
        />
      </WorkspacePage>
    )
  }

  if (!workspace.data || !workspace.employee) {
    return (
      <WorkspacePage>
        <WorkspaceEmptyState
          title="No linked employee profile"
          copy="This session does not resolve to an employee profile yet, so self-service profile details are unavailable."
        />
      </WorkspacePage>
    )
  }

  const { employee } = workspace
  const { contacts, addresses, emergency_contacts: emergencyContacts, sensitive_panels: sensitivePanels } = workspace.data.profile

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader>
          <div className="min-w-0 space-y-1">
            <h1 className="text-2xl font-semibold tracking-tight text-foreground">My profile</h1>
            <p className="max-w-3xl text-sm text-muted-foreground">
              Review the linked employee record, employment context, and approved self-service profile details.
            </p>
          </div>
          <WorkspaceHeaderActions>
            <Badge variant={workspace.source === 'demo' ? 'info' : 'neutral'}>
              {workspace.source === 'demo' ? 'Demo self-service profile' : 'Live self-service profile'}
            </Badge>
          </WorkspaceHeaderActions>
        </WorkspaceHeader>

        <WorkspaceContent className="space-y-4">
          <div className="organization-metric-grid">
            <MetricCard label="Profile" value={employee.full_name} caption={employee.employee_code} />
            <MetricCard label="Employment status" value={employee.employment_status.replace(/_/g, ' ')} caption={employee.employment_type.replace(/_/g, ' ')} />
            <MetricCard label="Pending acknowledgements" value={String(workspace.data.documents.summary.pending_acknowledgement_count)} caption="Policy items that still need action" />
            <MetricCard label="Assigned assets" value={String(workspace.data.assets.summary.active_count)} caption="Visible devices and issued equipment" />
          </div>

          <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <WorkspaceSurface>
              <WorkspaceContent>
                <WorkspaceSummaryRow label="Work email" value={employee.email} />
                <WorkspaceSummaryRow label="Phone" value={employee.phone ?? 'Not recorded'} />
                <WorkspaceSummaryRow label="Date of joining" value={formatDate(employee.date_of_joining)} />
                <WorkspaceSummaryRow label="Department" value={employee.department.name} />
                <WorkspaceSummaryRow label="Designation" value={employee.designation.name} />
                <WorkspaceSummaryRow label="Manager" value={employee.manager?.full_name ?? 'Not assigned'} />
                <WorkspaceSummaryRow label="Location" value={employee.location?.name ?? 'Not assigned'} />
                <WorkspaceSummaryRow label="Cost center" value={employee.cost_center?.name ?? 'Not assigned'} />
              </WorkspaceContent>
            </WorkspaceSurface>

            <WorkspaceSurface>
              <WorkspaceContent>
                <WorkspaceSummaryRow label="Primary contact" value={contacts.find((contact) => contact.is_primary)?.value ?? 'Not recorded'} />
                <WorkspaceSummaryRow label="Address types" value={String(addresses.length)} />
                <WorkspaceSummaryRow label="Emergency contacts" value={String(emergencyContacts.length)} />
                <WorkspaceSummaryRow label="Last profile sync" value={formatDateTime(employee.updated_at)} />
                <WorkspaceSummaryRow
                  label="Banking panel"
                  value={sensitivePanels.bank_accounts.visible ? 'Visible' : 'Hidden'}
                />
                <WorkspaceSummaryRow
                  label="Hidden sensitive docs"
                  value={String(workspace.data.documents.summary.hidden_sensitive_count)}
                />
              </WorkspaceContent>
            </WorkspaceSurface>
          </div>

          <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Contacts</h2>
                  <p className="text-sm text-muted-foreground">Primary work and mobile channels approved for self-service visibility.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                {contacts.length ? (
                  contacts.map((contact) => (
                    <WorkspaceSummaryRow
                      key={contact.id}
                      label={`${contact.label ?? contact.type}${contact.is_primary ? ' · primary' : ''}`}
                      value={contact.value}
                    />
                  ))
                ) : (
                  <WorkspaceEmptyState
                    title="No contact channels are visible"
                    copy="Contacts appear here once the linked employee profile has approved self-service contact records."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>

            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Addresses</h2>
                  <p className="text-sm text-muted-foreground">Only approved address records are available in this self-service view.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                {addresses.length ? (
                  addresses.map((address) => (
                    <WorkspaceSummaryRow
                      key={address.id}
                      label={address.type.charAt(0).toUpperCase() + address.type.slice(1)}
                      value={[address.address_line_1, address.address_line_2, address.city, address.state, address.country, address.postal_code].filter(Boolean).join(', ')}
                    />
                  ))
                ) : (
                  <WorkspaceEmptyState
                    title="No approved addresses are visible"
                    copy="Address records show up here once the linked employee profile has approved self-service address visibility."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>
          </div>

          <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Emergency contacts</h2>
                  <p className="text-sm text-muted-foreground">Emergency contact data is read only in the current Sprint 6 scope.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                {emergencyContacts.length ? (
                  emergencyContacts.map((contact) => (
                    <WorkspaceSummaryRow
                      key={contact.id}
                      label={`${contact.name} · ${contact.relationship}`}
                      value={`${contact.phone_number}${contact.email ? ` · ${contact.email}` : ''}`}
                    />
                  ))
                ) : (
                  <WorkspaceEmptyState
                    title="No emergency contacts are visible"
                    copy="Emergency contacts appear here once the linked employee profile has approved them for self-service review."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>

            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Sensitive panels</h2>
                  <p className="text-sm text-muted-foreground">Restricted panels stay hidden or masked until the current session has the required permission.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                <WorkspaceSummaryRow
                  label="Banking details"
                  value={sensitivePanels.bank_accounts.visible ? 'Visible in this session' : 'Sensitive banking is hidden'}
                />
                {!sensitivePanels.bank_accounts.visible && sensitivePanels.bank_accounts.message ? (
                  <p className="text-sm text-muted-foreground">{sensitivePanels.bank_accounts.message}</p>
                ) : null}
              </WorkspaceContent>
            </WorkspaceSurface>
          </div>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}

function MetricCard({ label, value, caption }: { label: string; value: string; caption: string }) {
  return (
    <div className="metric-card">
      <span className="metric-card__label">{label}</span>
      <strong className="metric-card__value">{value}</strong>
      <p className="metric-card__caption">{caption}</p>
    </div>
  )
}

function formatDate(value: string | null) {
  if (!value) {
    return 'Not available'
  }

  return new Intl.DateTimeFormat('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
}

function formatDateTime(value: string | null) {
  if (!value) {
    return 'Not available'
  }

  return new Intl.DateTimeFormat('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(value))
}
