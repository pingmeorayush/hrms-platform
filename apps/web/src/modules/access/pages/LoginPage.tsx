import { type ReactNode, useMemo, useState } from 'react'
import { Link, Navigate, useNavigate, useSearchParams } from 'react-router-dom'
import {
  ArrowRight,
  BadgeCheck,
  Building2,
  KeyRound,
  Layers3,
  Lock,
  ShieldCheck,
  Sparkles,
  Waypoints,
} from 'lucide-react'
import { ApiRequestError } from '../../../shared/api/http'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../shared/ui/card'
import { Input } from '../../../shared/ui/input'
import { setApiBaseUrl, setLiveSession } from '../../../app/store/accessSlice'
import { useAppDispatch, useAppSelector } from '../../../app/store/hooks'
import { forgotPassword, login, verifyMfa } from '../api/accessApi'

type AuthView = 'login' | 'mfa' | 'forgot'

function defaultNextPath(value: string | null) {
  if (!value) {
    return '/foundation'
  }

  return value.startsWith('/') ? value : '/foundation'
}

export function LoginPage() {
  const dispatch = useAppDispatch()
  const access = useAppSelector((state) => state.access)
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const nextPath = defaultNextPath(searchParams.get('next'))
  const reason = searchParams.get('reason')
  const resetSuccess = searchParams.get('reset') === 'success'
  const [view, setView] = useState<AuthView>('login')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [deviceName, setDeviceName] = useState('web-console')
  const [mfaCode, setMfaCode] = useState('')
  const [mfaMethod, setMfaMethod] = useState<string | null>(null)
  const [forgotEmail, setForgotEmail] = useState('')
  const [statusMessage, setStatusMessage] = useState<string | null>(
    resetSuccess
      ? 'Your password has been reset. Sign in with the new password.'
      : reason === 'signed-out'
        ? 'Your workspace session has been signed out.'
        : reason === 'live-session-required'
          ? 'Sign in to reopen the protected workspace.'
          : null,
  )
  const [errorMessage, setErrorMessage] = useState<string | null>(
    reason === 'session-expired' ? 'Your session expired. Sign in again to continue.' : null,
  )
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [isSubmitting, setIsSubmitting] = useState(false)

  const hasLiveSession = access.token.trim().length > 0
  const liveSessionSummary = useMemo(
    () => `${access.apiBaseUrl.replace(/\/$/, '')}/auth/login`,
    [access.apiBaseUrl],
  )

  if (hasLiveSession) {
    return <Navigate replace to={nextPath} />
  }

  async function handleLoginSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setIsSubmitting(true)
    setErrorMessage(null)
    setStatusMessage(null)
    setFieldErrors({})

    try {
      const response = await login(access.apiBaseUrl, {
        email,
        password,
        device_name: deviceName || 'web-console',
      })

      if ('access_token' in response) {
        dispatch(setLiveSession({ token: response.access_token }))
        navigate(nextPath, { replace: true })
        return
      }

      setMfaMethod(response.mfa_method)
      setView('mfa')
      setStatusMessage('Enter the verification code to finish signing in.')
    } catch (error) {
      handleAuthError(error, setErrorMessage, setFieldErrors)
    } finally {
      setIsSubmitting(false)
    }
  }

  async function handleMfaSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setIsSubmitting(true)
    setErrorMessage(null)
    setStatusMessage(null)
    setFieldErrors({})

    try {
      const response = await verifyMfa(access.apiBaseUrl, {
        email,
        code: mfaCode,
        device_name: deviceName || 'web-console',
      })

      dispatch(setLiveSession({ token: response.access_token }))
      navigate(nextPath, { replace: true })
    } catch (error) {
      handleAuthError(error, setErrorMessage, setFieldErrors)
    } finally {
      setIsSubmitting(false)
    }
  }

  async function handleForgotPasswordSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setIsSubmitting(true)
    setErrorMessage(null)
    setStatusMessage(null)
    setFieldErrors({})

    try {
      await forgotPassword(access.apiBaseUrl, {
        email: forgotEmail,
      })

      setStatusMessage('If the account exists, a reset link has been sent.')
      setView('login')
      setEmail(forgotEmail)
    } catch (error) {
      handleAuthError(error, setErrorMessage, setFieldErrors)
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <div className="relative min-h-svh overflow-hidden bg-[radial-gradient(circle_at_top,rgba(92,167,255,0.18),transparent_28%),radial-gradient(circle_at_bottom_left,rgba(234,138,52,0.14),transparent_24%),linear-gradient(180deg,#f7f9fc_0%,#eef4fb_100%)] px-4 py-8 sm:px-6 lg:px-8">
      <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_16%_18%,rgba(56,189,248,0.16),transparent_20%),radial-gradient(circle_at_82%_16%,rgba(249,115,22,0.14),transparent_18%)]" />
      <div className="pointer-events-none absolute inset-0 opacity-35 [background-image:linear-gradient(rgba(148,163,184,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.08)_1px,transparent_1px)] [background-position:center_center] [background-size:3rem_3rem]" />
      <div className="relative mx-auto grid max-w-6xl gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(25rem,0.9fr)]">
        <section className="relative overflow-hidden rounded-[2rem] border border-line/80 bg-[linear-gradient(135deg,rgba(255,255,255,0.985)_0%,rgba(246,249,255,0.985)_46%,rgba(255,247,236,0.98)_100%)] p-6 text-foreground shadow-[0_30px_80px_rgba(15,23,42,0.09)] lg:p-8">
          <div className="pointer-events-none absolute -right-16 top-8 h-44 w-44 rounded-full bg-[radial-gradient(circle,rgba(96,165,250,0.2)_0%,rgba(96,165,250,0)_72%)] blur-2xl" />
          <div className="pointer-events-none absolute bottom-0 left-0 h-52 w-52 rounded-full bg-[radial-gradient(circle,rgba(249,115,22,0.16)_0%,rgba(249,115,22,0)_74%)] blur-2xl" />

          <div className="relative">
            <div className="flex flex-wrap items-center gap-2">
              <Badge className="border-line/80 bg-white/82 text-[#5a6879] hover:bg-white/82" variant="neutral">
                Enterprise Access
              </Badge>
              <Badge className="border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-50" variant="neutral">
                <BadgeCheck className="mr-1.5 h-3.5 w-3.5" />
                Audited sign-in
              </Badge>
            </div>

            <h1 className="mt-5 max-w-3xl text-3xl font-semibold tracking-tight text-foreground sm:text-4xl">
              Sign in to PhoenixHRMS Enterprise.
            </h1>
            <p className="mt-4 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
              Authenticate with your assigned workspace account, complete step-up verification when required, and open
              a tenant-scoped session with audited permissions, regional defaults, and governed recovery controls.
            </p>

            <div className="mt-8 grid gap-3 sm:grid-cols-3">
              <AuthRailStat
                label="Access posture"
                value="Role aware"
                icon={<ShieldCheck className="h-4 w-4" />}
                accent="from-sky-400/40 to-sky-500/10"
              />
              <AuthRailStat
                label="Verification"
                value="MFA ready"
                icon={<KeyRound className="h-4 w-4" />}
                accent="from-emerald-400/35 to-emerald-500/10"
              />
              <AuthRailStat
                label="Audit trail"
                value="Always on"
                icon={<Layers3 className="h-4 w-4" />}
                accent="from-orange-400/35 to-orange-500/10"
              />
            </div>

            <div className="mt-8 rounded-[1.6rem] border border-line/80 bg-white/72 p-5 shadow-[inset_0_1px_0_rgba(255,255,255,0.82)] backdrop-blur">
              <div className="flex items-start justify-between gap-4">
                <div className="space-y-2">
                  <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#5a6879]">
                    Security Control Plane
                  </p>
                  <h2 className="text-xl font-semibold text-foreground">One entry point for every enterprise persona.</h2>
                  <p className="max-w-xl text-sm leading-7 text-slate-600">
                    Platform operators, tenant administrators, HR teams, managers, and employee self-service users all
                    enter through the same governed surface instead of fragmented or demo-only access paths.
                  </p>
                </div>
                <div className="hidden h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-sky-100 bg-sky-50 text-sky-700 sm:flex">
                  <Sparkles className="h-5 w-5" />
                </div>
              </div>

              <div className="mt-5 grid gap-4 sm:grid-cols-2">
                <FeatureCard
                  title="Tenant-scoped session"
                  copy="Sign-in resolves identity, company context, and navigation reach directly from the platform access contract."
                  icon={<Building2 className="h-4 w-4" />}
                  tone="sky"
                />
                <FeatureCard
                  title="Step-up verification"
                  copy="The sign-in flow can pause for MFA before any protected workspace opens, preserving a clean enterprise perimeter."
                  icon={<Lock className="h-4 w-4" />}
                  tone="emerald"
                />
                <FeatureCard
                  title="Recovery controls"
                  copy="Forgot-password and reset-password are first-class product surfaces, not hidden API-only maintenance paths."
                  icon={<Waypoints className="h-4 w-4" />}
                  tone="orange"
                />
                <FeatureCard
                  title="Audited environment target"
                  copy="Operators can point the client at staging or production identity endpoints without changing the user experience."
                  icon={<ArrowRight className="h-4 w-4" />}
                  tone="slate"
                />
              </div>
            </div>

            <div className="mt-6 flex flex-wrap gap-3">
              <AuthSignal title="Platform Admin" copy="Full authorization governance and cross-tenant operations." />
              <AuthSignal title="HR + Managers" copy="Operational approvals, people workflows, and review visibility." />
              <AuthSignal title="Employee Self-Service" copy="Protected personal flows without fallback shortcuts." />
            </div>
          </div>
        </section>

        <Card className="relative overflow-hidden border-white/40 bg-[linear-gradient(180deg,rgba(255,255,255,0.96)_0%,rgba(243,247,252,0.99)_100%)] shadow-[0_32px_80px_rgba(2,8,23,0.28)]">
          <div className="absolute inset-x-0 top-0 h-1.5 bg-[linear-gradient(90deg,#3b82f6_0%,#06b6d4_35%,#f97316_100%)]" />
          <CardHeader>
            <div className="flex items-center justify-between gap-3">
              <div>
                <p className="workspace-panel__eyebrow">Secure Sign-In</p>
                <CardTitle>
                  {view === 'login' ? 'Sign in to your workspace' : view === 'mfa' ? 'Verify step-up challenge' : 'Recover account access'}
                </CardTitle>
                <CardDescription>
                  {view === 'login'
                    ? 'Use your assigned account to open the protected workspace.'
                    : view === 'mfa'
                      ? 'Finish the verification challenge before the enterprise session is created.'
                      : 'Request a reset link for a managed account email address.'}
                </CardDescription>
              </div>
              <div className="grid h-11 w-11 place-items-center rounded-2xl border border-sky-200 bg-[linear-gradient(180deg,#eff6ff_0%,#dbeafe_100%)] text-sky-700 shadow-[inset_0_1px_0_rgba(255,255,255,0.8)]">
                {view === 'mfa' ? <KeyRound className="h-5 w-5" /> : <ShieldCheck className="h-5 w-5" />}
              </div>
            </div>
          </CardHeader>
          <CardContent className="space-y-5">
            {statusMessage ? <p className="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{statusMessage}</p> : null}
            {errorMessage ? <p className="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{errorMessage}</p> : null}

            {view === 'login' ? (
              <form className="space-y-4" onSubmit={handleLoginSubmit}>
                <div className="rounded-[1.5rem] border border-slate-200/80 bg-white/90 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.82)]">
                  <div className="grid gap-4">
                    <Field label="Email" error={fieldErrors.email?.[0]}>
                      <Input value={email} onChange={(event) => setEmail(event.target.value)} type="email" autoComplete="email" />
                    </Field>
                    <Field label="Password" error={fieldErrors.password?.[0]}>
                      <Input
                        value={password}
                        onChange={(event) => setPassword(event.target.value)}
                        type="password"
                        autoComplete="current-password"
                      />
                    </Field>
                    <Field label="Device name" error={fieldErrors.device_name?.[0]}>
                      <Input value={deviceName} onChange={(event) => setDeviceName(event.target.value)} />
                    </Field>
                    <Field label="API base URL">
                      <Input value={access.apiBaseUrl} onChange={(event) => dispatch(setApiBaseUrl(event.target.value))} />
                    </Field>
                  </div>
                </div>

                <div className="flex flex-wrap gap-3">
                  <Button type="submit" variant="primary" disabled={isSubmitting}>
                    {isSubmitting ? 'Signing in...' : 'Sign in'}
                  </Button>
                  <Button type="button" variant="ghost" onClick={() => setView('forgot')}>
                    Forgot password
                  </Button>
                </div>

                <div className="grid gap-3 sm:grid-cols-3">
                  <FormHint
                    title="MFA aware"
                    copy="Email OTP and authenticator-based verification can interrupt sign-in before the workspace opens."
                  />
                  <FormHint
                    title="Role scoped"
                    copy="Navigation and actions are filtered from the access contract after authentication succeeds."
                  />
                  <FormHint
                    title="Recovery ready"
                    copy="Password recovery returns users to the same controlled entry point rather than a parallel flow."
                  />
                </div>
              </form>
            ) : null}

            {view === 'mfa' ? (
              <form className="space-y-4" onSubmit={handleMfaSubmit}>
                <div className="rounded-[1.5rem] border border-slate-200/80 bg-white/90 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.82)]">
                  <div className="grid gap-4">
                    <Field label="Account email">
                      <Input value={email} readOnly />
                    </Field>
                    <Field label="Verification code" error={fieldErrors.code?.[0]}>
                      <Input
                        value={mfaCode}
                        onChange={(event) => setMfaCode(event.target.value)}
                        inputMode="numeric"
                        maxLength={6}
                        placeholder={mfaMethod === 'email_otp' ? 'Enter the 6-digit email code' : 'Enter the 6-digit authenticator code'}
                      />
                    </Field>
                    <div className="rounded-2xl border border-sky-100 bg-sky-50/80 px-3 py-3 text-xs leading-6 text-sky-900">
                      {mfaMethod === 'email_otp'
                        ? 'We expect a six-digit code from the email challenge.'
                        : 'We expect a six-digit code from the enrolled authenticator app.'}
                    </div>
                  </div>
                </div>

                <div className="flex flex-wrap gap-3">
                  <Button type="submit" variant="primary" disabled={isSubmitting}>
                    {isSubmitting ? 'Verifying...' : 'Verify and continue'}
                  </Button>
                  <Button type="button" variant="ghost" onClick={() => setView('login')}>
                    Back to sign in
                  </Button>
                </div>
              </form>
            ) : null}

            {view === 'forgot' ? (
              <form className="space-y-4" onSubmit={handleForgotPasswordSubmit}>
                <div className="rounded-[1.5rem] border border-slate-200/80 bg-white/90 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.82)]">
                  <Field label="Account email" error={fieldErrors.email?.[0]}>
                    <Input
                      value={forgotEmail}
                      onChange={(event) => setForgotEmail(event.target.value)}
                      type="email"
                      autoComplete="email"
                    />
                  </Field>
                </div>

                <div className="flex flex-wrap gap-3">
                  <Button type="submit" variant="primary" disabled={isSubmitting}>
                    {isSubmitting ? 'Sending...' : 'Send reset link'}
                  </Button>
                  <Button type="button" variant="ghost" onClick={() => setView('login')}>
                    Back to sign in
                  </Button>
                </div>
              </form>
            ) : null}

            <div className="rounded-[1.4rem] border border-slate-200/80 bg-[linear-gradient(135deg,rgba(239,246,255,0.98)_0%,rgba(255,247,237,0.9)_100%)] px-4 py-4">
              <div className="flex items-center justify-between gap-3">
                <div>
                  <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#6a7787]">Environment target</p>
                  <p className="mt-2 break-all text-sm text-slate-700">{liveSessionSummary}</p>
                </div>
                <Badge variant="info">Auth API</Badge>
              </div>
              <p className="mt-3 text-xs leading-6 text-slate-600">
                Recovery flows finish on the dedicated{' '}
                <Link className="font-medium text-primary underline-offset-2 hover:underline" to="/reset-password">
                  reset password page
                </Link>
                .
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}

function FeatureCard({
  title,
  copy,
  icon,
  tone,
}: {
  title: string
  copy: string
  icon: ReactNode
  tone: 'sky' | 'emerald' | 'orange' | 'slate'
}) {
  const toneClassName =
    tone === 'sky'
      ? 'border-sky-200 bg-sky-50 text-sky-700'
      : tone === 'emerald'
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
        : tone === 'orange'
          ? 'border-orange-200 bg-orange-50 text-orange-700'
          : 'border-slate-200 bg-slate-50 text-slate-700'

  return (
    <div className="rounded-2xl border border-line/80 bg-white/82 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.82)]">
      <div className={`inline-flex h-10 w-10 items-center justify-center rounded-2xl border ${toneClassName}`}>
        {icon}
      </div>
      <p className="mt-4 text-sm font-semibold text-foreground">{title}</p>
      <p className="mt-2 text-sm leading-6 text-slate-600">{copy}</p>
    </div>
  )
}

function AuthRailStat({
  label,
  value,
  icon,
  accent,
}: {
  label: string
  value: string
  icon: ReactNode
  accent: string
}) {
  return (
    <div className="rounded-2xl border border-line/80 bg-white/80 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.82)] backdrop-blur">
      <div className={`inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br ${accent} text-slate-900`}>
        {icon}
      </div>
      <p className="mt-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{label}</p>
      <p className="mt-2 text-lg font-semibold text-slate-900">{value}</p>
    </div>
  )
}

function AuthSignal({ title, copy }: { title: string; copy: string }) {
  return (
    <div className="rounded-2xl border border-line/80 bg-white/76 px-4 py-3 text-sm text-slate-700 backdrop-blur">
      <p className="font-semibold text-foreground">{title}</p>
      <p className="mt-1.5 leading-6 text-slate-600">{copy}</p>
    </div>
  )
}

function FormHint({ title, copy }: { title: string; copy: string }) {
  return (
    <div className="rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3">
      <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{title}</p>
      <p className="mt-2 text-xs leading-6 text-slate-600">{copy}</p>
    </div>
  )
}

function Field({
  label,
  error,
  children,
}: {
  label: string
  error?: string
  children: ReactNode
}) {
  return (
    <label className="block space-y-2">
      <span className="text-sm font-medium text-foreground">{label}</span>
      {children}
      {error ? <span className="text-xs text-rose-600">{error}</span> : null}
    </label>
  )
}

function handleAuthError(
  error: unknown,
  setErrorMessage: (message: string | null) => void,
  setFieldErrors: (errors: Record<string, string[]>) => void,
) {
  if (error instanceof ApiRequestError) {
    setErrorMessage(error.message)
    setFieldErrors(error.fieldErrors)
    return
  }

  setErrorMessage('The authentication request failed unexpectedly.')
}
