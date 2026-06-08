import { useContext } from 'react'
import { ConfirmContext } from './confirm-context'

export function useConfirm() {
  return useContext(ConfirmContext)
}
