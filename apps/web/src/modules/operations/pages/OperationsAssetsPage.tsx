import { useState, type FormEvent } from 'react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Input } from '../../../shared/ui/input'
import { Modal } from '../../../shared/ui/modal'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { Textarea } from '../../../shared/ui/textarea'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeader,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspaceSurface,
  WorkspaceTableShell,
  WorkspaceToolbar,
  WorkspaceToolbarRow,
} from '../../../shared/ui/workspace'
import { ApiRequestError } from '../../../shared/api/http'
import type {
  AssetAssignmentFormValues,
  AssetCategoryFormValues,
  AssetFormValues,
  AssetIssueFormValues,
  AssetReturnFormValues,
  OperationsAssetRecord,
} from '../types'
import { useOperationsRouteWorkspace } from './useOperationsRouteWorkspace'

const emptyAssetCategoryForm: AssetCategoryFormValues = {
  code: '',
  name: '',
  status: 'active',
  notes: '',
}

const emptyAssetForm: AssetFormValues = {
  asset_category_id: '',
  asset_tag: '',
  name: '',
  asset_type: 'physical',
  serial_number: '',
  manufacturer: '',
  model_name: '',
  purchase_date: '',
  status: 'available',
  notes: '',
}

const emptyAssignmentForm: AssetAssignmentFormValues = {
  employee_id: '',
  assigned_at: '',
  expected_return_date: '',
  handover_condition: '',
  assignment_notes: '',
}

const emptyIssueForm: AssetIssueFormValues = {
  issued_at: '',
  issue_notes: '',
}

const emptyReturnForm: AssetReturnFormValues = {
  returned_at: '',
  return_condition: '',
  return_notes: '',
}

type AssetActionMode = 'assign' | 'issue' | 'return'

export function OperationsAssetsPage() {
  const workspace = useOperationsRouteWorkspace()
  const [search, setSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState('all')
  const [dueFilter, setDueFilter] = useState('all')
  const [actionMessage, setActionMessage] = useState<string | null>(null)
  const [actionError, setActionError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [isSaving, setIsSaving] = useState(false)
  const [isCategoryModalOpen, setIsCategoryModalOpen] = useState(false)
  const [isAssetModalOpen, setIsAssetModalOpen] = useState(false)
  const [assetActionMode, setAssetActionMode] = useState<AssetActionMode | null>(null)
  const [selectedAsset, setSelectedAsset] = useState<OperationsAssetRecord | null>(null)
  const [assetCategoryForm, setAssetCategoryForm] = useState<AssetCategoryFormValues>(emptyAssetCategoryForm)
  const [assetForm, setAssetForm] = useState<AssetFormValues>(emptyAssetForm)
  const [assignmentForm, setAssignmentForm] = useState<AssetAssignmentFormValues>(emptyAssignmentForm)
  const [issueForm, setIssueForm] = useState<AssetIssueFormValues>(emptyIssueForm)
  const [returnForm, setReturnForm] = useState<AssetReturnFormValues>(emptyReturnForm)

  if (workspace.isLoading) {
    return <p className="workspace-muted">Loading asset operations...</p>
  }

  if (workspace.error) {
    return <p className="workspace-error">{workspace.error.message}</p>
  }

  if (!workspace.data) {
    return <p className="workspace-muted">No asset operations workspace is available yet.</p>
  }

  const normalizedQuery = search.trim().toLowerCase()
  const filteredAssets = workspace.data.assets.filter((asset) => {
    if (statusFilter !== 'all' && asset.status !== statusFilter) {
      return false
    }

    const dueState = deriveAssetDueState(asset)
    if (dueFilter !== 'all' && dueState !== dueFilter) {
      return false
    }

    if (!normalizedQuery) {
      return true
    }

    return [asset.asset_tag, asset.name, asset.current_assignment?.employee?.full_name ?? '', asset.notes ?? '']
      .join(' ')
      .toLowerCase()
      .includes(normalizedQuery)
  })
  const overdueAssets = workspace.data.assets.filter((asset) => deriveAssetDueState(asset) === 'overdue')
  const blockedAssets = workspace.data.assets.filter((asset) => deriveAssetDueState(asset) === 'blocked')

  async function handleCreateAssetCategory(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setIsSaving(true)
    setActionError(null)
    setFieldErrors({})

    try {
      await workspace.saveAssetCategory(assetCategoryForm)
      setActionMessage('Asset category created.')
      setIsCategoryModalOpen(false)
      setAssetCategoryForm(emptyAssetCategoryForm)
    } catch (error) {
      handleApiError(error, setActionError, setFieldErrors, 'The asset category could not be saved.')
    } finally {
      setIsSaving(false)
    }
  }

  async function handleCreateAsset(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setIsSaving(true)
    setActionError(null)
    setFieldErrors({})

    try {
      await workspace.saveAsset(assetForm)
      setActionMessage('Asset record created.')
      setIsAssetModalOpen(false)
      setAssetForm(emptyAssetForm)
    } catch (error) {
      handleApiError(error, setActionError, setFieldErrors, 'The asset record could not be created.')
    } finally {
      setIsSaving(false)
    }
  }

  async function handleAssetAction(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    if (!selectedAsset || !assetActionMode) {
      return
    }

    setIsSaving(true)
    setActionError(null)
    setFieldErrors({})

    try {
      if (assetActionMode === 'assign') {
        await workspace.assignAssetToEmployee(selectedAsset.id, assignmentForm)
        setActionMessage(`${selectedAsset.asset_tag} assigned successfully.`)
      }

      if (assetActionMode === 'issue') {
        await workspace.issueAssignedAsset(selectedAsset.id, issueForm)
        setActionMessage(`${selectedAsset.asset_tag} issued successfully.`)
      }

      if (assetActionMode === 'return') {
        await workspace.returnAssignedAsset(selectedAsset.id, returnForm)
        setActionMessage(`${selectedAsset.asset_tag} returned successfully.`)
      }

      setAssetActionMode(null)
      setSelectedAsset(null)
      setAssignmentForm(emptyAssignmentForm)
      setIssueForm(emptyIssueForm)
      setReturnForm(emptyReturnForm)
    } catch (error) {
      handleApiError(error, setActionError, setFieldErrors, 'The asset lifecycle action could not be completed.')
    } finally {
      setIsSaving(false)
    }
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Asset Operations"
          title="Asset Operations"
          description="Keep asset custody visible across onboarding, offboarding, and IT handoff workflows."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo contract' : 'Live contract'}</Badge>}
          context={[
            `${filteredAssets.length} asset(s) in scope`,
            `${overdueAssets.length} overdue return(s)`,
            workspace.canManageAssets ? 'Asset controls live' : 'Read only session',
          ]}
          actions={
            workspace.canManageAssets ? (
              <>
                <Button size="xs" variant="secondary" onClick={() => setIsCategoryModalOpen(true)}>
                  Add asset category
                </Button>
                <Button size="xs" variant="primary" onClick={() => setIsAssetModalOpen(true)}>
                  Register asset
                </Button>
              </>
            ) : (
              <Badge variant="warning">Read only in this session</Badge>
            )
          }
        />

        <WorkspaceContent className="space-y-4">
          <WorkspaceToolbar>
            <WorkspaceToolbarRow>
              <div className="flex flex-1 flex-wrap items-end gap-2.5">
                <WorkspaceField label="Search">
                  <Input
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    placeholder="Search asset tags, names, or current holders"
                  />
                </WorkspaceField>
                <WorkspaceField label="Asset status" compact>
                  <select
                    aria-label="Asset status"
                    className={nativeSelectClassName}
                    value={statusFilter}
                    onChange={(event) => setStatusFilter(event.target.value)}
                  >
                    <option value="all">All statuses</option>
                    <option value="available">Available</option>
                    <option value="assigned">Assigned</option>
                    <option value="issued">Issued</option>
                    <option value="returned">Returned</option>
                    <option value="maintenance">Maintenance</option>
                  </select>
                </WorkspaceField>
                <WorkspaceField label="Due state" compact>
                  <select
                    aria-label="Due state"
                    className={nativeSelectClassName}
                    value={dueFilter}
                    onChange={(event) => setDueFilter(event.target.value)}
                  >
                    <option value="all">All due states</option>
                    <option value="healthy">Healthy</option>
                    <option value="due_soon">Due soon</option>
                    <option value="overdue">Overdue</option>
                    <option value="blocked">Blocked</option>
                    <option value="closed">Closed</option>
                  </select>
                </WorkspaceField>
              </div>
            </WorkspaceToolbarRow>
          </WorkspaceToolbar>

          {actionMessage ? <p className="text-sm font-medium text-emerald-700">{actionMessage}</p> : null}
          {actionError ? <p className="workspace-error">{actionError}</p> : null}
          {!workspace.canManageAssets ? (
            <p className="workspace-muted">This session can inspect asset custody but cannot change categories or lifecycle states without `asset.manage`.</p>
          ) : null}

          <div className="organization-metric-grid">
            <MetricCard label="Inventory" value={String(workspace.data.assets.length)} caption={`${workspace.data.assetCategories.length} categories currently tracked`} />
            <MetricCard label="Issued assets" value={String(workspace.data.assets.filter((asset) => asset.status === 'issued').length)} caption={`${overdueAssets.length} asset(s) are overdue for return`} />
            <MetricCard label="Blocked" value={String(blockedAssets.length)} caption="Maintenance and unissued assignments that still need operator action" />
            <MetricCard label="Available" value={String(workspace.data.assets.filter((asset) => asset.status === 'available').length)} caption="Ready to assign for new joiners or replacements" />
          </div>

          <div className="grid gap-4 xl:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]">
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Asset inventory</h2>
                  <p className="text-sm text-muted-foreground">Assignment, issuance, return, overdue, and blocked states now stay in one operator queue.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                {filteredAssets.length ? (
                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Asset</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead>Holder</TableHead>
                          <TableHead>Due state</TableHead>
                          <TableHead>Action</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {filteredAssets.map((asset) => (
                          <TableRow key={asset.id}>
                            <TableCell>
                              <span className="ui-table-primary">{asset.asset_tag}</span>
                              <p className="text-xs text-muted-foreground">{asset.name}</p>
                            </TableCell>
                            <TableCell>
                              <Badge variant={statusVariant(asset.status)}>{asset.status.replace(/_/g, ' ')}</Badge>
                            </TableCell>
                            <TableCell>
                              {asset.current_assignment?.employee?.full_name ?? 'Unassigned'}
                              <p className="text-xs text-muted-foreground">{asset.asset_category?.name ?? 'Uncategorized'}</p>
                            </TableCell>
                            <TableCell>
                              <Badge variant={dueVariant(deriveAssetDueState(asset))}>{formatDueState(deriveAssetDueState(asset))}</Badge>
                              <p className="mt-1 text-xs text-muted-foreground">
                                {asset.current_assignment?.expected_return_date
                                  ? `Target ${formatDate(asset.current_assignment.expected_return_date)}`
                                  : asset.status === 'maintenance'
                                    ? 'Blocked in maintenance'
                                    : 'No return target'}
                              </p>
                            </TableCell>
                            <TableCell>
                              {workspace.canManageAssets ? (
                                <div className="flex flex-wrap gap-1.5">
                                  {asset.status === 'available' ? (
                                    <Button
                                      size="xs"
                                      variant="secondary"
                                      onClick={() => {
                                        setSelectedAsset(asset)
                                        setAssetActionMode('assign')
                                        setAssignmentForm(emptyAssignmentForm)
                                      }}
                                    >
                                      Assign
                                    </Button>
                                  ) : null}
                                  {asset.status === 'assigned' ? (
                                    <Button
                                      size="xs"
                                      variant="secondary"
                                      onClick={() => {
                                        setSelectedAsset(asset)
                                        setAssetActionMode('issue')
                                        setIssueForm(emptyIssueForm)
                                      }}
                                    >
                                      Issue
                                    </Button>
                                  ) : null}
                                  {(asset.status === 'assigned' || asset.status === 'issued') ? (
                                    <Button
                                      size="xs"
                                      variant="secondary"
                                      onClick={() => {
                                        setSelectedAsset(asset)
                                        setAssetActionMode('return')
                                        setReturnForm(emptyReturnForm)
                                      }}
                                    >
                                      Return
                                    </Button>
                                  ) : null}
                                </div>
                              ) : (
                                <span className="text-xs text-muted-foreground">Read only</span>
                              )}
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </WorkspaceTableShell>
                ) : (
                  <WorkspaceEmptyState
                    title="No assets match this queue"
                    copy="Adjust the status, due-state, or search filters to widen the inventory view."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>

            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Asset categories</h2>
                  <p className="text-sm text-muted-foreground">Category posture stays visible here so IT and HR know which asset families are active for assignment.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                <WorkspaceTableShell>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Category</TableHead>
                        <TableHead>Status</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {workspace.data.assetCategories.map((category) => (
                        <TableRow key={category.id}>
                          <TableCell>
                            <span className="ui-table-primary">{category.name}</span>
                            <p className="text-xs text-muted-foreground">{category.code}</p>
                          </TableCell>
                          <TableCell>
                            <Badge variant={category.status === 'active' ? 'success' : 'warning'}>{category.status}</Badge>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </WorkspaceTableShell>
              </WorkspaceContent>
            </WorkspaceSurface>
          </div>
        </WorkspaceContent>
      </WorkspaceSurface>

      <Modal
        open={isCategoryModalOpen}
        title="Create asset category"
        description="Keep assignment-ready asset families explicit so operators can filter the inventory cleanly."
        onClose={() => setIsCategoryModalOpen(false)}
      >
        <form className="space-y-3" onSubmit={handleCreateAssetCategory}>
          <WorkspaceField label="Code" error={fieldErrors.code?.[0]}>
            <Input value={assetCategoryForm.code} onChange={(event) => setAssetCategoryForm((current) => ({ ...current, code: event.target.value }))} />
          </WorkspaceField>
          <WorkspaceField label="Name" error={fieldErrors.name?.[0]}>
            <Input value={assetCategoryForm.name} onChange={(event) => setAssetCategoryForm((current) => ({ ...current, name: event.target.value }))} />
          </WorkspaceField>
          <WorkspaceField label="Status" error={fieldErrors.status?.[0]}>
            <select
              aria-label="Asset category status"
              className={nativeSelectClassName}
              value={assetCategoryForm.status}
              onChange={(event) => setAssetCategoryForm((current) => ({ ...current, status: event.target.value }))}
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </WorkspaceField>
          <WorkspaceField label="Notes" error={fieldErrors.notes?.[0]}>
            <Textarea rows={3} value={assetCategoryForm.notes} onChange={(event) => setAssetCategoryForm((current) => ({ ...current, notes: event.target.value }))} />
          </WorkspaceField>
          <div className="flex justify-end gap-2">
            <Button type="button" variant="secondary" onClick={() => setIsCategoryModalOpen(false)}>
              Cancel
            </Button>
            <Button type="submit" variant="primary" disabled={isSaving}>
              {isSaving ? 'Saving...' : 'Create category'}
            </Button>
          </div>
        </form>
      </Modal>

      <Modal
        open={isAssetModalOpen}
        title="Register asset"
        description="Add a new asset record before it enters assignment or issue workflows."
        onClose={() => setIsAssetModalOpen(false)}
      >
        <form className="space-y-3" onSubmit={handleCreateAsset}>
          <WorkspaceField label="Category" error={fieldErrors.asset_category_id?.[0]}>
            <select
              aria-label="Asset category"
              className={nativeSelectClassName}
              value={assetForm.asset_category_id}
              onChange={(event) => setAssetForm((current) => ({ ...current, asset_category_id: event.target.value }))}
            >
              <option value="">Select category</option>
              {workspace.data.assetCategories.map((category) => (
                <option key={category.id} value={String(category.id)}>
                  {category.name}
                </option>
              ))}
            </select>
          </WorkspaceField>
          <div className="grid gap-3 sm:grid-cols-2">
            <WorkspaceField label="Asset tag" error={fieldErrors.asset_tag?.[0]}>
              <Input value={assetForm.asset_tag} onChange={(event) => setAssetForm((current) => ({ ...current, asset_tag: event.target.value }))} />
            </WorkspaceField>
            <WorkspaceField label="Asset type" error={fieldErrors.asset_type?.[0]}>
              <select
                aria-label="Asset type"
                className={nativeSelectClassName}
                value={assetForm.asset_type}
                onChange={(event) => setAssetForm((current) => ({ ...current, asset_type: event.target.value }))}
              >
                <option value="physical">Physical</option>
                <option value="digital">Digital</option>
                <option value="accessory">Accessory</option>
                <option value="license">License</option>
              </select>
            </WorkspaceField>
          </div>
          <WorkspaceField label="Name" error={fieldErrors.name?.[0]}>
            <Input value={assetForm.name} onChange={(event) => setAssetForm((current) => ({ ...current, name: event.target.value }))} />
          </WorkspaceField>
          <div className="grid gap-3 sm:grid-cols-2">
            <WorkspaceField label="Serial number" error={fieldErrors.serial_number?.[0]}>
              <Input value={assetForm.serial_number} onChange={(event) => setAssetForm((current) => ({ ...current, serial_number: event.target.value }))} />
            </WorkspaceField>
            <WorkspaceField label="Purchase date" error={fieldErrors.purchase_date?.[0]}>
              <Input type="date" value={assetForm.purchase_date} onChange={(event) => setAssetForm((current) => ({ ...current, purchase_date: event.target.value }))} />
            </WorkspaceField>
          </div>
          <div className="grid gap-3 sm:grid-cols-2">
            <WorkspaceField label="Manufacturer">
              <Input value={assetForm.manufacturer} onChange={(event) => setAssetForm((current) => ({ ...current, manufacturer: event.target.value }))} />
            </WorkspaceField>
            <WorkspaceField label="Model name">
              <Input value={assetForm.model_name} onChange={(event) => setAssetForm((current) => ({ ...current, model_name: event.target.value }))} />
            </WorkspaceField>
          </div>
          <WorkspaceField label="Notes" error={fieldErrors.notes?.[0]}>
            <Textarea rows={3} value={assetForm.notes} onChange={(event) => setAssetForm((current) => ({ ...current, notes: event.target.value }))} />
          </WorkspaceField>
          <div className="flex justify-end gap-2">
            <Button type="button" variant="secondary" onClick={() => setIsAssetModalOpen(false)}>
              Cancel
            </Button>
            <Button type="submit" variant="primary" disabled={isSaving}>
              {isSaving ? 'Saving...' : 'Register asset'}
            </Button>
          </div>
        </form>
      </Modal>

      <Modal
        open={assetActionMode !== null}
        title={assetActionMode === 'assign' ? 'Assign asset' : assetActionMode === 'issue' ? 'Issue asset' : 'Return asset'}
        description={selectedAsset ? `${selectedAsset.asset_tag} · ${selectedAsset.name}` : 'Select an asset to continue.'}
        onClose={() => {
          setAssetActionMode(null)
          setSelectedAsset(null)
        }}
      >
        <form className="space-y-3" onSubmit={handleAssetAction}>
          {assetActionMode === 'assign' ? (
            <>
              <WorkspaceField label="Employee" error={fieldErrors.employee_id?.[0]}>
                <select
                  aria-label="Assign to employee"
                  className={nativeSelectClassName}
                  value={assignmentForm.employee_id}
                  onChange={(event) => setAssignmentForm((current) => ({ ...current, employee_id: event.target.value }))}
                >
                  <option value="">Select employee</option>
                  {workspace.data.employees
                    .filter((employee) => employee.employment_status !== 'terminated')
                    .map((employee) => (
                      <option key={employee.id} value={String(employee.id)}>
                        {employee.full_name}
                      </option>
                    ))}
                </select>
              </WorkspaceField>
              <div className="grid gap-3 sm:grid-cols-2">
                <WorkspaceField label="Assigned at" error={fieldErrors.assigned_at?.[0]}>
                  <Input type="datetime-local" value={assignmentForm.assigned_at} onChange={(event) => setAssignmentForm((current) => ({ ...current, assigned_at: event.target.value }))} />
                </WorkspaceField>
                <WorkspaceField label="Expected return" error={fieldErrors.expected_return_date?.[0]}>
                  <Input type="date" value={assignmentForm.expected_return_date} onChange={(event) => setAssignmentForm((current) => ({ ...current, expected_return_date: event.target.value }))} />
                </WorkspaceField>
              </div>
              <WorkspaceField label="Handover condition" error={fieldErrors.handover_condition?.[0]}>
                <Input value={assignmentForm.handover_condition} onChange={(event) => setAssignmentForm((current) => ({ ...current, handover_condition: event.target.value }))} />
              </WorkspaceField>
              <WorkspaceField label="Assignment notes" error={fieldErrors.assignment_notes?.[0]}>
                <Textarea rows={3} value={assignmentForm.assignment_notes} onChange={(event) => setAssignmentForm((current) => ({ ...current, assignment_notes: event.target.value }))} />
              </WorkspaceField>
            </>
          ) : null}

          {assetActionMode === 'issue' ? (
            <>
              <WorkspaceField label="Issued at" error={fieldErrors.issued_at?.[0]}>
                <Input type="datetime-local" value={issueForm.issued_at} onChange={(event) => setIssueForm((current) => ({ ...current, issued_at: event.target.value }))} />
              </WorkspaceField>
              <WorkspaceField label="Issue notes" error={fieldErrors.issue_notes?.[0]}>
                <Textarea rows={3} value={issueForm.issue_notes} onChange={(event) => setIssueForm((current) => ({ ...current, issue_notes: event.target.value }))} />
              </WorkspaceField>
            </>
          ) : null}

          {assetActionMode === 'return' ? (
            <>
              <WorkspaceField label="Returned at" error={fieldErrors.returned_at?.[0]}>
                <Input type="datetime-local" value={returnForm.returned_at} onChange={(event) => setReturnForm((current) => ({ ...current, returned_at: event.target.value }))} />
              </WorkspaceField>
              <WorkspaceField label="Return condition" error={fieldErrors.return_condition?.[0]}>
                <Input value={returnForm.return_condition} onChange={(event) => setReturnForm((current) => ({ ...current, return_condition: event.target.value }))} />
              </WorkspaceField>
              <WorkspaceField label="Return notes" error={fieldErrors.return_notes?.[0]}>
                <Textarea rows={3} value={returnForm.return_notes} onChange={(event) => setReturnForm((current) => ({ ...current, return_notes: event.target.value }))} />
              </WorkspaceField>
            </>
          ) : null}

          {actionError ? <p className="workspace-error">{actionError}</p> : null}
          <div className="flex justify-end gap-2">
            <Button type="button" variant="secondary" onClick={() => setAssetActionMode(null)}>
              Cancel
            </Button>
            <Button type="submit" variant="primary" disabled={isSaving || !selectedAsset}>
              {isSaving ? 'Saving...' : assetActionMode === 'assign' ? 'Assign asset' : assetActionMode === 'issue' ? 'Issue asset' : 'Return asset'}
            </Button>
          </div>
        </form>
      </Modal>
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
    return 'No target'
  }

  return new Intl.DateTimeFormat('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
}

function deriveAssetDueState(asset: OperationsAssetRecord) {
  if (asset.status === 'maintenance') {
    return 'blocked'
  }

  if (asset.status === 'returned') {
    return 'closed'
  }

  if (!asset.current_assignment?.expected_return_date) {
    return asset.status === 'assigned' ? 'blocked' : 'healthy'
  }

  const today = new Date().toISOString().slice(0, 10)
  const target = asset.current_assignment.expected_return_date

  if (target < today) {
    return 'overdue'
  }

  const diffDays =
    (new Date(target).getTime() - new Date(today).getTime()) / (1000 * 60 * 60 * 24)

  if (diffDays <= 5) {
    return 'due_soon'
  }

  return 'healthy'
}

function formatDueState(state: string) {
  return state.replace(/_/g, ' ')
}

function statusVariant(status: string) {
  if (status === 'maintenance') {
    return 'warning'
  }

  if (status === 'issued') {
    return 'success'
  }

  if (status === 'assigned') {
    return 'warning'
  }

  return 'neutral'
}

function dueVariant(state: string) {
  if (state === 'overdue') {
    return 'danger'
  }

  if (state === 'blocked' || state === 'due_soon') {
    return 'warning'
  }

  if (state === 'closed') {
    return 'success'
  }

  return 'neutral'
}

function handleApiError(
  error: unknown,
  setActionError: (value: string | null) => void,
  setFieldErrors: (value: Record<string, string[]>) => void,
  fallbackMessage: string,
) {
  if (error instanceof ApiRequestError) {
    setActionError(error.message)
    setFieldErrors(error.fieldErrors)
    return
  }

  if (error instanceof Error) {
    setActionError(error.message)
    return
  }

  setActionError(fallbackMessage)
}

const nativeSelectClassName =
  'flex h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/40'
