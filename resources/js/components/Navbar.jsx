import { useEffect, useState } from 'react'
import { useRouter } from '../router/Router'
import { useApp } from '../context/AppContext'
import BrandMark from './BrandMark'
import './Navbar.css'

const LINKS = [
  { to: '/', ar: 'الرئيسية', en:'Home' }, { to: '/courses', ar: 'الدورات', en:'Courses' },
  { to: '/curriculum', ar: 'المسارات التعليمية', en:'Learning paths' }, { to: '/projects', ar: 'المشاريع', en:'Projects' },
  { to: '/news', ar: 'آخر الأخبار', en:'Latest news' }, { to: '/mentorship', ar: 'المدربون', en:'Mentors' }, { to: '/about', ar: 'من نحن', en:'About' },
]
const LanguageSwitch=({language,setLanguage})=><div className="language-switch" role="group" aria-label={language==='ar'?'اختيار اللغة':'Choose language'}><button aria-pressed={language==='ar'} className={language==='ar'?'active':''} onClick={()=>setLanguage('ar')}>{language==='ar'?'العربية':'Arabic'}</button><button aria-pressed={language==='en'} className={language==='en'?'active':''} onClick={()=>setLanguage('en')}>{language==='ar'?'الإنجليزية':'English'}</button></div>

export default function Navbar({ onNavigate, onSectionSelect, onLogoClick }) {
  const [menuOpen, setMenuOpen] = useState(false)
  const [scrolled, setScrolled] = useState(false)
  const [activePath, setActivePath] = useState(
    typeof window !== 'undefined' ? window.location.pathname : '/'
  )
  const { navigate } = useRouter()
  const { user, logout, language, setLanguage } = useApp()
  const t=(ar,en)=>language==='ar'?ar:en

  // Keep activePath in sync with browser back/forward buttons
  useEffect(() => {
    const onPopState = () => setActivePath(window.location.pathname)
    window.addEventListener('popstate', onPopState)
    return () => window.removeEventListener('popstate', onPopState)
  }, [])

  // Compact, more opaque header once the page has scrolled a little
  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 12)
    onScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  const handleAuth = (view) => {
    onNavigate(view)
    setMenuOpen(false)
  }

  // Central navigation helper — updates the route AND marks the clicked link active
  const go = (to) => {
    navigate(to)
    setActivePath(to)
    setMenuOpen(false)
    if (onSectionSelect) onSectionSelect()
  }

  const isActive = (to) => activePath === to
  const handleLogout = async () => { await logout(); go('/') }

  return (
    <header className={scrolled ? 'scrolled' : ''}>
      <div className="wrap nav">
        <div className="nav-signal" aria-label={t('حالة منصة مِهارة','MEHARA platform status')}><i></i><span>{t('نظام التعلم · 2027','LEARNING OS · 2027')}</span></div>
        <a
          className="logo"
          href="#"
          onClick={(e) => { e.preventDefault(); onLogoClick(); setMenuOpen(false) }}
        >
          <BrandMark size={34}/>
          <span className="misr-wordmark"><b>{language==='ar'?'مِهارة':'MEHARA'}</b>{language==='ar'&&<span> | MEHARA</span>}</span>
        </a>

        <nav className={`nav-links${menuOpen ? ' open' : ''}`}>
          {LINKS.map((l) => (
            <a
              key={l.to}
              href={l.to}
              className={isActive(l.to) ? 'active' : ''}
              onClick={(e) => { e.preventDefault(); go(l.to) }}
            >
              {l[language]}
            </a>
          ))}
          {!user && <div className="nav-cta nav-cta-mobile">
            <button className="login" onClick={() => handleAuth('login')}>{t('تسجيل الدخول','Sign in')}</button>
            <button className="btn btn-primary" onClick={() => handleAuth('signup')}>{t('ابدأ التعلم','Start learning')}</button>
          </div>}
          {user && <div className="nav-cta nav-cta-mobile">
            <button className="login" onClick={() => go(user.role === 'admin' ? '/admin' : user.role === 'teacher' ? '/instructor' : '/dashboard')}>{t('لوحة التحكم','Dashboard')}</button>
            <button className="btn btn-primary" onClick={handleLogout}>{t('خروج','Sign out')}</button>
          </div>}
        </nav>

        <LanguageSwitch language={language} setLanguage={setLanguage}/>

        {!user ? <div className="nav-cta nav-cta-desktop">
          <button className="nav-search" aria-label={t('بحث','Search')} onClick={() => go('/search')}>⌕</button>
          <button className="login" onClick={() => handleAuth('login')}>{t('تسجيل الدخول','Sign in')}</button>
          <button className="btn btn-primary" onClick={() => handleAuth('signup')}>{t('ابدأ التعلم','Start learning')}</button>
        </div> : <div className="nav-cta nav-cta-desktop"><button className="nav-search" aria-label={t('بحث','Search')} onClick={() => go('/search')}>⌕</button><button className="login" onClick={() => go(user.role === 'admin' ? '/admin' : user.role === 'teacher' ? '/instructor' : '/dashboard')}>{t('لوحة التحكم','Dashboard')}</button><button className="btn btn-primary" onClick={handleLogout}>{t('خروج','Sign out')}</button></div>}

        <button
          className={`burger${menuOpen ? ' open' : ''}`}
          aria-label={t('القائمة','Menu')}
          aria-expanded={menuOpen}
          onClick={() => setMenuOpen(!menuOpen)}
        >
          <span></span><span></span><span></span>
        </button>
      </div>
    </header>
  )
}
