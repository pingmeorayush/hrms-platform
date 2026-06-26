import { useMemo, useState } from 'react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { CardDescription, CardTitle } from '../../../shared/ui/card'
import { Input } from '../../../shared/ui/input'
import { SelectField } from '../../../shared/ui/select-field'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { Textarea } from '../../../shared/ui/textarea'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspaceSplit,
  WorkspaceSummaryRow,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'
import type { CreateLearningItemInput, LearningDeliveryMode, LearningItemStatus } from '../types'
import type { LearningItemRecord } from '../types'
import { formatLearningDate, learningDeliveryModeLabel } from '../utils'
import { useLearningRouteWorkspace } from './useLearningRouteWorkspace'

type CatalogTab = 'active' | 'draft' | 'archived'

interface CatalogFormState {
  code: string
  title: string
  category: string
  delivery_mode: LearningDeliveryMode
  duration_minutes: string
  requires_completion_evidence: 'yes' | 'no'
  renewal_frequency_months: string
  default_due_days: string
  status: LearningItemStatus
  provider: string
  description: string
}

const catalogTabs: Array<{ id: CatalogTab; label: string }> = [
  { id: 'active', label: 'Active catalog' },
  { id: 'draft', label: 'Drafts' },
  { id: 'archived', label: 'Archived' },
]

const deliveryModeOptions: Array<[LearningDeliveryMode, string]> = [
  ['self_paced', 'Self paced'],
  ['instructor_led', 'Instructor led'],
  ['virtual_session', 'Virtual session'],
  ['blended', 'Blended'],
  ['document_acknowledgement', 'Document acknowledgement'],
]

function defaultCatalogForm(): CatalogFormState {
  return {
    code: '',
    title: '',
    category: 'Compliance',
    delivery_mode: 'self_paced',
    duration_minutes: '',
    requires_completion_evidence: 'no',
    renewal_frequency_months: '',
    default_due_days: '',
    status: 'active',
    provider: '',
    description: '',
  }
}

function mapItemToFormState(item: LearningItemRecord): CatalogFormState {
  return {
    code: item.code,
    title: item.title,
    category: item.category,
    delivery_mode: item.delivery_mode,
    duration_minutes: item.duration_minutes ? String(item.duration_minutes) : '',
    requires_completion_evidence: item.requires_completion_evidence ? 'yes' : 'no',
    renewal_frequency_months: item.renewal_frequency_months ? String(item.renewal_frequency_months) : '',
    default_due_days: item.default_due_days ? String(item.default_due_days) : '',
    status: item.status,
    provider: typeof item.metadata?.provider === 'string' ? item.metadata.provider : '',
    description: item.description ?? '',
  }
}

export function LearningCatalogPage() {
  const workspace = useLearningRouteWorkspace()
  const data = workspace.data
  const [activeTab, setActiveTab] = useState<CatalogTab>('active')
  const [searchTerm, setSearchTerm] = useState('')
  const [selectedItemId, setSelectedItemId] = useState<number | null>(null)
  const [form, setForm] = useState<CatalogFormState>(defaultCatalogForm())

  const filteredItems = useMemo(() => {
    if (!data) {
      return []
    }

    const base = data.items.filter((item) => item.status === activeTab)
    const query = searchTerm.trim().toLowerCase()

    if (!query) {
      return base
    }

    return base.filter((item) =>
      [item.code, item.title, item.category, item.description ?? ''].join(' ').toLowerCase().includes(query),
    )
  }, [activeTab, data, searchTerm])

  const selectedItem =
    filteredItems.find((item) => item.id === selectedItemId) ??
    data?.items.find((item) => item.id === selectedItemId) ??
    null
  const detailItem = selectedItem ?? filteredItems[0] ?? data?.items[0] ?? null

  const handleSaveItem = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    if (!form.code.trim() || !form.title.trim() || !form.category.trim()) {
      return
    }

    const payload: CreateLearningItemInput = {
      code: form.code.trim(),
      title: form.title.trim(),
      description: form.description.trim() || null,
      category: form.category.trim(),
      delivery_mode: form.delivery_mode,
      duration_minutes: form.duration_minutes ? Number(form.duration_minutes) : null,
      requires_completion_evidence: form.requires_completion_evidence === 'yes',
      renewal_frequency_months: form.renewal_frequency_months ? Number(form.renewal_frequency_months) : null,
      default_due_days: form.default_due_days ? Number(form.default_due_days) : null,
      metadata: form.provider.trim() ? { provider: form.provider.trim() } : null,
      status: form.status,
    }

    await workspace.saveLearningItem(selectedItem?.id ?? null, payload)

    if (!selectedItem) {
      setForm(defaultCatalogForm())
    }
  }

  if (workspace.isLoading) {
    return <WorkspaceEmptyState title="Loading learning catalog" copy="Resolving learning items, delivery posture, and catalog permissions." />
  }

  if (workspace.error) {
    return <WorkspaceEmptyState title="Learning catalog unavailable" copy={workspace.error.message || 'The learning catalog could not be loaded.'} />
  }

  if (!data || !workspace.canViewLearning) {
    return <WorkspaceEmptyState title="Learning catalog unavailable" copy="This session does not currently resolve to learning visibility." />
  }

  if (!workspace.canManageCatalog && !workspace.canAssignLearning) {
    return (
      <WorkspaceEmptyState
        title="Learning catalog unavailable"
        copy="This route is limited to learning administrators and assignment operators."
      />
    )
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader>
          <div className="space-y-1.5">
            <Badge variant="info">Catalog administration</Badge>
            <CardTitle>Learning catalog studio</CardTitle>
            <CardDescription>
              Manage learning items, delivery format, evidence requirements, and renewal posture before assignments roll into compliance queues.
            </CardDescription>
          </div>
          <WorkspaceHeaderActions>
            <Badge variant={workspace.source === 'demo' ? 'info' : 'neutral'}>
              {workspace.source === 'demo' ? 'Demo catalog surface' : 'Live catalog surface'}
            </Badge>
            {workspace.pendingActionLabel ? <Badge variant="info">{workspace.pendingActionLabel}</Badge> : null}
            {workspace.lastActionMessage ? <Badge variant="success">{workspace.lastActionMessage}</Badge> : null}
            {workspace.actionError ? <Badge variant="danger">{workspace.actionError}</Badge> : null}
          </WorkspaceHeaderActions>
        </WorkspaceHeader>
        <WorkspaceContent className="space-y-4">
          <WorkspaceTabs aria-label="Learning catalog tabs">
            {catalogTabs.map((tab) => (
              <WorkspaceTabButton key={tab.id} isActive={activeTab === tab.id} onClick={() => setActiveTab(tab.id)}>
                {tab.label}
              </WorkspaceTabButton>
            ))}
          </WorkspaceTabs>

          <WorkspaceField>
            <span>Search catalog</span>
            <Input
              value={searchTerm}
              onChange={(event) => setSearchTerm(event.target.value)}
              placeholder="Search by code, title, or category"
            />
          </WorkspaceField>

          <WorkspaceSplit
            primary={(
              <WorkspaceTableShell>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Item</TableHead>
                      <TableHead>Delivery</TableHead>
                      <TableHead>Evidence</TableHead>
                      <TableHead>Renewal</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {filteredItems.map((item) => (
                      <TableRow
                        key={item.id}
                        className={item.id === selectedItem?.id ? 'bg-primary/[0.06]' : 'cursor-pointer'}
                        onClick={() => {
                          setSelectedItemId(item.id)
                          setForm(mapItemToFormState(item))
                        }}
                      >
                        <TableCell>
                          <div className="space-y-1">
                            <p className="font-medium text-foreground">{item.title}</p>
                            <p className="text-xs text-muted-foreground">{item.code}</p>
                          </div>
                        </TableCell>
                        <TableCell>{learningDeliveryModeLabel(item.delivery_mode)}</TableCell>
                        <TableCell>
                          <Badge variant={item.requires_completion_evidence ? 'warning' : 'neutral'}>
                            {item.requires_completion_evidence ? 'Required' : 'Optional'}
                          </Badge>
                        </TableCell>
                        <TableCell>{item.renewal_frequency_months ? `${item.renewal_frequency_months} mo` : 'Not set'}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </WorkspaceTableShell>
            )}
            secondary={(
              <div className="space-y-3.5">
                <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                  <div className="space-y-1">
                    <h2 className="text-base font-semibold text-foreground">Selected item detail</h2>
                    <p className="text-sm text-muted-foreground">
                      Review the item posture before updating the catalog or issuing a new assignment.
                    </p>
                  </div>
                  {detailItem ? (
                    <div className="mt-4 space-y-1.5">
                      <WorkspaceSummaryRow label="Code" value={detailItem.code} />
                      <WorkspaceSummaryRow label="Category" value={detailItem.category} />
                      <WorkspaceSummaryRow label="Delivery" value={learningDeliveryModeLabel(detailItem.delivery_mode)} />
                      <WorkspaceSummaryRow label="Duration" value={detailItem.duration_minutes ? `${detailItem.duration_minutes} min` : 'Not set'} />
                      <WorkspaceSummaryRow label="Evidence" value={detailItem.requires_completion_evidence ? 'Required' : 'Optional'} />
                      <WorkspaceSummaryRow label="Renewal" value={detailItem.renewal_frequency_months ? `${detailItem.renewal_frequency_months} months` : 'Not configured'} />
                      <WorkspaceSummaryRow label="Updated" value={formatLearningDate(detailItem.updated_at?.slice(0, 10) ?? null)} />
                    </div>
                  ) : (
                    <p className="mt-4 text-sm text-muted-foreground">Select a learning item to review its configuration posture.</p>
                  )}
                </div>
              </div>
            )}
          />

          <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
            <div className="space-y-1">
              <h2 className="text-base font-semibold text-foreground">{selectedItem ? 'Update learning item' : 'Create learning item'}</h2>
              <p className="text-sm text-muted-foreground">
                Keep the compliance posture explicit: delivery mode, evidence requirement, and renewal cadence should be set deliberately.
              </p>
            </div>
            <form className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3" onSubmit={handleSaveItem}>
              <WorkspaceField label="Item code">
                <Input value={form.code} onChange={(event) => setForm((current) => ({ ...current, code: event.target.value }))} />
              </WorkspaceField>
              <WorkspaceField label="Title">
                <Input value={form.title} onChange={(event) => setForm((current) => ({ ...current, title: event.target.value }))} />
              </WorkspaceField>
              <WorkspaceField label="Category">
                <Input value={form.category} onChange={(event) => setForm((current) => ({ ...current, category: event.target.value }))} />
              </WorkspaceField>
              <SelectField
                label="Delivery mode"
                value={form.delivery_mode}
                options={deliveryModeOptions}
                onChange={(value) => setForm((current) => ({ ...current, delivery_mode: value as LearningDeliveryMode }))}
              />
              <WorkspaceField label="Duration (minutes)">
                <Input
                  value={form.duration_minutes}
                  onChange={(event) => setForm((current) => ({ ...current, duration_minutes: event.target.value }))}
                />
              </WorkspaceField>
              <SelectField
                label="Evidence required"
                value={form.requires_completion_evidence}
                options={[
                  ['no', 'Optional'],
                  ['yes', 'Required'],
                ]}
                onChange={(value) => setForm((current) => ({ ...current, requires_completion_evidence: value as 'yes' | 'no' }))}
              />
              <WorkspaceField label="Renewal frequency (months)">
                <Input
                  value={form.renewal_frequency_months}
                  onChange={(event) => setForm((current) => ({ ...current, renewal_frequency_months: event.target.value }))}
                />
              </WorkspaceField>
              <WorkspaceField label="Default due days">
                <Input
                  value={form.default_due_days}
                  onChange={(event) => setForm((current) => ({ ...current, default_due_days: event.target.value }))}
                />
              </WorkspaceField>
              <SelectField
                label="Status"
                value={form.status}
                options={[
                  ['draft', 'Draft'],
                  ['active', 'Active'],
                  ['archived', 'Archived'],
                ]}
                onChange={(value) => setForm((current) => ({ ...current, status: value as LearningItemStatus }))}
              />
              <WorkspaceField label="Provider" className="md:col-span-2 xl:col-span-1">
                <Input value={form.provider} onChange={(event) => setForm((current) => ({ ...current, provider: event.target.value }))} />
              </WorkspaceField>
              <WorkspaceField label="Description" className="md:col-span-2 xl:col-span-2">
                <Textarea
                  value={form.description}
                  onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))}
                  rows={4}
                />
              </WorkspaceField>
              <div className="md:col-span-2 xl:col-span-3 flex flex-wrap items-center gap-2">
                <Button type="submit">{selectedItem ? 'Update learning item' : 'Create learning item'}</Button>
                {selectedItem ? (
                  <Button
                    type="button"
                    variant="secondary"
                    onClick={() => {
                      setSelectedItemId(null)
                      setForm(defaultCatalogForm())
                    }}
                  >
                    Start new item
                  </Button>
                ) : null}
              </div>
            </form>
          </div>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}
