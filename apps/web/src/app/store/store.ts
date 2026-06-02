import { configureStore } from '@reduxjs/toolkit'
import { accessReducer } from './accessSlice'

export const appStore = configureStore({
  reducer: {
    access: accessReducer,
  },
})

export type RootState = ReturnType<typeof appStore.getState>
export type AppDispatch = typeof appStore.dispatch
