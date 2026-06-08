import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import type { PropsWithChildren } from 'react'
import { Provider } from 'react-redux'
import { appStore } from '../store/store'
import { ConfirmProvider } from '../../shared/ui/confirm'
import { ToastProvider } from '../../shared/ui/toast'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 60_000,
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
})

export function AppProviders({ children }: PropsWithChildren) {
  return (
    <Provider store={appStore}>
      <QueryClientProvider client={queryClient}>
        <ToastProvider>
          <ConfirmProvider>{children}</ConfirmProvider>
        </ToastProvider>
      </QueryClientProvider>
    </Provider>
  )
}
