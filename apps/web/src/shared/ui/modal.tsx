import type { PropsWithChildren, ReactNode } from 'react'
import { Button } from './button'
import { cn } from './cn'
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from './dialog'

type ModalSize = 'sm' | 'md' | 'lg'

export function Modal({
  open,
  title,
  description,
  size = 'md',
  footer,
  children,
  closeLabel = 'Close',
  closeOnOverlay = true,
  onClose,
}: PropsWithChildren<{
  open: boolean
  title: string
  description?: string
  size?: ModalSize
  footer?: ReactNode
  closeLabel?: string
  closeOnOverlay?: boolean
  onClose: () => void
}>) {
  return (
    <Dialog open={open} onOpenChange={(nextOpen) => (nextOpen ? undefined : onClose())}>
      <DialogContent
        className={cn('ui-modal', `ui-modal--${size}`)}
        size={size}
        showCloseButton={false}
        onInteractOutside={(event) => {
          if (!closeOnOverlay) {
            event.preventDefault()
          }
        }}
      >
        <DialogHeader className="ui-modal__header">
          <div className="ui-modal__heading">
            <DialogTitle className="ui-modal__title">{title}</DialogTitle>
            {description ? <DialogDescription className="ui-modal__description">{description}</DialogDescription> : null}
          </div>
          <DialogClose asChild>
            <Button aria-label={closeLabel} className="ui-modal__close" size="sm" variant="ghost">
              {closeLabel}
            </Button>
          </DialogClose>
        </DialogHeader>
        <div className="ui-modal__body">{children}</div>
        {footer ? <DialogFooter className="ui-modal__footer">{footer}</DialogFooter> : null}
      </DialogContent>
    </Dialog>
  )
}
