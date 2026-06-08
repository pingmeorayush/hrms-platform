import { Link } from 'react-router-dom'
import { useState } from 'react'
import { Badge } from '../../shared/ui/badge'
import { CardDescription, CardTitle } from '../../shared/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '../../shared/ui/table'
import {
  WorkspaceContent,
  WorkspaceHeader,
  WorkspacePage,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
  WorkspaceToolbar,
  WorkspaceToolbarRow,
  WorkspaceToolbarStatus,
  WorkspaceToolbarSummary,
} from '../../shared/ui/workspace'

interface FeaturePlaceholderPageProps {
  eyebrow: string
  title: string
  description: string
  plannedStories: string[]
  nextStep: string
}

export function FeaturePlaceholderPage({
  eyebrow,
  title,
  description,
  plannedStories,
  nextStep,
}: FeaturePlaceholderPageProps) {
  const [activeTab, setActiveTab] = useState<'stories' | 'path'>('stories')

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader>
          <div>
            <CardTitle>{title}</CardTitle>
            <CardDescription>{description}</CardDescription>
          </div>
          <div className="pill-row">
            <Badge variant="warning">Planned module</Badge>
            <Badge variant="info">{plannedStories.length} queued stories</Badge>
            <Badge variant="neutral">Foundation-first rollout</Badge>
          </div>
        </WorkspaceHeader>
        <WorkspaceContent>
          <WorkspaceToolbar>
            <WorkspaceToolbarRow>
              <WorkspaceTabs role="tablist" aria-label="Queued module views">
                <WorkspaceTabButton
                  type="button"
                  role="tab"
                  active={activeTab === 'stories'}
                  aria-selected={activeTab === 'stories'}
                  onClick={() => setActiveTab('stories')}
                >
                  Planned stories
                </WorkspaceTabButton>
                <WorkspaceTabButton
                  type="button"
                  role="tab"
                  active={activeTab === 'path'}
                  aria-selected={activeTab === 'path'}
                  onClick={() => setActiveTab('path')}
                >
                  Release path
                </WorkspaceTabButton>
              </WorkspaceTabs>
              <WorkspaceToolbarStatus>
                <Badge variant="neutral">{activeTab === 'stories' ? 'Queued story set' : 'Delivery sequence'}</Badge>
                <Badge variant="info">{plannedStories.length} planned slices</Badge>
              </WorkspaceToolbarStatus>
            </WorkspaceToolbarRow>
            <WorkspaceToolbarSummary>
              <strong>{activeTab === 'stories' ? 'Planned but not started' : 'Sequence visible'}</strong>
              <span className="ui-type-body text-muted-foreground">{activeTab === 'stories' ? eyebrow : nextStep}</span>
            </WorkspaceToolbarSummary>
          </WorkspaceToolbar>

          {activeTab === 'stories' ? (
            <WorkspaceTableShell>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead scope="col">Story</TableHead>
                    <TableHead scope="col">Scope</TableHead>
                    <TableHead scope="col">State</TableHead>
                    <TableHead scope="col">Sequence</TableHead>
                    <TableHead scope="col">Action</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {plannedStories.map((story, index) => (
                    <TableRow key={story}>
                      <TableHead scope="row" className="align-top">
                        <strong className="ui-type-body-strong block text-foreground">{story}</strong>
                        <small className="ui-type-caption mt-1 block text-muted-foreground">{eyebrow}</small>
                      </TableHead>
                      <TableCell className="ui-type-body align-top text-muted-foreground">
                        <p>{description}</p>
                      </TableCell>
                      <TableCell className="align-top">
                        <Badge variant="warning">Queued next</Badge>
                      </TableCell>
                      <TableCell className="ui-type-body align-top text-muted-foreground">
                        Step {index + 1} of {plannedStories.length}
                      </TableCell>
                      <TableCell className="ui-type-body align-top text-muted-foreground">
                        Awaiting active implementation
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </WorkspaceTableShell>
          ) : null}

          {activeTab === 'path' ? (
            <WorkspaceTableShell>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead scope="col">Phase</TableHead>
                    <TableHead scope="col">Summary</TableHead>
                    <TableHead scope="col">State</TableHead>
                    <TableHead scope="col">Dependency</TableHead>
                    <TableHead scope="col">Action</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {[
                    {
                      id: 'foundation',
                      phase: 'Foundation workspace',
                      summary: 'Use the console shell, session switching, and route exposure as the baseline.',
                      state: 'Ready now',
                      dependency: 'Completed first',
                      action: 'Go back to Foundation',
                    },
                    {
                      id: 'organization',
                      phase: 'Organization pattern',
                      summary: 'Organization establishes the first dense collection and editor workflow.',
                      state: 'Live now',
                      dependency: 'Shared CRUD pattern',
                      action: 'Reuse the organization shape',
                    },
                    {
                      id: 'module',
                      phase: title,
                      summary: nextStep,
                      state: 'Queued next',
                      dependency: 'Follows the live modules',
                      action: 'Return when sequence reaches this module',
                    },
                  ].map((row) => (
                    <TableRow key={row.id}>
                      <TableHead scope="row" className="align-top">
                        <strong className="ui-type-body-strong block text-foreground">{row.phase}</strong>
                      </TableHead>
                      <TableCell className="ui-type-body align-top text-muted-foreground">
                        <p>{row.summary}</p>
                      </TableCell>
                      <TableCell className="align-top">
                        <Badge variant={row.state === 'Queued next' ? 'warning' : 'success'}>{row.state}</Badge>
                      </TableCell>
                      <TableCell className="ui-type-body align-top text-muted-foreground">{row.dependency}</TableCell>
                      <TableCell className="ui-type-body align-top text-muted-foreground">
                        {row.id === 'foundation' ? (
                          <Link className="ui-type-body-strong text-primary hover:underline" to="/foundation">
                            {row.action}
                          </Link>
                        ) : (
                          <span>{row.action}</span>
                        )}
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </WorkspaceTableShell>
          ) : null}
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}
