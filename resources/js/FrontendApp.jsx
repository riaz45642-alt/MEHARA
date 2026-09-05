import { useState } from 'react'
import Navbar from './components/Navbar'
import Footer from './components/Footer'
import Login from './components/Login'
import Signup from './components/Signup'
import ScrollProgress from './components/ScrollProgress'
import AutoTranslate from './components/AutoTranslate'
import { RouterProvider, useRouter } from './router/Router'
import { AppProvider, useApp } from './context/AppContext'
import Pricing from './pages/Pricing'
import About from './pages/About'
import Help from './pages/Help'
import Privacy from './pages/Privacy'
import Terms from './pages/Terms'
import { NotFound } from './pages/NotFound'
import ForgotPassword from './pages/ForgotPassword'
import ResetPassword from './pages/ResetPassword'
import VerifyEmail from './pages/VerifyEmail'
import ConnectedLms from './pages/ConnectedLms'
import GlobalSearch from './pages/GlobalSearch'
import ProjectAssignmentsPage from './pages/platform/ProjectAssignmentsPage'
import HomePage from './pages/platform/HomePage'
import CourseMarketplacePage from './pages/platform/CourseMarketplacePage'
import CourseDetailsPage from './pages/platform/CourseDetailsPage'
import ProjectsPage from './pages/platform/ProjectsPage'
import OpportunitiesPage from './pages/platform/OpportunitiesPage'
import MentorshipPage from './pages/platform/MentorshipPage'
import ServicesPage from './pages/platform/ServicesPage'
import LiveClassesPage from './pages/platform/LiveClassesPage'
import StudentDashboardPage from './pages/platform/StudentDashboardPage'
import InstructorDashboardPage from './pages/platform/InstructorDashboardPage'
import AdminMinistryDashboardPage from './pages/platform/AdminMinistryDashboardPage'
import LearningPlayerPage from './pages/platform/LearningPlayerPage'
import CertificatesPage from './pages/platform/CertificatesPage'
import ProfessionalProfilePage from './pages/platform/ProfessionalProfilePage'
import GovernmentTrainingPage from './pages/platform/GovernmentTrainingPage'
import KidsLearningPage from './pages/platform/KidsLearningPage'
import InstructorWorkspacePage from './pages/platform/InstructorWorkspacePage'
import CourseEditorPage from './pages/platform/CourseEditorPage'
import CurriculumPage from './pages/platform/CurriculumPage'
import LearningProgressPage from './pages/platform/LearningProgressPage'
import CourseRegistrationPage from './pages/platform/CourseRegistrationPage'
import RegistrationReviewPage from './pages/platform/RegistrationReviewPage'
import CommunityPage from './pages/platform/CommunityPage'
import NewsPage from './pages/platform/NewsPage'
import AdminContentPage from './pages/platform/AdminContentPage'
import './App.css'
import './misr.css'
import './mehara.css'
import './mehara-pages.css'
import './mehara-details.css'
import './mehara-dashboard.css'
import './mehara-kids.css'
import './mehara-2027.css'
import './mehara-2027-polish.css'

function AppShell() {
  const [authView, setAuthView] = useState(null)
  const { path, navigate } = useRouter()
  const { user, authLoading } = useApp()
  const goHome = () => { setAuthView(null); navigate('/') }
  const openForgotPassword = () => { setAuthView(null); navigate('/forgot-password') }
  if (authLoading) return <main className="portal"><p>Restoring your session...</p></main>
  if (authView === 'login' && !user) return <Login onBack={goHome} onSwitch={() => setAuthView('signup')} onForgotPassword={openForgotPassword} />
  if (authView === 'signup' && !user) return <Signup onBack={goHome} onSwitch={() => setAuthView('login')} />
  const courseMatch = path.match(/^\/courses\/([^/]+)$/), learningMatch = path.match(/^\/learning\/([^/]+)$/), activityMatch = path.match(/^\/activities\/([^/]+)$/), workbookMatch = path.match(/^\/workbooks\/([^/]+)$/), bundleMatch = path.match(/^\/worksheet-bundles\/([^/]+)$/)
  const protectedPage = (content, roles = []) => {
    if (!user) return <Login onBack={goHome} onSwitch={() => navigate('/signup')} onForgotPassword={openForgotPassword} />
    if (!user.email_verified_at) return <VerifyEmail />
    if (roles.length && !roles.includes(user.role)) return <NotFound />
    return content
  }
  let Page
  if (path === '/') Page = <HomePage />
  else if (path === '/index' || path === '/home') Page = <HomePage />
  else if (path === '/courses') Page = <CourseMarketplacePage />
  else if (path === '/curriculum') Page = <CurriculumPage />
  else if (courseMatch) Page = <CourseDetailsPage id={decodeURIComponent(courseMatch[1])} />
  else if (learningMatch) Page = protectedPage(<LearningPlayerPage courseId={decodeURIComponent(learningMatch[1])} />)
  else if (path === '/projects') Page = <ProjectsPage />
  else if (path === '/news') Page = <NewsPage />
  else if (path === '/opportunities') Page = <OpportunitiesPage />
  else if (path === '/mentorship') Page = <MentorshipPage />
  else if (path === '/live-classes') Page = protectedPage(<LiveClassesPage />)
  else if (path === '/services') Page = <ServicesPage />
  else if (path === '/government-training') Page = <GovernmentTrainingPage />
  else if (path === '/kids-learning') Page = <KidsLearningPage />
  else if (path === '/kids') Page = <KidsLearningPage />
  else if (path === '/admin') Page = protectedPage(<AdminMinistryDashboardPage />, ['admin'])
  else if (path === '/instructor') Page = protectedPage(<InstructorDashboardPage />, ['teacher','admin'])
  else if (path === '/dashboard') Page = protectedPage(<StudentDashboardPage />)
  else if (path === '/progress') Page = protectedPage(<LearningProgressPage />)
  else if (path === '/registrations') Page = protectedPage(<CourseRegistrationPage />)
  else if (path === '/admin/registrations') Page = protectedPage(<RegistrationReviewPage />, ['admin'])
  else if (path === '/admin/content') Page = protectedPage(<AdminContentPage />, ['admin'])
  else if (path === '/instructor/courses') Page = protectedPage(<InstructorWorkspacePage area="courses" />, ['teacher','admin'])
  else if (path === '/instructor/courses/create' || /^\/instructor\/courses\/[^/]+\/edit$/.test(path)) Page = protectedPage(<CourseEditorPage />, ['teacher','admin'])
  else if (path === '/instructor/students') Page = protectedPage(<InstructorWorkspacePage area="students" />, ['teacher','admin'])
  else if (path === '/instructor/projects') Page = protectedPage(<InstructorWorkspacePage area="projects" />, ['teacher','admin'])
  else if (path === '/instructor/analytics') Page = protectedPage(<InstructorWorkspacePage area="analytics" />, ['teacher','admin'])
  else if (path === '/profile') Page = protectedPage(<ProfessionalProfilePage />)
  else if (path === '/community') Page = protectedPage(<CommunityPage />)
  else if (path === '/instructor-chat') Page = protectedPage(<CommunityPage trainerOnly />)
  else if (path === '/search') Page = protectedPage(<GlobalSearch />)
  else if (path === '/messages') Page = protectedPage(<ConnectedLms type="messages" />)
  else if (path === '/notifications') Page = protectedPage(<ConnectedLms type="notifications" />)
  else if (path === '/calendar') Page = protectedPage(<ConnectedLms type="calendar" />)
  else if (path === '/certificates') Page = protectedPage(<CertificatesPage />)
  else if (path === '/certificate-view') Page = protectedPage(<CertificatesPage />)
  else if (path === '/assignments') Page = protectedPage(<ProjectAssignmentsPage />)
  else if (activityMatch || path === '/activities') Page = protectedPage(<LearningProgressPage />)
  else if (workbookMatch || bundleMatch || path === '/workbooks' || path === '/worksheet-bundles') Page = protectedPage(<ProjectAssignmentsPage />)
  else if (['/bookmarks','/wishlist','/favorites'].includes(path)) Page = protectedPage(<ConnectedLms type="saved" savedKind={path === '/favorites' ? 'favorite' : path === '/wishlist' ? 'wishlist' : 'bookmark'} />)
  else if (['/users','/classes','/subjects','/billing'].includes(path)) Page = protectedPage(<AdminMinistryDashboardPage />, ['admin'])
  else if (path === '/pricing') Page = <Pricing />
  else if (path === '/help') Page = <Help />
  else if (path === '/about') Page = <About />
  else if (path === '/privacy') Page = <Privacy />
  else if (path === '/terms') Page = <Terms />
  else if (path === '/forgot-password') Page = <ForgotPassword />
  else if (path === '/reset-password') Page = <ResetPassword />
  else if (path === '/verify-email') Page = <VerifyEmail />
  else if (path === '/login' && !user) Page = <Login onBack={goHome} onSwitch={() => navigate('/signup')} onForgotPassword={openForgotPassword} />
  else if (path === '/auth' && !user) Page = <Login onBack={goHome} onSwitch={() => navigate('/signup')} onForgotPassword={openForgotPassword} />
  else if (path === '/signup' && !user) Page = <Signup onBack={goHome} onSwitch={() => navigate('/login')} />
  else Page = <NotFound />
  if (['/login','/signup','/forgot-password','/reset-password','/verify-email'].includes(path)) return Page
  return <><ScrollProgress/><Navbar onNavigate={setAuthView} onSectionSelect={() => setAuthView(null)} onLogoClick={goHome}/>{Page}<Footer/></>
}

export default function App(){return <RouterProvider><AppProvider><AutoTranslate/><AppShell/></AppProvider></RouterProvider>}
