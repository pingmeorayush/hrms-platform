import type { FormEvent } from 'react'
import { useMemo, useState } from 'react'
import { Link, NavLink, Navigate, Outlet, useLocation, useOutletContext, useParams } from 'react-router-dom'
import { Badge, type BadgeVariant } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../shared/ui/card'
import { Input } from '../../../shared/ui/input'
import { Modal } from '../../../shared/ui/modal'
import { SelectField as AppSelectField } from '../../../shared/ui/select-field'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { Textarea } from '../../../shared/ui/textarea'
import { useOperationFeedback } from '../../../shared/ui/use-operation-feedback'
import {
  type EmployeeAddressPayload,
  type EmployeeContactPayload,
  type EmployeeDocumentUploadPayload,
  type EmployeeEmergencyContactPayload,
  type EmployeeOnboardingTaskPayload,
  type EmployeeProfileUpdatePayload,
  type EmployeePromotionPayload,
  type EmployeeTerminationPayload,
  type EmployeeTransferPayload,
} from '../api/employeeProfileApi'
import { useEmployeeProfileWorkspace } from '../hooks/useEmployeeProfileWorkspace'
import {
  getEmployeeDetailSectionPath,
  getVisibleEmployeeDetailSections,
  matchEmployeeDetailSection,
} from '../navigation'
import type {
  AuditLogEntry,
  EmployeeAddressRecord,
  EmployeeBankAccountCollection,
  EmployeeContactRecord,
  EmployeeDocumentRecord,
  EmployeeEmergencyContactRecord,
  EmployeeOnboardingData,
  EmployeeOnboardingTaskRecord,
  EmployeeRecord,
  EmployeeStatus,
} from '../types'

export function EmployeeDetailShell() {
  const params = useParams()
  const location = useLocation()
  const employeeId = Number(params.employeeId ?? '')
  const resolvedEmployeeId = Number.isNaN(employeeId) ? null : employeeId
  const {
    data,
    source,
    snapshot,
    managerOptions,
    organizationOptions,
    canManage,
    canViewBank,
    canManageBank,
    canViewAudit,
    isLoading,
    error,
    isSaving,
    saveProfile,
    saveContact,
    saveAddress,
    saveEmergencyContact,
    saveOnboardingTask,
    uploadDocument,
    triggerTransfer,
    triggerPromotion,
    triggerTermination,
    downloadDocument,
  } = useEmployeeProfileWorkspace(resolvedEmployeeId)

  const tabs = useMemo(() => getVisibleEmployeeDetailSections(canViewAudit), [canViewAudit])
  const activeDetailSectionId = useMemo(
    () => matchEmployeeDetailSection(location.pathname)?.id ?? 'profile',
    [location.pathname],
  )
  const routeWorkspace = useMemo(
    () => ({
      data,
      source,
      snapshot,
      managerOptions,
      organizationOptions,
      canManage,
      canViewBank,
      canManageBank,
      canViewAudit,
      isLoading,
      error,
      isSaving,
      saveProfile,
      saveContact,
      saveAddress,
      saveEmergencyContact,
      saveOnboardingTask,
      uploadDocument,
      triggerTransfer,
      triggerPromotion,
      triggerTermination,
      downloadDocument,
    }),
    [
      data,
      source,
      snapshot,
      managerOptions,
      organizationOptions,
      canManage,
      canViewBank,
      canManageBank,
      canViewAudit,
      isLoading,
      error,
      isSaving,
      saveProfile,
      saveContact,
      saveAddress,
      saveEmergencyContact,
      saveOnboardingTask,
      uploadDocument,
      triggerTransfer,
      triggerPromotion,
      triggerTermination,
      downloadDocument,
    ],
  )

  const headerBadges = useMemo(() => {
    if (!data) {
      return []
    }

    const badges: Array<{ label: string; variant: BadgeVariant }> = [
      {
        label: statusLabel(data.employee.employment_status),
        variant: statusVariant(data.employee.employment_status),
      },
      {
        label: data.employee.employment_type.replace('_', ' '),
        variant: 'subtle',
      },
    ]

    if (activeDetailSectionId === 'profile') {
      badges.push({
        label: canViewBank ? 'Banking visible' : 'Banking restricted',
        variant: canViewBank ? 'success' : 'warning',
      })
    }

    if (activeDetailSectionId === 'documents' && data.documents.length > 0) {
      badges.push({
        label: `${data.documents.length} documents`,
        variant: 'subtle',
      })
    }

    if (activeDetailSectionId === 'history' && canViewAudit) {
      badges.push({
        label: `${data.auditHistory.items.length} audit events`,
        variant: 'subtle',
      })
    }

    return badges
  }, [activeDetailSectionId, canViewAudit, canViewBank, data])

  return (
    <div className="workspace-stack">
      {isLoading ? <p className="workspace-muted">Loading employee workspace...</p> : null}
      {error ? <p className="workspace-error">{error.message}</p> : null}

      {!isLoading && !error && !data ? (
        <Card>
          <CardHeader>
            <CardTitle>Employee not found</CardTitle>
            <CardDescription>
              The requested employee record could not be found in the current workspace session.
            </CardDescription>
          </CardHeader>
        </Card>
      ) : null}

      {data ? (
        <>
          <section className="employee-header-shell" aria-label="Employee workspace header">
            <Card className="employee-identity-strip">
              <CardContent className="employee-identity-strip__content">
                <div className="employee-identity-strip__summary">
                  <div className="employee-identity-strip__identity">
                    <Link className="employee-identity-strip__back-link" to="/employees/directory">
                      Back to directory
                    </Link>
                    <h2 className="employee-identity-strip__name">{data.employee.full_name}</h2>
                    <p className="employee-identity-strip__summary-line">
                      {`${data.employee.employee_code} · ${data.employee.designation.name} · ${data.employee.department.name}`}
                    </p>
                    <p className="employee-identity-strip__supporting">
                      {`${data.employee.location?.name ?? 'Location unassigned'} · Reports to ${data.employee.manager?.full_name ?? 'Unassigned'} · Joined ${formatDate(
                        data.employee.date_of_joining,
                      )}`}
                    </p>
                  </div>
                  <div className="pill-row pill-row--tight">
                    {headerBadges.map((badge) => (
                      <Badge key={badge.label} variant={badge.variant}>
                        {badge.label}
                      </Badge>
                    ))}
                  </div>
                </div>
                <div className="workspace-facts-strip__content employee-identity-strip__facts">
                  <div className="workspace-facts-strip__item">
                    <span>Manager</span>
                    <strong>{data.employee.manager?.full_name ?? 'Unassigned'}</strong>
                    <small>Reporting line</small>
                  </div>
                  <div className="workspace-facts-strip__item">
                    <span>Assignment</span>
                    <strong>{data.employee.designation.name}</strong>
                    <small>{data.employee.department.name}</small>
                  </div>
                  <div className="workspace-facts-strip__item">
                    <span>Location</span>
                    <strong>{data.employee.location?.name ?? 'Not assigned'}</strong>
                    <small>{data.employee.cost_center?.name ?? 'Cost center pending'}</small>
                  </div>
                  <div className="workspace-facts-strip__item">
                    <span>Session access</span>
                    <strong>{canManage ? 'Manage enabled' : 'Read only'}</strong>
                    <small>{source === 'live' ? 'Live employee APIs' : 'Demo employee APIs'}</small>
                  </div>
                </div>
              </CardContent>
            </Card>

            <section className="employee-operations-bar" aria-label="Employee operations">
              <nav className="workspace-collection-tabs employee-operations-bar__tabs" aria-label="Employee detail sections">
                {tabs.map((tab) => (
                  <NavLink
                    key={tab.id}
                    to={getEmployeeDetailSectionPath(params.employeeId ?? '', tab.id)}
                    className={({ isActive }) =>
                      `workspace-collection-tabs__button${isActive ? ' workspace-collection-tabs__button--active' : ''}`
                    }
                  >
                    {tab.label}
                  </NavLink>
                ))}
              </nav>
            </section>
          </section>

          <Outlet context={routeWorkspace} />
        </>
      ) : null}
    </div>
  )
}

export function EmployeeDetailIndexRedirect() {
  const params = useParams()

  return <Navigate replace to={getEmployeeDetailSectionPath(params.employeeId ?? '', 'profile')} />
}

function useEmployeeDetailRouteWorkspace() {
  return useOutletContext<ReturnType<typeof useEmployeeProfileWorkspace>>()
}

export function EmployeeProfileRouteSection() {
  const workspace = useEmployeeDetailRouteWorkspace()

  if (!workspace.data) {
    return null
  }

  return (
    <EmployeeProfileTab
      employee={workspace.data.employee}
      contacts={workspace.data.contacts}
      addresses={workspace.data.addresses}
      emergencyContacts={workspace.data.emergencyContacts}
      bankAccounts={workspace.data.bankAccounts}
      canManage={workspace.canManage}
      canViewBank={workspace.canViewBank}
      canManageBank={workspace.canManageBank}
      isSaving={workspace.isSaving}
      onSaveProfile={workspace.saveProfile}
      onSaveContact={workspace.saveContact}
      onSaveAddress={workspace.saveAddress}
      onSaveEmergencyContact={workspace.saveEmergencyContact}
    />
  )
}

export function EmployeeLifecycleRouteSection() {
  const workspace = useEmployeeDetailRouteWorkspace()

  if (!workspace.data) {
    return null
  }

  return (
    <EmployeeLifecycleTab
      employee={workspace.data.employee}
      managerOptions={workspace.managerOptions}
      organizationOptions={workspace.organizationOptions}
      canManage={workspace.canManage}
      isSaving={workspace.isSaving}
      onTransfer={workspace.triggerTransfer}
      onPromote={workspace.triggerPromotion}
      onTerminate={workspace.triggerTermination}
    />
  )
}

export function EmployeeOnboardingRouteSection() {
  const workspace = useEmployeeDetailRouteWorkspace()

  if (!workspace.data) {
    return null
  }

  return (
    <EmployeeOnboardingTab
      employee={workspace.data.employee}
      onboarding={workspace.data.onboarding}
      canManage={workspace.canManage}
      isSaving={workspace.isSaving}
      onSaveTask={workspace.saveOnboardingTask}
    />
  )
}

export function EmployeeDocumentsRouteSection() {
  const workspace = useEmployeeDetailRouteWorkspace()

  if (!workspace.data) {
    return null
  }

  return (
    <EmployeeDocumentsTab
      documents={workspace.data.documents}
      canManage={workspace.canManage}
      isSaving={workspace.isSaving}
      onUpload={workspace.uploadDocument}
      onDownload={workspace.downloadDocument}
    />
  )
}

export function EmployeeHistoryRouteSection() {
  const workspace = useEmployeeDetailRouteWorkspace()

  if (!workspace.data) {
    return null
  }

  if (!workspace.canViewAudit) {
    return (
      <Card className="workspace-detail-card">
        <CardHeader>
          <CardTitle>Audit history is restricted in this session</CardTitle>
          <CardDescription>
            This routed surface requires audit visibility from the employee access policy.
          </CardDescription>
        </CardHeader>
      </Card>
    )
  }

  return <EmployeeHistoryTab auditEntries={workspace.data.auditHistory.items} />
}

function EmployeeProfileTab({
  employee,
  contacts,
  addresses,
  emergencyContacts,
  bankAccounts,
  canManage,
  canViewBank,
  canManageBank,
  isSaving,
  onSaveProfile,
  onSaveContact,
  onSaveAddress,
  onSaveEmergencyContact,
}: {
  employee: EmployeeRecord
  contacts: EmployeeContactRecord[]
  addresses: EmployeeAddressRecord[]
  emergencyContacts: EmployeeEmergencyContactRecord[]
  bankAccounts: EmployeeBankAccountCollection | null
  canManage: boolean
  canViewBank: boolean
  canManageBank: boolean
  isSaving: boolean
  onSaveProfile: (payload: EmployeeProfileUpdatePayload) => Promise<unknown>
  onSaveContact: (contactId: number | undefined, payload: EmployeeContactPayload) => Promise<void>
  onSaveAddress: (addressId: number | undefined, payload: EmployeeAddressPayload) => Promise<void>
  onSaveEmergencyContact: (
    emergencyContactId: number | undefined,
    payload: EmployeeEmergencyContactPayload,
  ) => Promise<void>
}) {
  const [selectedContactId, setSelectedContactId] = useState<number | null>(null)
  const [selectedAddressId, setSelectedAddressId] = useState<number | null>(null)
  const [selectedEmergencyContactId, setSelectedEmergencyContactId] = useState<number | null>(null)
  const [isProfileModalOpen, setIsProfileModalOpen] = useState(false)
  const [isContactModalOpen, setIsContactModalOpen] = useState(false)
  const [isAddressModalOpen, setIsAddressModalOpen] = useState(false)
  const [isEmergencyModalOpen, setIsEmergencyModalOpen] = useState(false)
  const { runConfirmedAction } = useOperationFeedback()

  const selectedContact = contacts.find((record) => record.id === selectedContactId) ?? null
  const selectedAddress = addresses.find((record) => record.id === selectedAddressId) ?? null
  const selectedEmergencyContact =
    emergencyContacts.find((record) => record.id === selectedEmergencyContactId) ?? null
  const bankAccountCount = bankAccounts?.items.length ?? 0
  const verifiedBankAccountCount = bankAccounts?.items.filter((account) => Boolean(account.verified_at)).length ?? 0
  const primaryContactCount = contacts.filter((record) => record.is_primary).length
  const addressTypeCount = new Set(addresses.map((record) => record.type)).size
  const priorityEmergencyCount = emergencyContacts.filter((record) => (record.priority ?? 99) <= 2).length
  const profileRows = [
    ['Work email', employee.email],
    ['Phone', employee.phone ?? 'Not provided'],
    ['Date of birth', formatDate(employee.date_of_birth)],
    ['Gender', employee.gender ?? 'Not recorded'],
    ['Marital status', employee.marital_status ?? 'Not recorded'],
    ['Employment type', employee.employment_type.replace(/_/g, ' ')],
    ['Joined', formatDate(employee.date_of_joining)],
    ['Location', employee.location?.name ?? 'Not assigned'],
    ['Last updated', formatDateTime(employee.updated_at ?? employee.created_at ?? null)],
  ] satisfies Array<[string, string]>

  return (
    <div className="workspace-stack employee-profile-stack">
      <Card className="workspace-detail-card">
        <CardHeader className="workspace-collection__header workspace-collection__header--compact">
          <div>
            <CardTitle>Employee profile</CardTitle>
            <CardDescription>Current record values and editable core fields.</CardDescription>
          </div>
          <div className="pill-row">
            <Badge variant={canManage ? 'success' : 'warning'}>{canManage ? 'Edit enabled' : 'Read only'}</Badge>
            <Button variant="primary" size="sm" onClick={() => setIsProfileModalOpen(true)}>
              {canManage ? 'Edit in modal' : 'View details'}
            </Button>
          </div>
        </CardHeader>
        <CardContent className="employee-profile-panel employee-profile-panel--flat">
          <RecordSummaryGrid rows={profileRows} ariaLabel="Employee profile record summary" />
        </CardContent>
      </Card>

      <Card className="workspace-detail-card">
        <CardHeader className="workspace-collection__header workspace-collection__header--compact">
          <div>
            <CardTitle>Sensitive banking</CardTitle>
            <CardDescription>Protected payroll account records.</CardDescription>
          </div>
          <div className="pill-row">
            <Badge variant="subtle">{bankAccountCount} account records</Badge>
            <Badge variant={canViewBank ? 'success' : 'warning'}>
              {canViewBank ? 'Visible to this session' : 'Restricted'}
            </Badge>
            {canViewBank ? <Badge variant="subtle">{verifiedBankAccountCount} verified</Badge> : null}
          </div>
        </CardHeader>
        <CardContent className="employee-bank-panel employee-bank-panel--flat">
          <BankAccountsPanel bankAccounts={bankAccounts} canViewBank={canViewBank} canManageBank={canManageBank} />
        </CardContent>
      </Card>

      <Card className="workspace-detail-card">
        <CardHeader className="workspace-collection__header workspace-collection__header--compact">
          <div>
            <CardTitle>Contacts</CardTitle>
            <CardDescription>Operational contact channels.</CardDescription>
          </div>
          <div className="employee-split-panel__header-actions">
            <Badge variant="subtle">{contacts.length} records</Badge>
            <Badge variant="subtle">{primaryContactCount} primary</Badge>
            {canManage ? (
              <Button
                variant="secondary"
                size="sm"
                onClick={() => {
                  setSelectedContactId(null)
                  setIsContactModalOpen(true)
                }}
              >
                New contact
              </Button>
            ) : null}
          </div>
        </CardHeader>
        <CardContent className="employee-registry-panel">
          {contacts.length ? (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead scope="col">Contact</TableHead>
                  <TableHead scope="col">Channel</TableHead>
                  <TableHead scope="col">Status</TableHead>
                  <TableHead scope="col">Primary</TableHead>
                  <TableHead scope="col">Action</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {contacts.map((record) => (
                  <TableRow key={record.id}>
                    <TableHead scope="row" className="align-top">
                      <strong className="ui-type-body-strong block text-foreground">
                        {record.label ?? contactTypeLabel(record.type)}
                      </strong>
                      <small className="ui-type-caption mt-1 block text-muted-foreground">{record.value}</small>
                    </TableHead>
                    <TableCell className="ui-type-body align-top text-muted-foreground">{contactTypeLabel(record.type)}</TableCell>
                    <TableCell className="align-top">
                      <Badge variant={record.status === 'active' ? 'success' : 'subtle'}>
                        {record.status ?? 'Not set'}
                      </Badge>
                    </TableCell>
                    <TableCell className="align-top">
                      <Badge variant={record.is_primary ? 'info' : 'subtle'}>
                        {record.is_primary ? 'Primary' : 'Secondary'}
                      </Badge>
                    </TableCell>
                    <TableCell className="align-top">
                      <Button
                        variant="secondary"
                        size="sm"
                        onClick={() => {
                          setSelectedContactId(record.id)
                          setIsContactModalOpen(true)
                        }}
                      >
                        {canManage ? 'Edit' : 'View'}
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          ) : (
            <EmptyState title="No contacts yet" copy="Add the first employee contact channel." />
          )}
        </CardContent>
      </Card>

      <Card className="workspace-detail-card">
        <CardHeader className="workspace-collection__header workspace-collection__header--compact">
          <div>
            <CardTitle>Addresses</CardTitle>
            <CardDescription>Residence and office address records.</CardDescription>
          </div>
          <div className="employee-split-panel__header-actions">
            <Badge variant="subtle">{addresses.length} records</Badge>
            <Badge variant="subtle">{addressTypeCount} address types</Badge>
            {canManage ? (
              <Button
                variant="secondary"
                size="sm"
                onClick={() => {
                  setSelectedAddressId(null)
                  setIsAddressModalOpen(true)
                }}
              >
                New address
              </Button>
            ) : null}
          </div>
        </CardHeader>
        <CardContent className="employee-registry-panel">
          {addresses.length ? (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead scope="col">Type</TableHead>
                  <TableHead scope="col">Address</TableHead>
                  <TableHead scope="col">City</TableHead>
                  <TableHead scope="col">Postal code</TableHead>
                  <TableHead scope="col">Action</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {addresses.map((record) => (
                  <TableRow key={record.id}>
                    <TableHead scope="row" className="align-top">
                      <strong className="ui-type-body-strong block text-foreground">
                        {addressTypeLabel(record.type)}
                      </strong>
                      <small className="ui-type-caption mt-1 block text-muted-foreground">{record.country}</small>
                    </TableHead>
                    <TableCell className="ui-type-body align-top text-muted-foreground">
                      <p>{record.address_line_1}</p>
                      <small className="ui-type-caption mt-1 block text-muted-foreground">
                        {record.address_line_2 ?? 'No secondary line'}
                      </small>
                    </TableCell>
                    <TableCell className="ui-type-body align-top text-muted-foreground">{record.city}</TableCell>
                    <TableCell className="ui-type-body align-top text-muted-foreground">{record.postal_code}</TableCell>
                    <TableCell className="align-top">
                      <Button
                        variant="secondary"
                        size="sm"
                        onClick={() => {
                          setSelectedAddressId(record.id)
                          setIsAddressModalOpen(true)
                        }}
                      >
                        {canManage ? 'Edit' : 'View'}
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          ) : (
            <EmptyState title="No addresses yet" copy="Add the first address record." />
          )}
        </CardContent>
      </Card>

      <Card className="workspace-detail-card">
        <CardHeader className="workspace-collection__header workspace-collection__header--compact">
          <div>
            <CardTitle>Emergency contacts</CardTitle>
            <CardDescription>Escalation and safety-readiness contacts.</CardDescription>
          </div>
          <div className="employee-split-panel__header-actions">
            <Badge variant="subtle">{emergencyContacts.length} records</Badge>
            <Badge variant="subtle">{priorityEmergencyCount} priority contacts</Badge>
            {canManage ? (
              <Button
                variant="secondary"
                size="sm"
                onClick={() => {
                  setSelectedEmergencyContactId(null)
                  setIsEmergencyModalOpen(true)
                }}
              >
                New emergency contact
              </Button>
            ) : null}
          </div>
        </CardHeader>
        <CardContent className="employee-registry-panel">
          {emergencyContacts.length ? (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead scope="col">Contact</TableHead>
                  <TableHead scope="col">Relationship</TableHead>
                  <TableHead scope="col">Phone</TableHead>
                  <TableHead scope="col">Priority</TableHead>
                  <TableHead scope="col">Action</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {emergencyContacts.map((record) => (
                  <TableRow key={record.id}>
                    <TableHead scope="row" className="align-top">
                      <strong className="ui-type-body-strong block text-foreground">{record.name}</strong>
                      <small className="ui-type-caption mt-1 block text-muted-foreground">
                        {record.email ?? 'Email not recorded'}
                      </small>
                    </TableHead>
                    <TableCell className="ui-type-body align-top text-muted-foreground">{record.relationship}</TableCell>
                    <TableCell className="ui-type-body align-top text-muted-foreground">{record.phone_number}</TableCell>
                    <TableCell className="align-top">
                      <Badge variant={record.priority && record.priority <= 2 ? 'warning' : 'subtle'}>
                        {record.priority ? `Priority ${record.priority}` : 'Not set'}
                      </Badge>
                    </TableCell>
                    <TableCell className="align-top">
                      <Button
                        variant="secondary"
                        size="sm"
                        onClick={() => {
                          setSelectedEmergencyContactId(record.id)
                          setIsEmergencyModalOpen(true)
                        }}
                      >
                        {canManage ? 'Edit' : 'View'}
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          ) : (
            <EmptyState title="No emergency contacts yet" copy="Add the first emergency-contact entry." />
          )}
        </CardContent>
      </Card>

      <Modal
        open={isProfileModalOpen}
        title="Edit employee profile"
        description="Update core employee identity and assignment fields in a focused modal workflow."
        size="lg"
        onClose={() => setIsProfileModalOpen(false)}
      >
        <ProfileEditor
          key={`${employee.id}:${employee.updated_at ?? 'profile'}`}
          employee={employee}
          canManage={canManage}
          isSaving={isSaving}
          compact
          onSave={(payload) =>
            runConfirmedAction({
              title: 'Save employee profile?',
              description: 'This updates the core employee record used across the admin workspace.',
              confirmLabel: 'Save profile',
              tone: 'warning',
              successTitle: 'Employee profile updated',
              successDescription: 'Profile changes are now visible in the employee workspace.',
              errorTitle: 'Unable to save employee profile',
              action: async () => {
                await onSaveProfile(payload)
                setIsProfileModalOpen(false)
              },
            })
          }
        />
      </Modal>

      <Modal
        open={isContactModalOpen}
        title={selectedContact ? 'Edit contact' : 'Create contact'}
        description="Manage employee contact records in a focused modal workflow."
        onClose={() => setIsContactModalOpen(false)}
      >
        <ContactEditor
          key={`${employee.id}:contact:${selectedContact?.id ?? 'new'}`}
          contact={selectedContact}
          canManage={canManage}
          isSaving={isSaving}
          compact
          onSave={(payload) =>
            runConfirmedAction({
              title: selectedContact ? 'Save contact?' : 'Create contact?',
              description: selectedContact
                ? 'Review the contact details before saving.'
                : 'Create this employee contact record.',
              confirmLabel: selectedContact ? 'Save contact' : 'Create contact',
              tone: selectedContact ? 'warning' : 'default',
              successTitle: selectedContact ? 'Contact updated' : 'Contact created',
              successDescription: 'The contact record is now available in the employee workspace.',
              errorTitle: 'Unable to save contact',
              action: async () => {
                await onSaveContact(selectedContact?.id, payload)
                setSelectedContactId(null)
                setIsContactModalOpen(false)
              },
            })
          }
        />
      </Modal>

      <Modal
        open={isAddressModalOpen}
        title={selectedAddress ? 'Edit address' : 'Create address'}
        description="Manage employee addresses in a focused modal workflow."
        size="lg"
        onClose={() => setIsAddressModalOpen(false)}
      >
        <AddressEditor
          key={`${employee.id}:address:${selectedAddress?.id ?? 'new'}`}
          address={selectedAddress}
          canManage={canManage}
          isSaving={isSaving}
          compact
          onSave={(payload) =>
            runConfirmedAction({
              title: selectedAddress ? 'Save address?' : 'Create address?',
              description: selectedAddress
                ? 'Review the address details before saving.'
                : 'Create this employee address record.',
              confirmLabel: selectedAddress ? 'Save address' : 'Create address',
              tone: selectedAddress ? 'warning' : 'default',
              successTitle: selectedAddress ? 'Address updated' : 'Address created',
              successDescription: 'The address record is now available in the employee workspace.',
              errorTitle: 'Unable to save address',
              action: async () => {
                await onSaveAddress(selectedAddress?.id, payload)
                setSelectedAddressId(null)
                setIsAddressModalOpen(false)
              },
            })
          }
        />
      </Modal>

      <Modal
        open={isEmergencyModalOpen}
        title={selectedEmergencyContact ? 'Edit emergency contact' : 'Create emergency contact'}
        description="Manage emergency contact records in a focused modal workflow."
        onClose={() => setIsEmergencyModalOpen(false)}
      >
        <EmergencyContactEditor
          key={`${employee.id}:emergency:${selectedEmergencyContact?.id ?? 'new'}`}
          emergencyContact={selectedEmergencyContact}
          canManage={canManage}
          isSaving={isSaving}
          compact
          onSave={(payload) =>
            runConfirmedAction({
              title: selectedEmergencyContact ? 'Save emergency contact?' : 'Create emergency contact?',
              description: selectedEmergencyContact
                ? 'Review the contact and escalation details before saving.'
                : 'Create this emergency contact for the employee record.',
              confirmLabel: selectedEmergencyContact ? 'Save contact' : 'Create contact',
              tone: selectedEmergencyContact ? 'warning' : 'default',
              successTitle: selectedEmergencyContact ? 'Emergency contact updated' : 'Emergency contact created',
              successDescription: 'Emergency contact changes are now available in the workspace.',
              errorTitle: 'Unable to save emergency contact',
              action: async () => {
                await onSaveEmergencyContact(selectedEmergencyContact?.id, payload)
                setSelectedEmergencyContactId(null)
                setIsEmergencyModalOpen(false)
              },
            })
          }
        />
      </Modal>
    </div>
  )
}

function EmployeeLifecycleTab({
  employee,
  managerOptions,
  organizationOptions,
  canManage,
  isSaving,
  onTransfer,
  onPromote,
  onTerminate,
}: {
  employee: EmployeeRecord
  managerOptions: EmployeeRecord[]
  organizationOptions: { departments: Array<{ id: number; name: string }>; designations: Array<{ id: number; name: string }>; locations: Array<{ id: number; name: string }>; costCenters: Array<{ id: number; name: string }> } | null
  canManage: boolean
  isSaving: boolean
  onTransfer: (payload: EmployeeTransferPayload) => Promise<void>
  onPromote: (payload: EmployeePromotionPayload) => Promise<void>
  onTerminate: (payload: EmployeeTerminationPayload) => Promise<void>
}) {
  const [isTransferModalOpen, setIsTransferModalOpen] = useState(false)
  const [isPromotionModalOpen, setIsPromotionModalOpen] = useState(false)
  const [isTerminationModalOpen, setIsTerminationModalOpen] = useState(false)
  const { runConfirmedAction } = useOperationFeedback()
  const lifecycleRows = [
    ['Status', statusLabel(employee.employment_status)],
    ['Joined', formatDate(employee.date_of_joining)],
    ['Designation', employee.designation.name],
    ['Department', employee.department.name],
    ['Manager', employee.manager?.full_name ?? 'Unassigned'],
    ['Location', employee.location?.name ?? 'Not assigned'],
  ] satisfies Array<[string, string]>
  const lifecycleOperations = [
    {
      id: 'transfer',
      title: 'Transfer',
      context: `${employee.department.name} · ${employee.manager?.full_name ?? 'Unassigned'}`,
      detail: `${employee.location?.name ?? 'Location unassigned'} · ${employee.cost_center?.name ?? 'Cost center pending'}`,
      purpose: 'Reassign reporting, location, and cost center.',
      actionLabel: 'Open modal',
      actionVariant: 'primary' as const,
      badges: [
        { label: 'Org move', variant: 'subtle' as const },
        { label: employee.location?.name ?? 'Unassigned location', variant: 'subtle' as const },
      ],
      onAction: () => setIsTransferModalOpen(true),
    },
    {
      id: 'promotion',
      title: 'Promotion',
      context: `${employee.designation.name} · ${employee.department.name}`,
      detail: `Manager ${employee.manager?.full_name ?? 'Unassigned'} · Joined ${formatDate(employee.date_of_joining)}`,
      purpose: 'Promote role and aligned reporting context.',
      actionLabel: 'Open modal',
      actionVariant: 'primary' as const,
      badges: [{ label: 'Role uplift', variant: 'subtle' as const }],
      onAction: () => setIsPromotionModalOpen(true),
    },
    {
      id: 'termination',
      title: 'Termination',
      context: `${statusLabel(employee.employment_status)} · Joined ${formatDate(employee.date_of_joining)}`,
      detail: employee.termination_reason ?? 'Termination reason not recorded',
      purpose: 'Move the workforce state into history with explicit confirmation.',
      actionLabel: 'Open modal',
      actionVariant: 'danger' as const,
      badges: [{ label: 'High impact', variant: 'warning' as const }],
      onAction: () => setIsTerminationModalOpen(true),
    },
  ] as const

  if (!canManage) {
    return (
      <div className="workspace-stack">
        <Card className="workspace-detail-card">
          <CardHeader className="workspace-collection__header workspace-collection__header--compact">
            <div>
              <CardTitle>Lifecycle status</CardTitle>
              <CardDescription>Current employment state for this record.</CardDescription>
            </div>
          </CardHeader>
          <CardContent className="employee-profile-panel employee-profile-panel--flat">
            <RecordSummaryGrid rows={lifecycleRows} ariaLabel="Lifecycle status summary" />
          </CardContent>
        </Card>

        <Card className="workspace-detail-card">
          <CardHeader className="workspace-collection__header workspace-collection__header--compact">
            <CardTitle>Lifecycle actions are read only in this session</CardTitle>
            <CardDescription>
              Managers and reviewers can inspect the employee state here, but transfer, promotion, and termination remain restricted to employee-management roles.
            </CardDescription>
          </CardHeader>
        </Card>
      </div>
    )
  }

  return (
    <div className="workspace-stack">
      <Card className="workspace-detail-card">
        <CardHeader className="workspace-collection__header workspace-collection__header--compact">
          <div>
            <CardTitle>Lifecycle operations</CardTitle>
            <CardDescription>
              {`${statusLabel(employee.employment_status)} · Joined ${formatDate(employee.date_of_joining)} · ${employee.designation.name} · ${employee.department.name}`}
            </CardDescription>
          </div>
        </CardHeader>
        <CardContent className="employee-registry-panel">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead scope="col">Operation</TableHead>
                <TableHead scope="col">Current context</TableHead>
                <TableHead scope="col">Purpose</TableHead>
                <TableHead scope="col">Action</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {lifecycleOperations.map((operation) => (
                <TableRow key={operation.id}>
                  <TableHead scope="row" className="align-top">
                    <div className="ui-table-stack">
                      <strong className="ui-table-primary block">{operation.title}</strong>
                    </div>
                    <div className="ui-table-badge-row mt-2">
                      {operation.badges.map((badge) => (
                        <Badge key={`${operation.id}-${badge.label}`} variant={badge.variant}>
                          {badge.label}
                        </Badge>
                      ))}
                    </div>
                  </TableHead>
                  <TableCell className="align-top">
                    <div className="ui-table-stack">
                      <p className="ui-table-body-copy">{operation.context}</p>
                      <small className="ui-table-secondary block">{operation.detail}</small>
                    </div>
                  </TableCell>
                  <TableCell className="ui-table-body-muted align-top">{operation.purpose}</TableCell>
                  <TableCell className="ui-table-action-cell align-top">
                    <Button variant={operation.actionVariant} size="sm" onClick={operation.onAction}>
                      {operation.actionLabel}
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <Modal
        open={isTransferModalOpen}
        title="Transfer employee"
        description="Update reporting, location, and cost-center assignments in a focused modal workflow."
        size="lg"
        onClose={() => setIsTransferModalOpen(false)}
      >
        <TransferEditor
          employee={employee}
          managerOptions={managerOptions}
          organizationOptions={organizationOptions}
          isSaving={isSaving}
          compact
          onSave={(payload) =>
            runConfirmedAction({
              title: 'Confirm employee transfer?',
              description: 'This changes the employee assignment and reporting context across the workspace.',
              confirmLabel: 'Confirm transfer',
              tone: 'warning',
              successTitle: 'Employee transferred',
              successDescription: 'Transfer details are now visible in the employee workspace.',
              errorTitle: 'Unable to transfer employee',
              action: async () => {
                await onTransfer(payload)
                setIsTransferModalOpen(false)
              },
            })
          }
        />
      </Modal>

      <Modal
        open={isPromotionModalOpen}
        title="Promote employee"
        description="Update promotion details in a focused modal workflow."
        size="lg"
        onClose={() => setIsPromotionModalOpen(false)}
      >
        <PromotionEditor
          employee={employee}
          managerOptions={managerOptions}
          organizationOptions={organizationOptions}
          isSaving={isSaving}
          compact
          onSave={(payload) =>
            runConfirmedAction({
              title: 'Confirm employee promotion?',
              description: 'This updates designation and related organizational context for the employee.',
              confirmLabel: 'Confirm promotion',
              tone: 'warning',
              successTitle: 'Employee promoted',
              successDescription: 'Promotion details are now reflected in the employee workspace.',
              errorTitle: 'Unable to promote employee',
              action: async () => {
                await onPromote(payload)
                setIsPromotionModalOpen(false)
              },
            })
          }
        />
      </Modal>

      <Modal
        open={isTerminationModalOpen}
        title="Terminate employee"
        description="Complete employee termination in a focused modal workflow with explicit confirmation."
        size="lg"
        onClose={() => setIsTerminationModalOpen(false)}
      >
        <TerminationEditor
          employee={employee}
          isSaving={isSaving}
          compact
          onSave={(payload) =>
            runConfirmedAction({
              title: 'Confirm employee termination?',
              description: 'This changes the employee lifecycle state and should be reviewed carefully before continuing.',
              confirmLabel: 'Confirm termination',
              tone: 'danger',
              successTitle: 'Employee terminated',
              successDescription: 'Lifecycle status is now reflected in the employee workspace.',
              errorTitle: 'Unable to terminate employee',
              action: async () => {
                await onTerminate(payload)
                setIsTerminationModalOpen(false)
              },
            })
          }
        />
      </Modal>
    </div>
  )
}

function EmployeeOnboardingTab({
  employee,
  onboarding,
  canManage,
  isSaving,
  onSaveTask,
}: {
  employee: EmployeeRecord
  onboarding: EmployeeOnboardingData
  canManage: boolean
  isSaving: boolean
  onSaveTask: (taskId: number | undefined, payload: EmployeeOnboardingTaskPayload) => Promise<void>
}) {
  const [selectedTaskId, setSelectedTaskId] = useState<number | null>(null)
  const [isTaskModalOpen, setIsTaskModalOpen] = useState(false)
  const { runConfirmedAction } = useOperationFeedback()
  const selectedTask = onboarding.items.find((item) => item.id === selectedTaskId) ?? null
  const incompleteTaskCount = onboarding.summary.pending_count + onboarding.summary.in_progress_count
  const onboardingSummary = `${onboarding.summary.completed_count} complete · ${incompleteTaskCount} incomplete · ${onboarding.summary.progress_percentage}% progress`

  return (
    <div className="workspace-stack">
      <Card className="workspace-detail-card">
        <CardHeader className="workspace-collection__header workspace-collection__header--compact">
          <div>
            <CardTitle>Onboarding checklist</CardTitle>
            <CardDescription>{`${employee.full_name} · ${onboardingSummary}`}</CardDescription>
          </div>
          <div className="employee-split-panel__header-actions">
            <Badge variant="subtle">{onboarding.items.length} tasks</Badge>
            <Badge variant="subtle">{incompleteTaskCount} incomplete</Badge>
            <Button
              variant="primary"
              size="sm"
              onClick={() => {
                setSelectedTaskId(null)
                setIsTaskModalOpen(true)
              }}
            >
              New task
            </Button>
          </div>
        </CardHeader>
        <CardContent className="employee-registry-panel">
          {onboarding.items.length ? (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead scope="col">Task</TableHead>
                  <TableHead scope="col">Owner</TableHead>
                  <TableHead scope="col">Due date</TableHead>
                  <TableHead scope="col">Status</TableHead>
                  <TableHead scope="col">Action</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {onboarding.items.map((task) => (
                  <TableRow key={task.id}>
                    <TableHead scope="row" className="align-top">
                      <div className="ui-table-stack">
                        <strong className="ui-table-primary block">{task.title}</strong>
                        <small className="ui-table-secondary block">{task.category}</small>
                      </div>
                    </TableHead>
                    <TableCell className="ui-table-body-muted align-top">
                      {task.assignee_type.replace(/_/g, ' ')} ·{' '}
                      {task.task_type ? task.task_type.replace(/_/g, ' ') : 'general task'}
                    </TableCell>
                    <TableCell className="ui-table-body-muted align-top">
                      {task.due_date ? formatDate(task.due_date) : 'No due date'}
                    </TableCell>
                    <TableCell className="align-top">
                      <Badge variant={task.status === 'completed' ? 'success' : task.status === 'in_progress' ? 'info' : 'neutral'}>
                        {task.status.replace(/_/g, ' ')}
                      </Badge>
                    </TableCell>
                    <TableCell className="ui-table-action-cell align-top">
                      <Button
                        size="sm"
                        onClick={() => {
                          setSelectedTaskId(task.id)
                          setIsTaskModalOpen(true)
                        }}
                      >
                        Open modal
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          ) : (
            <div className="empty-panel">
              <h3 className="empty-panel__title">No onboarding tasks yet</h3>
              <p className="empty-panel__copy">Create the first onboarding checklist item from the guided modal workflow.</p>
            </div>
          )}
        </CardContent>
      </Card>

      <Modal
        open={isTaskModalOpen}
        title={selectedTask ? 'Edit onboarding task' : 'Create onboarding task'}
        description="Manage onboarding checklist tasks in a focused modal workflow."
        size="lg"
        onClose={() => setIsTaskModalOpen(false)}
      >
        <OnboardingTaskEditor
          key={`${employee.id}:task:${selectedTask?.id ?? 'new'}`}
          task={selectedTask}
          canManage={canManage}
          isSaving={isSaving}
          compact
          onSave={(payload) =>
            runConfirmedAction({
              title: selectedTask ? 'Save onboarding task?' : 'Create onboarding task?',
              description: selectedTask
                ? 'Review the checklist changes before saving.'
                : 'Create this onboarding task for the employee.',
              confirmLabel: selectedTask ? 'Save task' : 'Create task',
              tone: selectedTask ? 'warning' : 'default',
              successTitle: selectedTask ? 'Onboarding task updated' : 'Onboarding task created',
              successDescription: 'Checklist changes are now visible in the onboarding tab.',
              errorTitle: 'Unable to save onboarding task',
              action: async () => {
                await onSaveTask(selectedTask?.id, payload)
                setSelectedTaskId(null)
                setIsTaskModalOpen(false)
              },
            })
          }
        />
      </Modal>
    </div>
  )
}

function EmployeeDocumentsTab({
  documents,
  canManage,
  isSaving,
  onUpload,
  onDownload,
}: {
  documents: EmployeeDocumentRecord[]
  canManage: boolean
  isSaving: boolean
  onUpload: (payload: EmployeeDocumentUploadPayload) => Promise<void>
  onDownload: (document: EmployeeDocumentRecord) => Promise<void>
}) {
  const expiringDocumentCount = documents.filter((document) => Boolean(document.expiry_date)).length
  const [isUploadModalOpen, setIsUploadModalOpen] = useState(false)
  const { runConfirmedAction } = useOperationFeedback()

  return (
    <Card className="workspace-detail-card">
      <CardHeader className="workspace-collection__header workspace-collection__header--compact">
        <div>
          <CardTitle>Employee documents</CardTitle>
          <CardDescription>Protected file records and upload controls in one routed panel.</CardDescription>
        </div>
        <div className="employee-split-panel__header-actions">
          <Badge variant="subtle">{documents.length} files</Badge>
          <Badge variant="subtle">{expiringDocumentCount} with expiry</Badge>
          <Button variant="primary" size="sm" onClick={() => setIsUploadModalOpen(true)}>
            Upload in modal
          </Button>
        </div>
      </CardHeader>
      <CardContent className="employee-registry-panel">
        {documents.length ? (
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead scope="col">Document</TableHead>
                <TableHead scope="col">File</TableHead>
                <TableHead scope="col">Expiry</TableHead>
                <TableHead scope="col">Updated</TableHead>
                <TableHead scope="col">Action</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {documents.map((document) => (
                <TableRow key={document.id}>
                  <TableHead scope="row" className="align-top">
                    <div className="ui-table-stack">
                      <strong className="ui-table-primary block">{document.document_type}</strong>
                      <small className="ui-table-secondary block">
                        {formatBytes(document.file_size_bytes)} · {document.mime_type}
                      </small>
                    </div>
                  </TableHead>
                  <TableCell className="ui-table-body-muted align-top">
                    {document.original_file_name}
                  </TableCell>
                  <TableCell className="align-top">
                    <Badge variant={document.expiry_date ? 'warning' : 'success'}>
                      {document.expiry_date ? formatDate(document.expiry_date) : 'No expiry'}
                    </Badge>
                  </TableCell>
                  <TableCell className="ui-table-body-muted align-top">
                    {formatDateTime(document.updated_at ?? document.created_at ?? null)}
                  </TableCell>
                  <TableCell className="ui-table-action-cell align-top">
                    <Button size="sm" onClick={() => onDownload(document)}>
                      Download
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        ) : (
          <div className="empty-panel">
            <h3 className="empty-panel__title">No documents uploaded</h3>
            <p className="empty-panel__copy">Upload the first employee document from the guided modal workflow.</p>
          </div>
        )}
      </CardContent>

      <Modal
        open={isUploadModalOpen}
        title="Upload employee document"
        description="Add protected employee documents from a focused modal workflow."
        onClose={() => setIsUploadModalOpen(false)}
      >
        <DocumentUploadEditor
          canManage={canManage}
          isSaving={isSaving}
          compact
          onSave={(payload) =>
            runConfirmedAction({
              title: 'Upload this employee document?',
              description: 'The file will be attached to the protected employee document store.',
              confirmLabel: 'Upload document',
              tone: 'warning',
              successTitle: 'Document uploaded',
              successDescription: 'The file is now available in the employee documents collection.',
              errorTitle: 'Unable to upload document',
              action: async () => {
                await onUpload(payload)
                setIsUploadModalOpen(false)
              },
            })
          }
        />
      </Modal>
    </Card>
  )
}

function EmployeeHistoryTab({ auditEntries }: { auditEntries: AuditLogEntry[] }) {
  const actorCount = new Set(auditEntries.map((entry) => entry.user?.email ?? entry.user?.name ?? 'system')).size
  const latestAuditTimestamp = auditEntries[0]?.created_at ?? null

  return (
    <Card className="workspace-detail-card">
      <CardHeader className="workspace-collection__header workspace-collection__header--compact">
        <div>
          <CardTitle>Audit history</CardTitle>
          <CardDescription>Lifecycle, onboarding, and document events from the protected audit stream.</CardDescription>
        </div>
        <div className="employee-split-panel__header-actions">
          <Badge variant="subtle">{auditEntries.length} events</Badge>
          <Badge variant="subtle">{actorCount} actors</Badge>
          {latestAuditTimestamp ? <Badge variant="subtle">Latest {formatDate(latestAuditTimestamp)}</Badge> : null}
        </div>
      </CardHeader>
      <CardContent>
        {auditEntries.length ? (
          <div className="workspace-collection-table-wrap">
            <table className="workspace-collection-table workspace-collection-table--employee-audit">
              <colgroup>
                <col style={{ width: '22%' }} />
                <col style={{ width: '24%' }} />
                <col style={{ width: '14%' }} />
                <col style={{ width: '16%' }} />
                <col style={{ width: '24%' }} />
              </colgroup>
              <thead className="workspace-collection-table__head">
                <tr>
                  <th scope="col">Event</th>
                  <th scope="col">Summary</th>
                  <th scope="col">Actor</th>
                  <th scope="col">Time</th>
                  <th scope="col">Metadata</th>
                </tr>
              </thead>
              <tbody>
                {auditEntries.map((entry) => (
                  <tr className="workspace-collection-table__row" key={entry.id}>
                    <th scope="row" className="workspace-collection-table__primary">
                      <strong>{auditEventLabel(entry.event_type)}</strong>
                      <small>{entry.entity_type ?? 'employee'} · {entry.entity_id ?? 'n/a'}</small>
                    </th>
                    <td className="workspace-collection-table__secondary">
                      <p>{entry.user?.email ?? 'System-originated event'}</p>
                    </td>
                    <td className="workspace-collection-table__status">
                      <Badge variant="subtle">{entry.user?.name ?? 'System'}</Badge>
                    </td>
                    <td className="workspace-collection-table__meta">
                      <small>{entry.created_at ? formatDateTime(entry.created_at) : 'Timestamp pending'}</small>
                    </td>
                    <td className="workspace-collection-table__action">
                      <span className="workspace-collection-table__hint">{formatMetadata(entry.metadata)}</span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <div className="empty-panel">
            <h3 className="empty-panel__title">No audit events yet</h3>
            <p className="empty-panel__copy">Employee lifecycle events will appear here once actions are recorded.</p>
          </div>
        )}
      </CardContent>
    </Card>
  )
}

function ProfileEditor({
  employee,
  canManage,
  isSaving,
  compact = false,
  onSave,
}: {
  employee: EmployeeRecord
  canManage: boolean
  isSaving: boolean
  compact?: boolean
  onSave: (payload: EmployeeProfileUpdatePayload) => Promise<unknown>
}) {
  const [values, setValues] = useState({
    first_name: employee.first_name,
    middle_name: employee.middle_name ?? '',
    last_name: employee.last_name,
    email: employee.email,
    phone: employee.phone ?? '',
    date_of_birth: employee.date_of_birth ?? '',
    gender: employee.gender ?? '',
    marital_status: employee.marital_status ?? '',
    employment_type: employee.employment_type,
  })
  const [message, setMessage] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage(null)
    setFormError(null)

    if (!values.first_name.trim() || !values.last_name.trim() || !values.email.trim()) {
      setFormError('First name, last name, and email are required.')
      return
    }

    try {
      await onSave({
        first_name: values.first_name.trim(),
        middle_name: values.middle_name.trim() || null,
        last_name: values.last_name.trim(),
        email: values.email.trim(),
        phone: values.phone.trim() || null,
        date_of_birth: values.date_of_birth || null,
        gender: values.gender.trim() || null,
        marital_status: values.marital_status.trim() || null,
        employment_type: values.employment_type.trim(),
      })
      setMessage('Employee profile saved successfully.')
    } catch (caughtError) {
      setFormError((caughtError as Error).message)
    }
  }

  return (
    <form className={`workspace-form${compact ? ' workspace-form--compact' : ''}`} onSubmit={handleSubmit}>
      <div className={`workspace-form-grid employee-editor-grid${compact ? ' employee-editor-grid--compact' : ''}`}>
        <Field label="First name">
          <Input
            value={values.first_name}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, first_name: event.target.value }))}
          />
        </Field>
        <Field label="Middle name">
          <Input
            value={values.middle_name}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, middle_name: event.target.value }))}
          />
        </Field>
        <Field label="Last name">
          <Input
            value={values.last_name}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, last_name: event.target.value }))}
          />
        </Field>
        <Field label="Email">
          <Input
            type="email"
            value={values.email}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, email: event.target.value }))}
          />
        </Field>
        <Field label="Phone">
          <Input
            value={values.phone}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, phone: event.target.value }))}
          />
        </Field>
        <Field label="Date of birth">
          <Input
            type="date"
            value={values.date_of_birth}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, date_of_birth: event.target.value }))}
          />
        </Field>
        <Field label="Gender">
          <Input
            value={values.gender}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, gender: event.target.value }))}
            placeholder="e.g. female"
          />
        </Field>
        <Field label="Marital status">
          <Input
            value={values.marital_status}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, marital_status: event.target.value }))}
            placeholder="e.g. single"
          />
        </Field>
        <Field label="Employment type">
          <Input
            value={values.employment_type}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, employment_type: event.target.value }))}
          />
        </Field>
      </div>

      <FormNotice error={formError} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={!canManage || isSaving}>
          Save profile changes
        </Button>
        {!canManage ? <span className="workspace-muted">This session can review profile fields but not change them.</span> : null}
      </div>
    </form>
  )
}

function ContactEditor({
  contact,
  canManage,
  isSaving,
  compact = false,
  onSave,
}: {
  contact: EmployeeContactRecord | null
  canManage: boolean
  isSaving: boolean
  compact?: boolean
  onSave: (payload: EmployeeContactPayload) => Promise<void>
}) {
  const [values, setValues] = useState({
    type: contact?.type ?? 'email',
    label: contact?.label ?? '',
    value: contact?.value ?? '',
    is_primary: contact?.is_primary ?? false,
    status: contact?.status ?? 'active',
    notes: contact?.notes ?? '',
  })
  const [message, setMessage] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage(null)
    setFormError(null)

    if (!values.value.trim()) {
      setFormError('Contact value is required.')
      return
    }

    try {
      await onSave({
        type: values.type,
        label: values.label.trim() || null,
        value: values.value.trim(),
        is_primary: values.is_primary,
        status: values.status,
        notes: values.notes.trim() || null,
      })
      setMessage(contact ? 'Contact updated.' : 'Contact added.')
    } catch (caughtError) {
      setFormError((caughtError as Error).message)
    }
  }

  return (
    <form className={`workspace-form${compact ? ' workspace-form--compact' : ''}`} onSubmit={handleSubmit}>
      <div className={`workspace-form-grid employee-editor-grid${compact ? ' employee-editor-grid--compact' : ''}`}>
        <SelectField
          label="Type"
          value={values.type}
          disabled={!canManage || isSaving}
          onChange={(value) => setValues((current) => ({ ...current, type: value as EmployeeContactPayload['type'] }))}
          options={[
            ['email', 'Email'],
            ['phone', 'Phone'],
            ['mobile', 'Mobile'],
            ['whatsapp', 'WhatsApp'],
            ['other', 'Other'],
          ]}
        />
        <Field label="Label">
          <Input
            value={values.label}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, label: event.target.value }))}
            placeholder="Work email"
          />
        </Field>
        <Field label="Value">
          <Input
            value={values.value}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, value: event.target.value }))}
          />
        </Field>
        <SelectField
          label="Status"
          value={values.status ?? 'active'}
          disabled={!canManage || isSaving}
          onChange={(value) => setValues((current) => ({ ...current, status: value as 'active' | 'inactive' }))}
          options={[
            ['active', 'Active'],
            ['inactive', 'Inactive'],
          ]}
        />
      </div>

      <CheckboxField
        label="Primary for this contact type"
        checked={values.is_primary}
        disabled={!canManage || isSaving}
        onChange={(checked) => setValues((current) => ({ ...current, is_primary: checked }))}
      />

      <Field label="Notes">
        <Textarea
          rows={compact ? 2 : 3}
          value={values.notes}
          disabled={!canManage || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, notes: event.target.value }))}
        />
      </Field>

      <FormNotice error={formError} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={!canManage || isSaving}>
          {contact ? 'Save contact' : 'Add contact'}
        </Button>
      </div>
    </form>
  )
}

function AddressEditor({
  address,
  canManage,
  isSaving,
  compact = false,
  onSave,
}: {
  address: EmployeeAddressRecord | null
  canManage: boolean
  isSaving: boolean
  compact?: boolean
  onSave: (payload: EmployeeAddressPayload) => Promise<void>
}) {
  const [values, setValues] = useState({
    type: address?.type ?? 'current',
    address_line_1: address?.address_line_1 ?? '',
    address_line_2: address?.address_line_2 ?? '',
    city: address?.city ?? '',
    state: address?.state ?? '',
    country: address?.country ?? 'India',
    postal_code: address?.postal_code ?? '',
    notes: address?.notes ?? '',
  })
  const [message, setMessage] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage(null)
    setFormError(null)

    if (!values.address_line_1.trim() || !values.city.trim() || !values.country.trim() || !values.postal_code.trim()) {
      setFormError('Address line 1, city, country, and postal code are required.')
      return
    }

    try {
      await onSave({
        type: values.type,
        address_line_1: values.address_line_1.trim(),
        address_line_2: values.address_line_2.trim() || null,
        city: values.city.trim(),
        state: values.state.trim() || null,
        country: values.country.trim(),
        postal_code: values.postal_code.trim(),
        notes: values.notes.trim() || null,
      })
      setMessage(address ? 'Address updated.' : 'Address added.')
    } catch (caughtError) {
      setFormError((caughtError as Error).message)
    }
  }

  return (
    <form className={`workspace-form${compact ? ' workspace-form--compact' : ''}`} onSubmit={handleSubmit}>
      <div className={`workspace-form-grid employee-editor-grid${compact ? ' employee-editor-grid--compact' : ''}`}>
        <SelectField
          label="Type"
          value={values.type}
          disabled={!canManage || isSaving}
          onChange={(value) => setValues((current) => ({ ...current, type: value as EmployeeAddressPayload['type'] }))}
          options={[
            ['current', 'Current'],
            ['permanent', 'Permanent'],
            ['office', 'Office'],
          ]}
        />
        <Field label="Address line 1">
          <Input
            value={values.address_line_1}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, address_line_1: event.target.value }))}
          />
        </Field>
        <Field label="Address line 2">
          <Input
            value={values.address_line_2}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, address_line_2: event.target.value }))}
          />
        </Field>
        <Field label="City">
          <Input
            value={values.city}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, city: event.target.value }))}
          />
        </Field>
        <Field label="State">
          <Input
            value={values.state}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, state: event.target.value }))}
          />
        </Field>
        <Field label="Country">
          <Input
            value={values.country}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, country: event.target.value }))}
          />
        </Field>
        <Field label="Postal code">
          <Input
            value={values.postal_code}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, postal_code: event.target.value }))}
          />
        </Field>
      </div>

      <Field label="Notes">
        <Textarea
          rows={compact ? 2 : 3}
          value={values.notes}
          disabled={!canManage || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, notes: event.target.value }))}
        />
      </Field>

      <FormNotice error={formError} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={!canManage || isSaving}>
          {address ? 'Save address' : 'Add address'}
        </Button>
      </div>
    </form>
  )
}

function EmergencyContactEditor({
  emergencyContact,
  canManage,
  isSaving,
  compact = false,
  onSave,
}: {
  emergencyContact: EmployeeEmergencyContactRecord | null
  canManage: boolean
  isSaving: boolean
  compact?: boolean
  onSave: (payload: EmployeeEmergencyContactPayload) => Promise<void>
}) {
  const [values, setValues] = useState({
    name: emergencyContact?.name ?? '',
    relationship: emergencyContact?.relationship ?? '',
    phone_number: emergencyContact?.phone_number ?? '',
    email: emergencyContact?.email ?? '',
    address: emergencyContact?.address ?? '',
    priority: emergencyContact?.priority ? String(emergencyContact.priority) : '1',
    notes: emergencyContact?.notes ?? '',
  })
  const [message, setMessage] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage(null)
    setFormError(null)

    if (!values.name.trim() || !values.relationship.trim() || !values.phone_number.trim()) {
      setFormError('Name, relationship, and phone number are required.')
      return
    }

    try {
      await onSave({
        name: values.name.trim(),
        relationship: values.relationship.trim(),
        phone_number: values.phone_number.trim(),
        email: values.email.trim() || null,
        address: values.address.trim() || null,
        priority: Number(values.priority) || null,
        notes: values.notes.trim() || null,
      })
      setMessage(emergencyContact ? 'Emergency contact updated.' : 'Emergency contact added.')
    } catch (caughtError) {
      setFormError((caughtError as Error).message)
    }
  }

  return (
    <form className={`workspace-form${compact ? ' workspace-form--compact' : ''}`} onSubmit={handleSubmit}>
      <div className={`workspace-form-grid employee-editor-grid${compact ? ' employee-editor-grid--compact' : ''}`}>
        <Field label="Name">
          <Input
            value={values.name}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, name: event.target.value }))}
          />
        </Field>
        <Field label="Relationship">
          <Input
            value={values.relationship}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, relationship: event.target.value }))}
          />
        </Field>
        <Field label="Phone number">
          <Input
            value={values.phone_number}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, phone_number: event.target.value }))}
          />
        </Field>
        <Field label="Email">
          <Input
            type="email"
            value={values.email}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, email: event.target.value }))}
          />
        </Field>
        <Field label="Priority">
          <Input
            type="number"
            min="1"
            max="9"
            value={values.priority}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, priority: event.target.value }))}
          />
        </Field>
      </div>

      <Field label="Address">
        <Textarea
          rows={compact ? 2 : 3}
          value={values.address}
          disabled={!canManage || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, address: event.target.value }))}
        />
      </Field>

      <Field label="Notes">
        <Textarea
          rows={compact ? 2 : 3}
          value={values.notes}
          disabled={!canManage || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, notes: event.target.value }))}
        />
      </Field>

      <FormNotice error={formError} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={!canManage || isSaving}>
          {emergencyContact ? 'Save emergency contact' : 'Add emergency contact'}
        </Button>
      </div>
    </form>
  )
}

function BankAccountsPanel({
  bankAccounts,
  canViewBank,
  canManageBank,
}: {
  bankAccounts: EmployeeBankAccountCollection | null
  canViewBank: boolean
  canManageBank: boolean
}) {
  if (!canViewBank) {
    return (
      <div className="empty-panel">
        <h3 className="empty-panel__title">Sensitive banking is hidden</h3>
        <p className="empty-panel__copy">
          This session does not have the bank-visibility permission granted by the current backend policy.
        </p>
      </div>
    )
  }

  if (!bankAccounts || bankAccounts.items.length === 0) {
    return (
      <div className="empty-panel">
        <h3 className="empty-panel__title">No bank accounts on file</h3>
        <p className="empty-panel__copy">Bank accounts will appear here when the protected payroll-ready record is added.</p>
      </div>
    )
  }

  return (
    <Table className="employee-bank-table">
      <colgroup>
        <col style={{ width: '24%' }} />
        <col style={{ width: '28%' }} />
        <col style={{ width: '14%' }} />
        <col style={{ width: '16%' }} />
        <col style={{ width: '18%' }} />
      </colgroup>
      <TableHeader>
        <TableRow>
          <TableHead scope="col">Bank</TableHead>
          <TableHead scope="col">Account</TableHead>
          <TableHead scope="col">Verification</TableHead>
          <TableHead scope="col">Updated</TableHead>
          <TableHead scope="col">Access</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {bankAccounts.items.map((account) => (
          <TableRow key={account.id}>
            <TableHead scope="row" className="align-top">
              <strong className="block text-sm font-semibold text-foreground">{account.bank_name}</strong>
              <small className="mt-1 block text-xs text-muted-foreground">
                {account.account_holder_name} · {account.is_primary ? 'Primary account' : 'Secondary account'}
              </small>
            </TableHead>
            <TableCell className="align-top text-sm text-muted-foreground">
              <p>{account.account_number}</p>
              <small className="mt-1 block text-xs text-muted-foreground">
                {account.ifsc_code ? `IFSC ${account.ifsc_code}` : 'IFSC not recorded'}
              </small>
            </TableCell>
            <TableCell className="align-top">
              <Badge variant={account.verified_at ? 'success' : 'warning'}>
                {account.verified_at ? 'Verified' : 'Pending'}
              </Badge>
            </TableCell>
            <TableCell className="align-top text-sm text-muted-foreground">
              {formatDateTime(account.updated_at ?? account.created_at ?? null)}
            </TableCell>
            <TableCell className="align-top">
              <div className="pill-row pill-row--tight">
                <Badge variant={account.sensitive_access === 'full' ? 'success' : 'warning'}>
                  {account.sensitive_access === 'full' ? 'Full fields' : 'Masked fields'}
                </Badge>
                <Badge variant="subtle">{canManageBank ? 'Manage-enabled' : 'View-only'}</Badge>
              </div>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}

function RecordSummaryGrid({
  rows,
  ariaLabel = 'Record summary',
}: {
  rows: Array<[string, string]>
  ariaLabel?: string
}) {
  return (
    <div className="employee-summary-grid" aria-label={ariaLabel}>
      {rows.map(([label, value]) => (
        <div className="employee-summary-grid__item" key={label}>
          <span className="employee-summary-grid__label">{label}</span>
          <strong className="employee-summary-grid__value">{value}</strong>
        </div>
      ))}
    </div>
  )
}

function TransferEditor({
  employee,
  managerOptions,
  organizationOptions,
  isSaving,
  compact = false,
  onSave,
}: {
  employee: EmployeeRecord
  managerOptions: EmployeeRecord[]
  organizationOptions: { departments: Array<{ id: number; name: string }>; locations: Array<{ id: number; name: string }>; costCenters: Array<{ id: number; name: string }> } | null
  isSaving: boolean
  compact?: boolean
  onSave: (payload: EmployeeTransferPayload) => Promise<void>
}) {
  const [values, setValues] = useState({
    effective_date: todayDate(),
    department_id: String(employee.department.id),
    manager_id: employee.manager ? String(employee.manager.id) : '',
    location_id: employee.location ? String(employee.location.id) : '',
    cost_center_id: employee.cost_center ? String(employee.cost_center.id) : '',
    notes: '',
  })
  const [message, setMessage] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage(null)
    setFormError(null)

    if (!values.effective_date) {
      setFormError('Effective date is required.')
      return
    }

    try {
      await onSave({
        effective_date: values.effective_date,
        department_id: Number(values.department_id) || undefined,
        manager_id: values.manager_id ? Number(values.manager_id) : null,
        location_id: values.location_id ? Number(values.location_id) : null,
        cost_center_id: values.cost_center_id ? Number(values.cost_center_id) : null,
        notes: values.notes.trim() || null,
      })
      setMessage('Transfer recorded successfully.')
    } catch (caughtError) {
      setFormError((caughtError as Error).message)
    }
  }

  if (!organizationOptions) {
    return <p className="workspace-muted">Loading organization options for lifecycle actions...</p>
  }

  return (
    <form className={`workspace-form${compact ? ' workspace-form--compact' : ''}`} onSubmit={handleSubmit}>
      <div className={`workspace-form-grid employee-editor-grid${compact ? ' employee-editor-grid--compact' : ''}`}>
        <Field label="Effective date">
          <Input
            type="date"
            value={values.effective_date}
            disabled={isSaving}
            onChange={(event) => setValues((current) => ({ ...current, effective_date: event.target.value }))}
          />
        </Field>
        <SelectField
          label="Department"
          value={values.department_id}
          disabled={isSaving}
          onChange={(value) => setValues((current) => ({ ...current, department_id: value }))}
          options={organizationOptions.departments.map((record) => [String(record.id), record.name])}
        />
        <SelectField
          label="Manager"
          value={values.manager_id}
          disabled={isSaving}
          onChange={(value) => setValues((current) => ({ ...current, manager_id: value }))}
          options={[
            ['', 'Unassigned'],
            ...managerOptions.map((record) => [String(record.id), record.full_name] as [string, string]),
          ]}
        />
        <SelectField
          label="Location"
          value={values.location_id}
          disabled={isSaving}
          onChange={(value) => setValues((current) => ({ ...current, location_id: value }))}
          options={[
            ['', 'Unassigned'],
            ...organizationOptions.locations.map((record) => [String(record.id), record.name] as [string, string]),
          ]}
        />
        <SelectField
          label="Cost center"
          value={values.cost_center_id}
          disabled={isSaving}
          onChange={(value) => setValues((current) => ({ ...current, cost_center_id: value }))}
          options={[
            ['', 'Unassigned'],
            ...organizationOptions.costCenters.map((record) => [String(record.id), record.name] as [string, string]),
          ]}
        />
      </div>

      <Field label="Notes">
        <Textarea
          rows={compact ? 2 : 3}
          value={values.notes}
          disabled={isSaving}
          onChange={(event) => setValues((current) => ({ ...current, notes: event.target.value }))}
        />
      </Field>

      <FormNotice error={formError} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={isSaving}>
          Record transfer
        </Button>
      </div>
    </form>
  )
}

function PromotionEditor({
  employee,
  managerOptions,
  organizationOptions,
  isSaving,
  compact = false,
  onSave,
}: {
  employee: EmployeeRecord
  managerOptions: EmployeeRecord[]
  organizationOptions: { departments: Array<{ id: number; name: string }>; designations: Array<{ id: number; name: string }>; locations: Array<{ id: number; name: string }>; costCenters: Array<{ id: number; name: string }> } | null
  isSaving: boolean
  compact?: boolean
  onSave: (payload: EmployeePromotionPayload) => Promise<void>
}) {
  const [values, setValues] = useState({
    effective_date: todayDate(),
    designation_id: String(employee.designation.id),
    department_id: String(employee.department.id),
    manager_id: employee.manager ? String(employee.manager.id) : '',
    location_id: employee.location ? String(employee.location.id) : '',
    cost_center_id: employee.cost_center ? String(employee.cost_center.id) : '',
    notes: '',
  })
  const [message, setMessage] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage(null)
    setFormError(null)

    if (!values.effective_date || !values.designation_id) {
      setFormError('Effective date and designation are required.')
      return
    }

    try {
      await onSave({
        effective_date: values.effective_date,
        designation_id: Number(values.designation_id),
        department_id: Number(values.department_id) || undefined,
        manager_id: values.manager_id ? Number(values.manager_id) : null,
        location_id: values.location_id ? Number(values.location_id) : null,
        cost_center_id: values.cost_center_id ? Number(values.cost_center_id) : null,
        notes: values.notes.trim() || null,
      })
      setMessage('Promotion recorded successfully.')
    } catch (caughtError) {
      setFormError((caughtError as Error).message)
    }
  }

  if (!organizationOptions) {
    return <p className="workspace-muted">Loading organization options for lifecycle actions...</p>
  }

  return (
    <form className={`workspace-form${compact ? ' workspace-form--compact' : ''}`} onSubmit={handleSubmit}>
      <div className={`workspace-form-grid employee-editor-grid${compact ? ' employee-editor-grid--compact' : ''}`}>
        <Field label="Effective date">
          <Input
            type="date"
            value={values.effective_date}
            disabled={isSaving}
            onChange={(event) => setValues((current) => ({ ...current, effective_date: event.target.value }))}
          />
        </Field>
        <SelectField
          label="Designation"
          value={values.designation_id}
          disabled={isSaving}
          onChange={(value) => setValues((current) => ({ ...current, designation_id: value }))}
          options={organizationOptions.designations.map((record) => [String(record.id), record.name])}
        />
        <SelectField
          label="Department"
          value={values.department_id}
          disabled={isSaving}
          onChange={(value) => setValues((current) => ({ ...current, department_id: value }))}
          options={organizationOptions.departments.map((record) => [String(record.id), record.name])}
        />
        <SelectField
          label="Manager"
          value={values.manager_id}
          disabled={isSaving}
          onChange={(value) => setValues((current) => ({ ...current, manager_id: value }))}
          options={[
            ['', 'Unassigned'],
            ...managerOptions.map((record) => [String(record.id), record.full_name] as [string, string]),
          ]}
        />
        <SelectField
          label="Location"
          value={values.location_id}
          disabled={isSaving}
          onChange={(value) => setValues((current) => ({ ...current, location_id: value }))}
          options={[
            ['', 'Unassigned'],
            ...organizationOptions.locations.map((record) => [String(record.id), record.name] as [string, string]),
          ]}
        />
        <SelectField
          label="Cost center"
          value={values.cost_center_id}
          disabled={isSaving}
          onChange={(value) => setValues((current) => ({ ...current, cost_center_id: value }))}
          options={[
            ['', 'Unassigned'],
            ...organizationOptions.costCenters.map((record) => [String(record.id), record.name] as [string, string]),
          ]}
        />
      </div>

      <Field label="Notes">
        <Textarea
          rows={compact ? 2 : 3}
          value={values.notes}
          disabled={isSaving}
          onChange={(event) => setValues((current) => ({ ...current, notes: event.target.value }))}
        />
      </Field>

      <FormNotice error={formError} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={isSaving}>
          Record promotion
        </Button>
      </div>
    </form>
  )
}

function TerminationEditor({
  employee,
  isSaving,
  compact = false,
  onSave,
}: {
  employee: EmployeeRecord
  isSaving: boolean
  compact?: boolean
  onSave: (payload: EmployeeTerminationPayload) => Promise<void>
}) {
  const [values, setValues] = useState({
    termination_date: todayDate(),
    reason: employee.termination_reason ?? '',
    notes: '',
  })
  const [message, setMessage] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage(null)
    setFormError(null)

    if (!values.termination_date || !values.reason.trim()) {
      setFormError('Termination date and reason are required.')
      return
    }

    try {
      await onSave({
        termination_date: values.termination_date,
        reason: values.reason.trim(),
        notes: values.notes.trim() || null,
      })
      setMessage('Termination recorded successfully.')
    } catch (caughtError) {
      setFormError((caughtError as Error).message)
    }
  }

  return (
    <form className={`workspace-form${compact ? ' workspace-form--compact' : ''}`} onSubmit={handleSubmit}>
      <div className={`workspace-form-grid employee-editor-grid${compact ? ' employee-editor-grid--compact' : ''}`}>
        <Field label="Termination date">
          <Input
            type="date"
            value={values.termination_date}
            disabled={isSaving}
            onChange={(event) => setValues((current) => ({ ...current, termination_date: event.target.value }))}
          />
        </Field>
        <Field label="Reason">
          <Input
            value={values.reason}
            disabled={isSaving}
            onChange={(event) => setValues((current) => ({ ...current, reason: event.target.value }))}
          />
        </Field>
      </div>

      <Field label="Notes">
        <Textarea
          rows={compact ? 2 : 4}
          value={values.notes}
          disabled={isSaving}
          onChange={(event) => setValues((current) => ({ ...current, notes: event.target.value }))}
        />
      </Field>

      <FormNotice error={formError} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={isSaving}>
          Record termination
        </Button>
      </div>
    </form>
  )
}

function OnboardingTaskEditor({
  task,
  canManage,
  isSaving,
  compact = false,
  onSave,
}: {
  task: EmployeeOnboardingTaskRecord | null
  canManage: boolean
  isSaving: boolean
  compact?: boolean
  onSave: (payload: EmployeeOnboardingTaskPayload) => Promise<void>
}) {
  const [values, setValues] = useState<{
    title: string
    category: NonNullable<EmployeeOnboardingTaskPayload['category']>
    task_type: EmployeeOnboardingTaskPayload['task_type'] | 'none'
    assignee_type: NonNullable<EmployeeOnboardingTaskPayload['assignee_type']>
    status: NonNullable<EmployeeOnboardingTaskPayload['status']>
    sort_order: string
    due_date: string
    notes: string
  }>({
    title: task?.title ?? '',
    category: task?.category ?? 'hr',
    task_type: task?.task_type ?? 'complete_forms',
    assignee_type: task?.assignee_type ?? 'employee',
    status: task?.status ?? 'pending',
    sort_order: String(task?.sort_order ?? 10),
    due_date: task?.due_date ?? '',
    notes: task?.notes ?? '',
  })
  const [message, setMessage] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage(null)
    setFormError(null)

    if (!values.title.trim()) {
      setFormError('Task title is required.')
      return
    }

    try {
      await onSave({
        title: values.title.trim(),
        category: values.category as EmployeeOnboardingTaskPayload['category'],
        task_type: values.task_type === 'none' ? null : (values.task_type as EmployeeOnboardingTaskPayload['task_type']),
        assignee_type: values.assignee_type as EmployeeOnboardingTaskPayload['assignee_type'],
        status: values.status as EmployeeOnboardingTaskPayload['status'],
        sort_order: Number(values.sort_order) || 10,
        due_date: values.due_date || null,
        notes: values.notes.trim() || null,
      })
      setMessage(task ? 'Onboarding task updated.' : 'Onboarding task created.')
    } catch (caughtError) {
      setFormError((caughtError as Error).message)
    }
  }

  return (
    <form className={`workspace-form${compact ? ' workspace-form--compact' : ''}`} onSubmit={handleSubmit}>
      <div className={`workspace-form-grid employee-editor-grid${compact ? ' employee-editor-grid--compact' : ''}`}>
        <Field label="Title">
          <Input
            value={values.title}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, title: event.target.value }))}
          />
        </Field>
        <SelectField
          label="Category"
          value={values.category}
          disabled={!canManage || isSaving}
          onChange={(value) =>
            setValues((current) => ({
              ...current,
              category: value as NonNullable<EmployeeOnboardingTaskPayload['category']>,
            }))
          }
          options={[
            ['hr', 'HR'],
            ['it', 'IT'],
            ['manager', 'Manager'],
            ['department', 'Department'],
            ['compliance', 'Compliance'],
            ['training', 'Training'],
            ['other', 'Other'],
          ]}
        />
        <SelectField
          label="Task type"
          value={values.task_type ?? 'none'}
          disabled={!canManage || isSaving}
          onChange={(value) =>
            setValues((current) => ({
              ...current,
              task_type: value as EmployeeOnboardingTaskPayload['task_type'] | 'none',
            }))
          }
          options={[
            ['complete_forms', 'Complete forms'],
            ['read_policy', 'Read policy'],
            ['submit_documents', 'Submit documents'],
            ['complete_training', 'Complete training'],
            ['attend_session', 'Attend session'],
            ['meet_manager', 'Meet manager'],
            ['setup_equipment', 'Setup equipment'],
            ['other', 'Other'],
            ['none', 'None'],
          ]}
        />
        <SelectField
          label="Assignee"
          value={values.assignee_type}
          disabled={!canManage || isSaving}
          onChange={(value) =>
            setValues((current) => ({
              ...current,
              assignee_type: value as NonNullable<EmployeeOnboardingTaskPayload['assignee_type']>,
            }))
          }
          options={[
            ['employee', 'Employee'],
            ['manager', 'Manager'],
            ['hr', 'HR'],
            ['it_team', 'IT team'],
            ['facilities', 'Facilities'],
            ['security', 'Security'],
            ['other', 'Other'],
          ]}
        />
        <SelectField
          label="Status"
          value={values.status}
          disabled={!canManage || isSaving}
          onChange={(value) =>
            setValues((current) => ({
              ...current,
              status: value as NonNullable<EmployeeOnboardingTaskPayload['status']>,
            }))
          }
          options={[
            ['pending', 'Pending'],
            ['in_progress', 'In progress'],
            ['completed', 'Completed'],
            ['skipped', 'Skipped'],
          ]}
        />
        <Field label="Sort order">
          <Input
            type="number"
            min="0"
            max="999"
            value={values.sort_order}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, sort_order: event.target.value }))}
          />
        </Field>
        <Field label="Due date">
          <Input
            type="date"
            value={values.due_date}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, due_date: event.target.value }))}
          />
        </Field>
      </div>

      <Field label="Notes">
        <Textarea
          rows={compact ? 2 : 3}
          value={values.notes}
          disabled={!canManage || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, notes: event.target.value }))}
        />
      </Field>

      <FormNotice error={formError} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={!canManage || isSaving}>
          {task ? 'Save task' : 'Create task'}
        </Button>
      </div>
    </form>
  )
}

function DocumentUploadEditor({
  canManage,
  isSaving,
  compact = false,
  onSave,
}: {
  canManage: boolean
  isSaving: boolean
  compact?: boolean
  onSave: (payload: EmployeeDocumentUploadPayload) => Promise<void>
}) {
  const [values, setValues] = useState({
    document_type: '',
    expiry_date: '',
    notes: '',
    file: null as File | null,
  })
  const [message, setMessage] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage(null)
    setFormError(null)

    if (!values.document_type.trim() || !values.file) {
      setFormError('Document type and file are required.')
      return
    }

    try {
      await onSave({
        document_type: values.document_type.trim(),
        expiry_date: values.expiry_date || null,
        notes: values.notes.trim() || null,
        file: values.file,
      })
      setValues({
        document_type: '',
        expiry_date: '',
        notes: '',
        file: null,
      })
      setMessage('Document uploaded successfully.')
    } catch (caughtError) {
      setFormError((caughtError as Error).message)
    }
  }

  return (
    <form className={`workspace-form${compact ? ' workspace-form--compact' : ''}`} onSubmit={handleSubmit}>
      <Field label="Document type">
        <Input
          value={values.document_type}
          disabled={!canManage || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, document_type: event.target.value }))}
          placeholder="e.g. Signed contract"
        />
      </Field>
      <Field label="Expiry date">
        <Input
          type="date"
          value={values.expiry_date}
          disabled={!canManage || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, expiry_date: event.target.value }))}
        />
      </Field>
      <Field label="Notes">
        <Textarea
          rows={compact ? 2 : 3}
          value={values.notes}
          disabled={!canManage || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, notes: event.target.value }))}
        />
      </Field>
      <Field label="File">
        <Input
          type="file"
          disabled={!canManage || isSaving}
          onChange={(event) =>
            setValues((current) => ({
              ...current,
              file: event.target.files?.[0] ?? null,
            }))
          }
          accept=".pdf,.docx,.png,.jpg,.jpeg"
        />
      </Field>

      <FormNotice error={formError} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={!canManage || isSaving}>
          Upload document
        </Button>
        {!canManage ? <span className="workspace-muted">This session can review documents but cannot upload new files.</span> : null}
      </div>
    </form>
  )
}

function EmptyState({ title, copy }: { title: string; copy: string }) {
  return (
    <div className="empty-panel">
      <h3 className="empty-panel__title">{title}</h3>
      <p className="empty-panel__copy">{copy}</p>
    </div>
  )
}

function Field({
  label,
  children,
}: {
  label: string
  children: React.ReactNode
}) {
  return (
    <label className="workspace-field">
      <span>{label}</span>
      {children}
    </label>
  )
}

function SelectField({
  label,
  value,
  onChange,
  options,
  disabled,
}: {
  label: string
  value: string
  onChange: (value: string) => void
  options: Array<[string, string]>
  disabled?: boolean
}) {
  return <AppSelectField label={label} value={value} onChange={onChange} options={options} disabled={disabled} />
}

function CheckboxField({
  label,
  checked,
  onChange,
  disabled,
}: {
  label: string
  checked: boolean
  onChange: (checked: boolean) => void
  disabled?: boolean
}) {
  return (
    <label className="employee-checkbox-field">
      <input
        type="checkbox"
        checked={checked}
        disabled={disabled}
        onChange={(event) => onChange(event.target.checked)}
      />
      <span>{label}</span>
    </label>
  )
}

function FormNotice({ error, message }: { error: string | null; message: string | null }) {
  if (!error && !message) {
    return null
  }

  return (
    <div className="workspace-stack workspace-stack--tight">
      {error ? <p className="workspace-error">{error}</p> : null}
      {message ? <p className="workspace-success">{message}</p> : null}
    </div>
  )
}

function contactTypeLabel(value: EmployeeContactRecord['type']) {
  switch (value) {
    case 'whatsapp':
      return 'WhatsApp'
    default:
      return value.charAt(0).toUpperCase() + value.slice(1)
  }
}

function addressTypeLabel(value: EmployeeAddressRecord['type']) {
  return value.charAt(0).toUpperCase() + value.slice(1)
}

function auditEventLabel(value: string) {
  return value
    .split('.')
    .map((part) => part.replace(/_/g, ' '))
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' · ')
}

function formatMetadata(metadata: Record<string, unknown>) {
  const values = Object.entries(metadata)
    .slice(0, 3)
    .map(([key, value]) => `${key.replace(/_/g, ' ')}: ${String(value)}`)

  return values.length ? values.join(' · ') : 'No additional metadata'
}

function formatBytes(value: number) {
  if (value < 1024) {
    return `${value} B`
  }

  if (value < 1024 * 1024) {
    return `${(value / 1024).toFixed(1)} KB`
  }

  return `${(value / (1024 * 1024)).toFixed(1)} MB`
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

function todayDate() {
  return new Date().toISOString().slice(0, 10)
}

function statusLabel(value: EmployeeStatus) {
  switch (value) {
    case 'notice_period':
      return 'Notice period'
    default:
      return value.charAt(0).toUpperCase() + value.slice(1)
  }
}

function statusVariant(value: EmployeeStatus): 'success' | 'warning' | 'subtle' | 'danger' {
  switch (value) {
    case 'active':
      return 'success'
    case 'probation':
    case 'notice_period':
      return 'warning'
    case 'inactive':
      return 'subtle'
    case 'terminated':
      return 'danger'
    default:
      return 'subtle'
  }
}
