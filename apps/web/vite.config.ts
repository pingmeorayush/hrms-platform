import { defineConfig } from 'vitest/config'
import tailwindcss from '@tailwindcss/vite'
import react from '@vitejs/plugin-react'
import { fileURLToPath, URL } from 'node:url'
import { loadEnv } from 'vite'

function resolveManualChunk(id: string) {
  if (id.includes('node_modules')) {
    if (
      id.includes('/react/') ||
      id.includes('/react-dom/') ||
      id.includes('/react-router') ||
      id.includes('/@reduxjs/') ||
      id.includes('/react-redux/') ||
      id.includes('/@tanstack/react-query/')
    ) {
      return 'vendor-framework'
    }

    if (id.includes('/@radix-ui/')) {
      return 'vendor-radix'
    }

    if (id.includes('/lucide-react/')) {
      return 'vendor-icons'
    }

    return 'vendor-misc'
  }

  const routeChunkMatchers: Array<[string, string]> = [
    ['/src/modules/payroll/', 'route-payroll'],
    ['/src/modules/operations/', 'route-operations'],
    ['/src/modules/employees/', 'route-employees'],
    ['/src/modules/attendance/', 'route-attendance'],
    ['/src/modules/leave/', 'route-leave'],
    ['/src/modules/recruitment/', 'route-recruitment'],
    ['/src/modules/performance/', 'route-performance'],
    ['/src/modules/learning/', 'route-learning'],
    ['/src/modules/organization/', 'route-organization'],
    ['/src/modules/self-service/', 'route-self-service'],
    ['/src/modules/access/', 'route-access'],
  ]

  const matchedRouteChunk = routeChunkMatchers.find(([segment]) => id.includes(segment))
  if (matchedRouteChunk) {
    return matchedRouteChunk[1]
  }

  return undefined
}

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const host = env.VITE_HOST || '127.0.0.1'
  const port = Number(env.VITE_PORT || 5173)
  const proxyTarget = env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8000'

  return {
    plugins: [react(), tailwindcss()],
    build: {
      rollupOptions: {
        output: {
          manualChunks(id) {
            return resolveManualChunk(id)
          },
        },
      },
    },
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
      },
    },
    server: {
      host,
      port,
      proxy: {
        '/api': proxyTarget,
      },
    },
    test: {
      environment: 'jsdom',
      setupFiles: './src/setupTests.ts',
      css: true,
    },
  }
})
