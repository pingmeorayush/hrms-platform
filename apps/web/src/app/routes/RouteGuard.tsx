import type { PropsWithChildren } from 'react'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../shared/ui/card'
import { Badge } from '../../shared/ui/badge'
import { useAccessSnapshot } from '../../modules/access/hooks/useAccessSnapshot'
import { useAppSelector } from '../store/hooks'
import { hasPermissions, type PermissionMatch } from '../../shared/auth/permissions'

interface RouteGuardProps extends PropsWithChildren {
  permissions?: string[]
  match?: PermissionMatch
  title?: string
  description?: string
}

export function RouteGuard({
  permissions = [],
  match = 'all',
  title = 'Access required',
  description = 'Your current workspace session does not have access to this page.',
  children,
}: RouteGuardProps) {
  const access = useAppSelector((state) => state.access)
  const { snapshot, isLoading, error, source } = useAccessSnapshot()

  if (source === 'live' && access.token.trim().length === 0) {
    return (
      <GuardState
        eyebrow="Live API"
        title="Connect a bearer token first"
        description="Open the Foundation workspace, switch to Live API mode, and paste a bearer token before opening protected routes."
      />
    )
  }

  if (isLoading) {
    return (
      <GuardState
        eyebrow="Loading"
        title="Checking workspace access"
        description="We are loading the current session and permission contract."
      />
    )
  }

  if (error) {
    return (
      <GuardState
        eyebrow="Access error"
        title="Unable to load the workspace session"
        description={error.message}
        variant="warning"
      />
    )
  }

  if (!snapshot) {
    return (
      <GuardState
        eyebrow="Session required"
        title="No workspace session is available"
        description="Use Demo mode or configure a live API session from the Foundation workspace."
        variant="warning"
      />
    )
  }

  if (!hasPermissions(snapshot.user.permissions, permissions, match)) {
    return <GuardState eyebrow="Access blocked" title={title} description={description} variant="warning" />
  }

  return <>{children}</>
}

function GuardState({
  eyebrow,
  title,
  description,
  variant = 'info',
}: {
  eyebrow: string
  title: string
  description: string
  variant?: 'info' | 'warning'
}) {
  return (
    <Card className="workspace-state-card">
      <CardHeader>
        <div className="workspace-state-card__header">
          <p className="workspace-panel__eyebrow">{eyebrow}</p>
          <Badge variant={variant === 'warning' ? 'warning' : 'info'}>{eyebrow}</Badge>
        </div>
        <CardTitle>{title}</CardTitle>
        <CardDescription>{description}</CardDescription>
      </CardHeader>
      <CardContent>
        <p className="workspace-state-card__hint">
          The routed shell is active, but this screen waits for the required permission contract or live session context.
        </p>
      </CardContent>
    </Card>
  )
}
