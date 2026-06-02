import { createSlice, type PayloadAction } from '@reduxjs/toolkit'
import type { DemoPersona } from '../../modules/access/types'

export type AccessMode = 'demo' | 'live'

export interface AccessState {
  mode: AccessMode
  demoPersona: DemoPersona
  apiBaseUrl: string
  token: string
}

const initialState: AccessState = {
  mode: 'demo',
  demoPersona: 'platformAdmin',
  apiBaseUrl: import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000/api/v1',
  token: '',
}

const accessSlice = createSlice({
  name: 'access',
  initialState,
  reducers: {
    setMode(state, action: PayloadAction<AccessMode>) {
      state.mode = action.payload
    },
    setDemoPersona(state, action: PayloadAction<DemoPersona>) {
      state.demoPersona = action.payload
    },
    setApiBaseUrl(state, action: PayloadAction<string>) {
      state.apiBaseUrl = action.payload
    },
    setToken(state, action: PayloadAction<string>) {
      state.token = action.payload
    },
  },
})

export const { setApiBaseUrl, setDemoPersona, setMode, setToken } = accessSlice.actions
export const accessReducer = accessSlice.reducer
