import { createSlice, type PayloadAction } from '@reduxjs/toolkit'
import type { DemoPersona } from '../../modules/access/types'
import { isDemoAccessEnabled } from '../../modules/access/runtime'

export type AccessMode = 'demo' | 'live'

export const ACCESS_STORAGE_KEY = 'phoenixhrms.access'

export interface AccessState {
  mode: AccessMode
  demoPersona: DemoPersona
  apiBaseUrl: string
  token: string
}

function createDefaultAccessState(): AccessState {
  return {
    mode: isDemoAccessEnabled ? 'demo' : 'live',
    demoPersona: 'platformAdmin',
    apiBaseUrl: import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000/api/v1',
    token: '',
  }
}

function loadAccessState(): AccessState {
  const defaults = createDefaultAccessState()

  if (typeof window === 'undefined') {
    return defaults
  }

  try {
    const raw = window.localStorage.getItem(ACCESS_STORAGE_KEY)

    if (!raw) {
      return defaults
    }

    const parsed = JSON.parse(raw) as Partial<AccessState>
    const persistedMode = parsed.mode === 'demo' || parsed.mode === 'live' ? parsed.mode : defaults.mode

    return {
      mode: isDemoAccessEnabled ? persistedMode : 'live',
      demoPersona: parsed.demoPersona ?? defaults.demoPersona,
      apiBaseUrl: parsed.apiBaseUrl?.trim() || defaults.apiBaseUrl,
      token: parsed.token ?? defaults.token,
    }
  } catch {
    return defaults
  }
}

const initialState: AccessState = loadAccessState()

const accessSlice = createSlice({
  name: 'access',
  initialState,
  reducers: {
    setMode(state, action: PayloadAction<AccessMode>) {
      if (action.payload === 'demo' && !isDemoAccessEnabled) {
        state.mode = 'live'
        return
      }

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
    setLiveSession(
      state,
      action: PayloadAction<{
        token: string
      }>,
    ) {
      state.mode = 'live'
      state.token = action.payload.token
    },
    clearLiveSession(state) {
      state.mode = 'live'
      state.token = ''
    },
  },
})

export const { clearLiveSession, setApiBaseUrl, setDemoPersona, setLiveSession, setMode, setToken } =
  accessSlice.actions
export const accessReducer = accessSlice.reducer
