import type { PropsWithChildren, ReactNode } from 'react'

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

  const allowed =
    match === 'any'
      ? permissions.some((permission) => grantedPermissions.includes(permission))
      : permissions.every((permission) => grantedPermissions.includes(permission))

  return allowed ? <>{children}</> : <>{fallback}</>
}
