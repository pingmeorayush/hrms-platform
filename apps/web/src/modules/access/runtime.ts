export const isDemoAccessEnabled =
  import.meta.env.MODE === 'test' || import.meta.env.VITE_ENABLE_DEMO_ACCESS === 'true'
