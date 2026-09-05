import { useEffect, useRef, useState } from 'react'
import { useApp } from '../context/AppContext'
import { errorMessage } from '../services/api'
import './Auth.css'

export default function Login({ onBack, onSwitch, onForgotPassword }) {
  const { login, demoLogin, googleLogin, api } = useApp()
  const googleButton = useRef(null)
  const [form, setForm] = useState({ email: '', password: '' })
  const [showPass, setShowPass] = useState(false)
  const [remember, setRemember] = useState(false)
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const [demo, setDemo] = useState({ enabled: false, accounts: [] })

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value })
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError(''); setLoading(true)
    try {
      const data = await login(form, remember)
      window.history.replaceState({}, '', data.user.email_verified_at ? (data.user.role === 'admin' ? '/admin' : data.user.role === 'teacher' ? '/instructor' : '/dashboard') : '/verify-email')
      window.dispatchEvent(new PopStateEvent('popstate'))
    } catch (requestError) {
      setError(errorMessage(requestError))
    } finally { setLoading(false) }
  }

  useEffect(() => {
    api.get('/auth/demo-status').then(({ data }) => setDemo(data)).catch(() => {})
  }, [api])

  const useDemo = async (email) => {
    if (!form.password) { setForm(current => ({ ...current, email })); setError('Enter the configured local demo password, then choose the account again.'); return }
    setError(''); setLoading(true)
    try {
      const data = await demoLogin({ email, password: form.password })
      window.history.replaceState({}, '', data.user.role === 'admin' ? '/admin' : data.user.role === 'teacher' ? '/instructor' : '/dashboard')
      window.dispatchEvent(new PopStateEvent('popstate'))
    } catch (requestError) { setError(errorMessage(requestError)) } finally { setLoading(false) }
  }

  useEffect(() => {
    const clientId = import.meta.env.VITE_GOOGLE_CLIENT_ID
    if (!clientId) return
    const render = () => {
      if (!window.google?.accounts?.id || !googleButton.current) return
      window.google.accounts.id.initialize({ client_id: clientId, callback: async ({ credential }) => {
        setError(''); setLoading(true)
        try { const data = await googleLogin(credential); window.history.replaceState({}, '', data.user.role === 'admin' ? '/admin' : data.user.role === 'teacher' ? '/instructor' : '/dashboard'); window.dispatchEvent(new PopStateEvent('popstate')) }
        catch (e) { setError(errorMessage(e)) } finally { setLoading(false) }
      } })
      googleButton.current.innerHTML = ''
      window.google.accounts.id.renderButton(googleButton.current, { theme: 'outline', size: 'large', width: 300, text: 'continue_with' })
    }
    if (window.google?.accounts?.id) { render(); return }
    const script = document.createElement('script'); script.src = 'https://accounts.google.com/gsi/client'; script.async = true; script.defer = true; script.onload = render; document.head.appendChild(script)
    return () => { script.onload = null }
  }, [googleLogin])

  return (
    <section className="auth misr-auth" dir="rtl">

      <div className="auth-card">
        <button className="back-link" onClick={onBack}>العودة للرئيسية ←</button>

        <a className="logo misr-wordmark" href="#" onClick={(e) => { e.preventDefault(); onBack() }}><b>مِهارة</b><span> | MEHARA</span></a>

        <div className="eyebrow">مرحباً بك مجدداً</div>
        <h1>تسجيل الدخول إلى حسابك</h1>
        <p className="sub">تابع دوراتك ومشاريعك وتقدمك من مكان واحد.</p>

        {demo.enabled && <aside className="demo-login-panel"><div><span>DEVELOPMENT / DEMO MODE</span><strong>Use a controlled test identity</strong><small>Only reserved .test accounts are accepted. Enter the local demo password below first.</small></div><div className="demo-account-buttons">{demo.accounts.map(account => <button type="button" disabled={loading} key={account.email} onClick={() => useDemo(account.email)}>{account.label}</button>)}<button type="button" className="demo-custom" disabled={loading||!form.email.endsWith('.test')} onClick={() => useDemo(form.email)}>Use entered .test email</button></div></aside>}

        <form onSubmit={handleSubmit}>
          {error && <div className="auth-error" role="alert">{error}</div>}
          <label className="field">
            <span>البريد الإلكتروني</span>
            <input
              type="email"
              name="email"
              placeholder={demo.enabled ? 'student01@example.test' : 'you@example.com'}
              value={form.email}
              onChange={handleChange}
              required
            />
          </label>

          <label className="field">
            <span>كلمة المرور</span>
            <div className="pass-wrap">
              <input
                type={showPass ? 'text' : 'password'}
                name="password"
                placeholder="أدخل كلمة المرور"
                value={form.password}
                onChange={handleChange}
                required
              />
              <button type="button" className="pass-toggle" onClick={() => setShowPass(!showPass)}>
                {showPass ? 'إخفاء' : 'إظهار'}
              </button>
            </div>
          </label>

          <div className="row-between">
            <label className="checkbox">
              <input type="checkbox" checked={remember} onChange={(e) => setRemember(e.target.checked)} /> تذكرني
            </label>
            <button type="button" className="link link-btn" onClick={onForgotPassword}>نسيت كلمة المرور؟</button>
          </div>

          <button type="submit" className="btn btn-primary auth-submit" disabled={loading}>{loading ? 'جارٍ تسجيل الدخول...' : 'تسجيل الدخول'}</button>
        </form>

        <div className="divider"><span>أو تابع باستخدام</span></div>

        {import.meta.env.VITE_GOOGLE_CLIENT_ID ? <div className="social-row" ref={googleButton} aria-label="Continue with Google" /> : <p className="sub">Google sign-in becomes available when its client ID is configured.</p>}

        <p className="switch-line">
          ليس لديك حساب؟ <button type="button" className="link link-btn" onClick={onSwitch}>إنشاء حساب</button>
        </p>
      </div>
    </section>
  )
}
