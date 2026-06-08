import type { PropsWithChildren, ReactNode } from 'react'
import { hasPermissions } from '../../../shared/auth/permissions'

interface CanProps extends PropsWithChildren {
  grantedPermissions: string[]
  permissions?: string[]
  match?: 'all' | 'any'
  fallback?: ReactNode
}

export function Can({
  grantedPermissions,
  permissions = [],
  match = 'all',
  fallback = null,
  children,
}: CanProps) {
  if (permissions.length === 0) {
    return <>{children}</>
  }

  const allowed = hasPermissions(grantedPermissions, permissions, match)

  return allowed ? <>{children}</> : <>{fallback}</>
}
