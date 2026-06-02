import { render, screen } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import { Can } from './Can'

describe('Can', () => {
  it('renders children when all required permissions are granted', () => {
    render(
      <Can grantedPermissions={['workflow.view', 'workflow.execute']} permissions={['workflow.view']}>
        <button type="button">Launch workflow</button>
      </Can>,
    )

    expect(screen.getByRole('button', { name: 'Launch workflow' })).toBeInTheDocument()
  })

  it('hides children when the session lacks the required permission', () => {
    render(
      <Can grantedPermissions={['notification.view']} permissions={['auth.manage_roles']}>
        <button type="button">Create role</button>
      </Can>,
    )

    expect(screen.queryByRole('button', { name: 'Create role' })).not.toBeInTheDocument()
  })

  it('supports any-match visibility for compound admin contracts', () => {
    render(
      <Can
        grantedPermissions={['auth.manage_permissions']}
        permissions={['auth.manage_roles', 'auth.manage_permissions']}
        match="any"
      >
        <span>Access control visible</span>
      </Can>,
    )

    expect(screen.getByText('Access control visible')).toBeInTheDocument()
  })
})
