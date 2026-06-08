export type PermissionMatch = 'all' | 'any'

export function hasPermissions(
  grantedPermissions: string[],
  requiredPermissions: string[] = [],
  match: PermissionMatch = 'all',
) {
  if (requiredPermissions.length === 0) {
    return true
  }

  return match === 'any'
    ? requiredPermissions.some((permission) => grantedPermissions.includes(permission))
    : requiredPermissions.every((permission) => grantedPermissions.includes(permission))
}
