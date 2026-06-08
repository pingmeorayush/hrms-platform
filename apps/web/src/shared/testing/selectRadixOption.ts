import { screen, waitForElementToBeRemoved } from '@testing-library/react'

type TestUser = {
  click: (element: Element) => Promise<void>
}

type RoleScope = {
  findByRole: typeof screen.findByRole
  getByRole: typeof screen.getByRole
}

export async function selectRadixOption(
  user: TestUser,
  scope: RoleScope,
  label: string | RegExp,
  option: string | RegExp,
) {
  await user.click(await scope.findByRole('combobox', { name: label }))
  const listbox = await screen.findByRole('listbox')
  await user.click(screen.getByRole('option', { name: option }))
  await waitForElementToBeRemoved(listbox).catch(() => undefined)
}
