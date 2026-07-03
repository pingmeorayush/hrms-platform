import { type ReactNode, useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import {
  ArrowLeft,
  BadgeCheck,
  KeyRound,
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
import { setApiBaseUrl } from '../../../app/store/accessSlice'
import { useAppDispatch, useAppSelector } from '../../../app/store/hooks'
import { resetPassword } from '../api/accessApi'

export function ResetPasswordPage() {
  const dispatch = useAppDispatch()
  const access = useAppSelector((state) => state.access)
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const [email, setEmail] = useState(searchParams.get('email') ?? '')
  const [token, setToken] = useState(searchParams.get('token') ?? '')
  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [errorMessage, setErrorMessage] = useState<string | null>(null)
  const [isSubmitting, setIsSubmitting] = useState(false)

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setIsSubmitting(true)
    setErrorMessage(null)
    setFieldErrors({})

    try {
      await resetPassword(access.apiBaseUrl, {
        email,
        token,
        password,
        password_confirmation: passwordConfirmation,
      })

      navigate('/login?reset=success', { replace: true })
    } catch (error) {
      if (error instanceof ApiRequestError) {
        setErrorMessage(error.message)
        setFieldErrors(error.fieldErrors)
      } else {
        setErrorMessage('The password reset request failed unexpectedly.')
      }
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <div className="relative min-h-svh overflow-hidden bg-[radial-gradient(circle_at_top,rgba(92,167,255,0.18),transparent_28%),radial-gradient(circle_at_bottom_left,rgba(234,138,52,0.14),transparent_24%),linear-gradient(180deg,#f7f9fc_0%,#eef4fb_100%)] px-4 py-8">
      <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_16%_18%,rgba(56,189,248,0.16),transparent_20%),radial-gradient(circle_at_82%_16%,rgba(249,115,22,0.14),transparent_18%)]" />
      <div className="pointer-events-none absolute inset-0 opacity-35 [background-image:linear-gradient(rgba(148,163,184,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.08)_1px,transparent_1px)] [background-size:3rem_3rem]" />
      <div className="relative mx-auto grid max-w-6xl gap-6 lg:grid-cols-[minmax(0,1.05fr)_minmax(25rem,0.95fr)]">
        <section className="relative overflow-hidden rounded-[2rem] border border-line/80 bg-[linear-gradient(135deg,rgba(255,255,255,0.985)_0%,rgba(246,249,255,0.985)_46%,rgba(255,247,236,0.98)_100%)] p-6 text-foreground shadow-[0_30px_80px_rgba(15,23,42,0.09)] lg:p-8">
          <div className="pointer-events-none absolute -right-10 top-6 h-40 w-40 rounded-full bg-[radial-gradient(circle,rgba(96,165,250,0.2)_0%,rgba(96,165,250,0)_72%)] blur-2xl" />
          <div className="pointer-events-none absolute bottom-0 left-0 h-48 w-48 rounded-full bg-[radial-gradient(circle,rgba(249,115,22,0.16)_0%,rgba(249,115,22,0)_72%)] blur-2xl" />

          <div className="relative">
            <div className="flex flex-wrap items-center gap-2">
              <Badge className="border-line/80 bg-white/82 text-[#5a6879] hover:bg-white/82" variant="neutral">
                Identity Recovery
              </Badge>
              <Badge className="border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-50" variant="neutral">
                <BadgeCheck className="mr-1.5 h-3.5 w-3.5" />
                Controlled reset
              </Badge>
            </div>

            <h1 className="mt-5 max-w-3xl text-3xl font-semibold tracking-tight text-foreground sm:text-4xl">
              Restore account access without leaving the secure perimeter.
            </h1>
            <p className="mt-4 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
              Reset links, single-use tokens, and password policy checks now flow through the same enterprise access
              surface as sign-in, so recovery stays audited, consistent, and operator-friendly.
            </p>

            <div className="mt-8 grid gap-3 sm:grid-cols-3">
              <RecoveryMetric
                label="Token posture"
                value="Single use"
                icon={<Waypoints className="h-4 w-4" />}
                accent="from-sky-400/40 to-sky-500/10"
              />
              <RecoveryMetric
                label="Password policy"
                value="Validated"
                icon={<Lock className="h-4 w-4" />}
                accent="from-emerald-400/35 to-emerald-500/10"
              />
              <RecoveryMetric
                label="Return path"
                value="Same sign-in"
                icon={<ShieldCheck className="h-4 w-4" />}
                accent="from-orange-400/35 to-orange-500/10"
              />
            </div>

            <div className="mt-8 rounded-[1.6rem] border border-line/80 bg-white/72 p-5 shadow-[inset_0_1px_0_rgba(255,255,255,0.82)] backdrop-blur">
              <div className="flex items-start justify-between gap-4">
                <div className="space-y-2">
                  <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#5a6879]">
                    Recovery Flow
                  </p>
                  <h2 className="text-xl font-semibold text-foreground">Three controlled steps, one trusted outcome.</h2>
                  <p className="max-w-xl text-sm leading-7 text-slate-600">
                    Verify the account, present the reset token, and issue a replacement password. After that, users
                    return to the same enterprise sign-in path instead of a parallel reset-only experience.
                  </p>
                </div>
                <div className="hidden h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-sky-100 bg-sky-50 text-sky-700 sm:flex">
                  <Sparkles className="h-5 w-5" />
                </div>
              </div>

              <div className="mt-5 grid gap-4 sm:grid-cols-2">
                <RecoveryNote
                  title="Single-use recovery token"
                  copy="Recovery tokens are entered directly into the controlled reset workflow instead of being handled off-platform."
                  icon={<KeyRound className="h-4 w-4" />}
                  tone="sky"
                />
                <RecoveryNote
                  title="Password policy enforcement"
                  copy="New credentials are validated before the workspace session can be re-established."
                  icon={<Lock className="h-4 w-4" />}
                  tone="emerald"
                />
                <RecoveryNote
                  title="Environment targeting"
                  copy="Operators can keep reset traffic pointed at the correct staging or production identity endpoint."
                  icon={<Waypoints className="h-4 w-4" />}
                  tone="orange"
                />
                <RecoveryNote
                  title="Return to controlled sign-in"
                  copy="Successful resets redirect back to the same secure sign-in page, not to a side-channel demo flow."
                  icon={<ArrowLeft className="h-4 w-4" />}
                  tone="slate"
                />
              </div>
            </div>
          </div>
        </section>

        <Card className="relative overflow-hidden w-full border-white/40 bg-[linear-gradient(180deg,rgba(255,255,255,0.96)_0%,rgba(243,247,252,0.99)_100%)] shadow-[0_32px_80px_rgba(2,8,23,0.28)]">
          <div className="absolute inset-x-0 top-0 h-1.5 bg-[linear-gradient(90deg,#3b82f6_0%,#06b6d4_38%,#f97316_100%)]" />
          <CardHeader>
            <div className="flex items-center justify-between gap-3">
              <div>
                <p className="workspace-panel__eyebrow">Password Recovery</p>
                <CardTitle>Reset password</CardTitle>
                <CardDescription>Set a fresh password, then return to sign-in for a protected workspace session.</CardDescription>
              </div>
              <div className="grid h-11 w-11 place-items-center rounded-2xl border border-sky-200 bg-[linear-gradient(180deg,#eff6ff_0%,#dbeafe_100%)] text-sky-700 shadow-[inset_0_1px_0_rgba(255,255,255,0.8)]">
                <ShieldCheck className="h-5 w-5" />
              </div>
            </div>
          </CardHeader>
          <CardContent className="space-y-5">
            {errorMessage ? <p className="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{errorMessage}</p> : null}

            <form className="space-y-4" onSubmit={handleSubmit}>
              <div className="rounded-[1.5rem] border border-slate-200/80 bg-white/90 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.82)]">
                <div className="grid gap-4">
                  <Field label="Email" error={fieldErrors.email?.[0]}>
                    <Input value={email} onChange={(event) => setEmail(event.target.value)} type="email" autoComplete="email" />
                  </Field>
                  <Field label="Reset token" error={fieldErrors.token?.[0]}>
                    <Input value={token} onChange={(event) => setToken(event.target.value)} />
                  </Field>
                  <Field label="New password" error={fieldErrors.password?.[0]}>
                    <Input
                      value={password}
                      onChange={(event) => setPassword(event.target.value)}
                      type="password"
                      autoComplete="new-password"
                    />
                  </Field>
                  <Field label="Confirm password">
                    <Input
                      value={passwordConfirmation}
                      onChange={(event) => setPasswordConfirmation(event.target.value)}
                      type="password"
                      autoComplete="new-password"
                    />
                  </Field>
                  <Field label="API base URL">
                    <Input value={access.apiBaseUrl} onChange={(event) => dispatch(setApiBaseUrl(event.target.value))} />
                  </Field>
                </div>
              </div>
              <div className="flex flex-wrap gap-3">
                <Button type="submit" variant="primary" disabled={isSubmitting}>
                  {isSubmitting ? 'Resetting...' : 'Reset password'}
                </Button>
                <Button asChild variant="ghost">
                  <Link to="/login">Back to sign in</Link>
                </Button>
              </div>

              <div className="grid gap-3 sm:grid-cols-3">
                <RecoveryHint
                  title="Verified account"
                  copy="The reset request remains scoped to the supplied account address and token pair."
                />
                <RecoveryHint
                  title="Policy check"
                  copy="Fresh credentials are evaluated before the old session can be replaced."
                />
                <RecoveryHint
                  title="Trusted return"
                  copy="Users land back on the same secure sign-in page after the reset completes."
                />
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}

function RecoveryNote({
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

function RecoveryMetric({
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

function RecoveryHint({ title, copy }: { title: string; copy: string }) {
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
