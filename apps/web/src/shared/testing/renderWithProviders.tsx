import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { combineReducers, configureStore } from '@reduxjs/toolkit'
import { render } from '@testing-library/react'
import type { PropsWithChildren, ReactElement } from 'react'
import { Provider } from 'react-redux'
import { MemoryRouter } from 'react-router-dom'
import { accessReducer, type AccessState } from '../../app/store/accessSlice'
import { RegionalizationProvider } from '../regionalization/provider'
import { ConfirmProvider } from '../ui/confirm'
import { ToastProvider } from '../ui/toast'

interface RenderOptions {
  accessState?: Partial<AccessState>
  initialEntries?: string[]
}

export function renderWithProviders(
  ui: ReactElement,
  { accessState, initialEntries = ['/foundation'] }: RenderOptions = {},
) {
  const rootReducer = combineReducers({
    access: accessReducer,
  })

  const preloadedState: ReturnType<typeof rootReducer> = {
    access: {
      mode: 'demo',
      demoPersona: 'platformAdmin',
      apiBaseUrl: 'http://127.0.0.1:8000/api/v1',
      token: '',
      ...accessState,
    },
  }

  const store = configureStore({
    reducer: rootReducer,
    preloadedState,
  })

  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        retry: false,
      },
    },
  })

  function Wrapper({ children }: PropsWithChildren) {
    return (
      <Provider store={store}>
        <QueryClientProvider client={queryClient}>
          <RegionalizationProvider>
            <ToastProvider>
              <ConfirmProvider>
                <MemoryRouter initialEntries={initialEntries}>{children}</MemoryRouter>
              </ConfirmProvider>
            </ToastProvider>
          </RegionalizationProvider>
        </QueryClientProvider>
      </Provider>
    )
  }

  return {
    store,
    queryClient,
    ...render(ui, { wrapper: Wrapper }),
  }
}
