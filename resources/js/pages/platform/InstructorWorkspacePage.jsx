import { useEffect,useState } from 'react'
import { Link } from '../../router/Router'
import { useApp } from '../../context/AppContext'
import { errorMessage } from '../../services/api'

const areas={courses:['My Courses','Create, edit and organize your professional course catalogue.'],students:['Learners','Review enrolled learners, progress and submitted work.'],projects:['Instructor Projects','Create practical briefs and evaluate project submissions.'],analytics:['Course Performance','Review real learning and completion data.']}

export default function InstructorWorkspacePage({area='courses'}){
  const{api}=useApp();const[items,setItems]=useState([]),[summary,setSummary]=useState(null),[error,setError]=useState('');const[title,copy]=areas[area]||areas.courses
  useEffect(()=>{setError('');if(area==='courses')api.get('/courses').then(r=>setItems(r.data?.data||[])).catch(e=>setError(errorMessage(e)));else if(area==='students')api.get('/students').then(r=>setItems(r.data?.data||r.data||[])).catch(e=>setError(errorMessage(e)));else if(area==='projects')api.get('/public/projects').then(r=>setItems(r.data?.data||[])).catch(e=>setError(errorMessage(e)));else api.get('/dashboard').then(r=>setSummary(r.data)).catch(e=>setError(errorMessage(e)))},[area,api])
  return <main className="p-page"><section className="p-page-hero compact"><div className="wrap"><span className="p-eyebrow">Instructor workspace</span><h1>{title}</h1><p>{copy}</p><div className="p-actions"><Link className="btn btn-primary" to="/instructor/courses/create">Create course</Link><Link className="btn btn-ghost" to="/instructor/projects">Projects</Link></div>{error&&<p className="p-notice">{error}</p>}</div></section><section className="p-section wrap">
  {area==='analytics'&&<div className="portal-stats">{Object.entries(summary?.counts||{}).map(([key,value])=><article key={key}><div><strong>{value}</strong><span>{key.replaceAll('_',' ')}</span></div></article>)}</div>}
  {area!=='analytics'&&<div className="p-listings">{items.map(item=><article key={item.id}><div className="p-list-icon">{area==='courses'?'▤':area==='students'?'◎':'◇'}</div><div className="p-list-main"><span className="p-eyebrow">{item.status||item.role||item.difficulty||'active'}</span><h2>{item.title||item.name||`Record ${item.id}`}</h2><p>{item.description||item.email||item.course_code||'MEHARA learning record'}</p>{area==='courses'&&<div className="p-list-meta"><span>{item.lessons_count||0} lessons</span><span>{item.price_lyd||0} LYD</span><span>{item.is_published?'Published':'Draft'}</span></div>}</div>{area==='courses'&&<Link className="btn btn-ghost" to={`/instructor/courses/${item.id}/edit`}>Edit</Link>}</article>)}{!items.length&&<div className="p-empty"><strong>No records yet.</strong><p>Create or assign content and it will appear here.</p></div>}</div>}
  </section></main>
}
