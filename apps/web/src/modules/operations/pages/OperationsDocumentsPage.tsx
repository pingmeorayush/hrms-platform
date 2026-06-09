import { useState, type FormEvent } from 'react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { CardDescription, CardTitle } from '../../../shared/ui/card'
import { Input } from '../../../shared/ui/input'
import { Modal } from '../../../shared/ui/modal'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { Textarea } from '../../../shared/ui/textarea'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspaceSurface,
  WorkspaceTableShell,
  WorkspaceToolbar,
  WorkspaceToolbarRow,
} from '../../../shared/ui/workspace'
import { ApiRequestError } from '../../../shared/api/http'
import type { DocumentCategoryFormValues, OperationsDocumentCategoryRecord } from '../types'
import { useOperationsRouteWorkspace } from './useOperationsRouteWorkspace'

const emptyCategoryForm: DocumentCategoryFormValues = {
  code: '',
  name: '',
  repository_scope: 'policy',
  default_visibility_scope: 'restricted',
  retention_days: '',
  allowed_role_names: '',
  status: 'active',
  notes: '',
}

export function OperationsDocumentsPage() {
  const workspace = useOperationsRouteWorkspace()
  const [search, setSearch] = useState('')
  const [scopeFilter, setScopeFilter] = useState('all')
  const [statusFilter, setStatusFilter] = useState('all')
  const [isCategoryModalOpen, setIsCategoryModalOpen] = useState(false)
  const [editingCategory, setEditingCategory] = useState<OperationsDocumentCategoryRecord | null>(null)
  const [formValues, setFormValues] = useState<DocumentCategoryFormValues>(emptyCategoryForm)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [actionMessage, setActionMessage] = useState<string | null>(null)
  const [actionError, setActionError] = useState<string | null>(null)
  const [isSaving, setIsSaving] = useState(false)

  if (workspace.isLoading) {
    return <p className="workspace-muted">Loading document operations...</p>
  }

  if (workspace.error) {
    return <p className="workspace-error">{workspace.error.message}</p>
  }

  if (!workspace.data) {
    return <p className="workspace-muted">No document operations workspace is available yet.</p>
  }

  const normalizedQuery = search.trim().toLowerCase()
  const filteredCategories = workspace.data.documentCategories.filter((category) => {
    if (scopeFilter !== 'all' && category.repository_scope !== scopeFilter) {
      return false
    }

    if (statusFilter !== 'all' && category.status !== statusFilter) {
      return false
    }

    if (!normalizedQuery) {
      return true
    }

    return [category.code, category.name, category.notes ?? '', category.repository_scope]
      .join(' ')
      .toLowerCase()
      .includes(normalizedQuery)
  })

  const filteredDocuments = workspace.data.documents.filter((document) => {
    if (scopeFilter !== 'all' && document.repository_scope !== scopeFilter) {
      return false
    }

    if (normalizedQuery) {
      return [document.title, document.original_file_name, document.document_category?.name ?? '']
        .join(' ')
        .toLowerCase()
        .includes(normalizedQuery)
    }

    return true
  })

  async function handleSaveCategory(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setIsSaving(true)
    setActionMessage(null)
    setActionError(null)
    setFieldErrors({})

    try {
      await workspace.saveDocumentCategory(formValues, editingCategory?.id ?? null)
      setActionMessage(editingCategory ? 'Document category updated.' : 'Document category created.')
      setIsCategoryModalOpen(false)
      setEditingCategory(null)
      setFormValues(emptyCategoryForm)
    } catch (error) {
      if (error instanceof ApiRequestError) {
        setActionError(error.message)
        setFieldErrors(error.fieldErrors)
      } else if (error instanceof Error) {
        setActionError(error.message)
      } else {
        setActionError('The document category could not be saved.')
      }
    } finally {
      setIsSaving(false)
    }
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader>
          <div>
            <h1 className="text-2xl font-semibold tracking-tight text-foreground">Document operations</h1>
            <CardTitle>Repository governance and retention watch</CardTitle>
            <CardDescription>
              Manage repository categories, role-aware visibility defaults, and the current tenant document posture.
            </CardDescription>
          </div>
          <WorkspaceHeaderActions>
            {workspace.canManageDocuments ? (
              <Button
                size="xs"
                variant="primary"
                onClick={() => {
                  setEditingCategory(null)
                  setFormValues(emptyCategoryForm)
                  setFieldErrors({})
                  setActionError(null)
                  setIsCategoryModalOpen(true)
                }}
              >
                Add category
              </Button>
            ) : (
              <Badge variant="warning">Read only in this session</Badge>
            )}
          </WorkspaceHeaderActions>
        </WorkspaceHeader>

        <WorkspaceContent className="space-y-4">
          <WorkspaceToolbar>
            <WorkspaceToolbarRow>
              <div className="flex flex-1 flex-wrap items-end gap-2.5">
                <WorkspaceField label="Search">
                  <Input
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    placeholder="Search categories, scopes, or filenames"
                  />
                </WorkspaceField>
                <WorkspaceField label="Repository scope" compact>
                  <select
                    aria-label="Repository scope"
                    className={nativeSelectClassName}
                    value={scopeFilter}
                    onChange={(event) => setScopeFilter(event.target.value)}
                  >
                    <option value="all">All scopes</option>
                    <option value="policy">Policy</option>
                    <option value="compliance">Compliance</option>
                    <option value="asset">Asset</option>
                  </select>
                </WorkspaceField>
                <WorkspaceField label="Category status" compact>
                  <select
                    aria-label="Category status"
                    className={nativeSelectClassName}
                    value={statusFilter}
                    onChange={(event) => setStatusFilter(event.target.value)}
                  >
                    <option value="all">All statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </WorkspaceField>
              </div>
            </WorkspaceToolbarRow>
          </WorkspaceToolbar>

          {actionMessage ? <p className="text-sm font-medium text-emerald-700">{actionMessage}</p> : null}
          {actionError ? <p className="workspace-error">{actionError}</p> : null}
          {!workspace.canManageDocuments ? (
            <p className="workspace-muted">Document operations are visible here, but category changes stay restricted without `document.manage`.</p>
          ) : null}

          <div className="grid gap-4 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Document categories</h2>
                  <p className="text-sm text-muted-foreground">Category defaults determine scope, visibility, and retention posture across the repository.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                {filteredCategories.length ? (
                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Category</TableHead>
                          <TableHead>Scope</TableHead>
                          <TableHead>Visibility</TableHead>
                          <TableHead>Retention</TableHead>
                          <TableHead>Roles</TableHead>
                          <TableHead>Action</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {filteredCategories.map((category) => (
                          <TableRow key={category.id}>
                            <TableCell>
                              <span className="ui-table-primary">{category.name}</span>
                              <p className="text-xs text-muted-foreground">{category.code}</p>
                            </TableCell>
                            <TableCell>{category.repository_scope}</TableCell>
                            <TableCell>
                              <Badge variant={category.default_visibility_scope === 'confidential' ? 'danger' : category.default_visibility_scope === 'restricted' ? 'warning' : 'neutral'}>
                                {category.default_visibility_scope}
                              </Badge>
                            </TableCell>
                            <TableCell>{category.retention_days ? `${category.retention_days} days` : 'Not set'}</TableCell>
                            <TableCell>{category.allowed_role_names.length ? category.allowed_role_names.join(', ') : 'Inherited'}</TableCell>
                            <TableCell>
                              {workspace.canManageDocuments ? (
                                <Button
                                  size="xs"
                                  variant="secondary"
                                  onClick={() => {
                                    setEditingCategory(category)
                                    setFormValues({
                                      code: category.code,
                                      name: category.name,
                                      repository_scope: category.repository_scope,
                                      default_visibility_scope: category.default_visibility_scope,
                                      retention_days: category.retention_days ? String(category.retention_days) : '',
                                      allowed_role_names: category.allowed_role_names.join(', '),
                                      status: category.status,
                                      notes: category.notes ?? '',
                                    })
                                    setFieldErrors({})
                                    setActionError(null)
                                    setIsCategoryModalOpen(true)
                                  }}
                                >
                                  Edit
                                </Button>
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
                    title="No document categories match this view"
                    copy="Adjust the search, scope, or status filters to widen the document governance queue."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>

            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Repository files</h2>
                  <p className="text-sm text-muted-foreground">Review how current files inherit visibility and retention from the configured category model.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                {filteredDocuments.length ? (
                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Document</TableHead>
                          <TableHead>Category</TableHead>
                          <TableHead>Retention</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {filteredDocuments.map((document) => (
                          <TableRow key={document.id}>
                            <TableCell>
                              <span className="ui-table-primary">{document.title}</span>
                              <p className="text-xs text-muted-foreground">{document.original_file_name}</p>
                            </TableCell>
                            <TableCell>
                              <Badge variant={document.visibility_scope === 'confidential' ? 'danger' : document.visibility_scope === 'restricted' ? 'warning' : 'neutral'}>
                                {document.document_category?.name ?? 'Uncategorized'}
                              </Badge>
                              <p className="mt-1 text-xs text-muted-foreground">{document.visibility_scope}</p>
                            </TableCell>
                            <TableCell>{document.retention_until ? formatDate(document.retention_until) : 'Not set'}</TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </WorkspaceTableShell>
                ) : (
                  <WorkspaceEmptyState
                    title="No repository files match this view"
                    copy="Widen the scope or search filters to review more document records."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>
          </div>
        </WorkspaceContent>
      </WorkspaceSurface>

      <Modal
        open={isCategoryModalOpen}
        title={editingCategory ? 'Edit document category' : 'Create document category'}
        description="These defaults shape role visibility, repository scope, and retention posture for future document uploads."
        onClose={() => setIsCategoryModalOpen(false)}
      >
        <form className="space-y-3" onSubmit={handleSaveCategory}>
          <WorkspaceField label="Code" error={fieldErrors.code?.[0]}>
            <Input value={formValues.code} onChange={(event) => setFormValues((current) => ({ ...current, code: event.target.value }))} />
          </WorkspaceField>
          <WorkspaceField label="Name" error={fieldErrors.name?.[0]}>
            <Input value={formValues.name} onChange={(event) => setFormValues((current) => ({ ...current, name: event.target.value }))} />
          </WorkspaceField>
          <div className="grid gap-3 sm:grid-cols-2">
            <WorkspaceField label="Repository scope" error={fieldErrors.repository_scope?.[0]}>
              <select
                aria-label="Category repository scope"
                className={nativeSelectClassName}
                value={formValues.repository_scope}
                onChange={(event) => setFormValues((current) => ({ ...current, repository_scope: event.target.value }))}
              >
                <option value="policy">Policy</option>
                <option value="compliance">Compliance</option>
                <option value="asset">Asset</option>
              </select>
            </WorkspaceField>
            <WorkspaceField label="Default visibility" error={fieldErrors.default_visibility_scope?.[0]}>
              <select
                aria-label="Default visibility"
                className={nativeSelectClassName}
                value={formValues.default_visibility_scope}
                onChange={(event) =>
                  setFormValues((current) => ({ ...current, default_visibility_scope: event.target.value }))
                }
              >
                <option value="internal">Internal</option>
                <option value="restricted">Restricted</option>
                <option value="confidential">Confidential</option>
              </select>
            </WorkspaceField>
          </div>
          <div className="grid gap-3 sm:grid-cols-2">
            <WorkspaceField label="Retention days" error={fieldErrors.retention_days?.[0]}>
              <Input
                inputMode="numeric"
                value={formValues.retention_days}
                onChange={(event) => setFormValues((current) => ({ ...current, retention_days: event.target.value }))}
              />
            </WorkspaceField>
            <WorkspaceField label="Status" error={fieldErrors.status?.[0]}>
              <select
                aria-label="Category status"
                className={nativeSelectClassName}
                value={formValues.status}
                onChange={(event) => setFormValues((current) => ({ ...current, status: event.target.value }))}
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </WorkspaceField>
          </div>
          <WorkspaceField
            label="Allowed roles"
            error={fieldErrors.allowed_role_names?.[0]}
          >
            <Input
              value={formValues.allowed_role_names}
              onChange={(event) =>
                setFormValues((current) => ({ ...current, allowed_role_names: event.target.value }))
              }
              placeholder="manager, employee"
            />
          </WorkspaceField>
          <WorkspaceField label="Notes" error={fieldErrors.notes?.[0]}>
            <Textarea
              rows={3}
              value={formValues.notes}
              onChange={(event) => setFormValues((current) => ({ ...current, notes: event.target.value }))}
            />
          </WorkspaceField>
          {actionError ? <p className="workspace-error">{actionError}</p> : null}
          <div className="flex justify-end gap-2">
            <Button type="button" variant="secondary" onClick={() => setIsCategoryModalOpen(false)}>
              Cancel
            </Button>
            <Button type="submit" variant="primary" disabled={isSaving}>
              {isSaving ? 'Saving...' : editingCategory ? 'Save changes' : 'Create category'}
            </Button>
          </div>
        </form>
      </Modal>
    </WorkspacePage>
  )
}

function formatDate(value: string | null) {
  if (!value) {
    return 'Not set'
  }

  return new Intl.DateTimeFormat('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
}

const nativeSelectClassName =
  'flex h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/40'
