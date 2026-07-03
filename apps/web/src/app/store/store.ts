import { configureStore } from '@reduxjs/toolkit'
import { ACCESS_STORAGE_KEY, accessReducer } from './accessSlice'

export const appStore = configureStore({
  reducer: {
    access: accessReducer,
  },
})

if (typeof window !== 'undefined') {
  appStore.subscribe(() => {
    try {
      window.localStorage.setItem(ACCESS_STORAGE_KEY, JSON.stringify(appStore.getState().access))
    } catch {
      return
    }
  })
}

export type RootState = ReturnType<typeof appStore.getState>
export type AppDispatch = typeof appStore.dispatch
