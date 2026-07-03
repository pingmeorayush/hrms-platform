import type { PropsWithChildren, ReactNode } from 'react'
import { useEffect } from 'react'
import { Navigate, useLocation } from 'react-router-dom'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../shared/ui/card'
import { Button } from '../../shared/ui/button'
import { Badge } from '../../shared/ui/badge'
import { ApiRequestError } from '../../shared/api/http'
import { useAccessSnapshot } from '../../modules/access/hooks/useAccessSnapshot'
import { isDemoAccessEnabled } from '../../modules/access/runtime'
import { clearLiveSession } from '../store/accessSlice'
import { useAppDispatch, useAppSelector } from '../store/hooks'

export function AppSessionGate({ children }: PropsWithChildren) {
  const access = useAppSelector((state) => state.access)
  const dispatch = useAppDispatch()
  const location = useLocation()
  const { snapshot, isLoading, error } = useAccessSnapshot()
  const next = encodeURIComponent(`${location.pathname}${location.search}${location.hash}`)
  const sessionExpired = error instanceof ApiRequestError && error.status === 401

  useEffect(() => {
    if (sessionExpired) {
      dispatch(clearLiveSession())
    }
  }, [dispatch, sessionExpired])

  if (isDemoAccessEnabled && access.mode === 'demo') {
    return <>{children}</>
  }

  if (!access.token.trim()) {
    return <Navigate replace to={`/login?next=${next}`} />
  }

  if (sessionExpired) {
    return <Navigate replace to={`/login?next=${next}&reason=session-expired`} />
  }

  if (isLoading && !snapshot) {
    return (
      <SessionStateCard
        eyebrow="Signing in"
        title="Checking your workspace session"
        description="We are validating the current bearer session and loading your tenant access contract."
      />
    )
  }

  if (error) {
    return (
      <SessionStateCard
        eyebrow="Session error"
        title="We could not open your workspace"
        description={error.message}
        action={<Button asChild variant="primary"><a href={`/login?next=${next}`}>Back to sign in</a></Button>}
      />
    )
  }

  if (!snapshot) {
    return <Navigate replace to={`/login?next=${next}`} />
  }

  return <>{children}</>
}

function SessionStateCard({
  eyebrow,
  title,
  description,
  action,
}: {
  eyebrow: string
  title: string
  description: string
  action?: ReactNode
}) {
  return (
    <div className="flex min-h-svh items-center justify-center bg-[radial-gradient(circle_at_top,rgba(92,167,255,0.12),transparent_28%),var(--page-bg)] p-6">
      <Card className="w-full max-w-xl">
        <CardHeader>
          <div className="flex items-center justify-between gap-3">
            <p className="workspace-panel__eyebrow">{eyebrow}</p>
            <Badge variant="info">{eyebrow}</Badge>
          </div>
          <CardTitle>{title}</CardTitle>
          <CardDescription>{description}</CardDescription>
        </CardHeader>
        {action ? <CardContent>{action}</CardContent> : null}
      </Card>
    </div>
  )
}
