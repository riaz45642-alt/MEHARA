import { useEffect, useState } from 'react'
import { useApp } from '../context/AppContext'
import { useRouter } from '../router/Router'
import { errorMessage } from '../services/api'
import './ConnectedLms.css'

const labels={courses:'Courses',lessons:'Lessons',worksheets:'Projects & assignments',workbooks:'Learning resources',activities:'Assessments',classes:'Cohorts',users:'People'}
const paths={courses:'courses',lessons:'courses',worksheets:'assignments',workbooks:'workbooks',activities:'activities',classes:'classes',users:'users'}
export default function GlobalSearch(){const{api}=useApp();const{navigate}=useRouter();const[q,setQ]=useState(''),[data,setData]=useState(null),[loading,setLoading]=useState(false),[error,setError]=useState('')
  useEffect(()=>{if(q.trim().length<2){setData(null);setError('');return}const timer=setTimeout(()=>{setLoading(true);setError('');api.get('/search',{params:{q:q.trim()}}).then(r=>setData(r.data)).catch(e=>setError(errorMessage(e))).finally(()=>setLoading(false))},300);return()=>clearTimeout(timer)},[q])
  const count=data?Object.values(data).reduce((n,x)=>n+x.length,0):0
  return <main className="connected-lms world-professional"><section className="lms-hero"><div><span className="lms-eyebrow">Platform search</span><h1>Search MISR</h1><p>Find courses, lessons, projects, resources and learning groups.</p></div><div className="lms-mascot" aria-hidden="true">⌕</div></section><div className="lms-toolbar"><label><span aria-hidden="true">⌕</span><input autoFocus value={q} onChange={e=>setQ(e.target.value)} placeholder="Search courses, lessons, projects and resources…" aria-label="Search MISR"/></label></div>
    {loading&&<div className="lms-state" role="status">Searching the platform…</div>}{error&&<div className="lms-state error" role="alert">{error}</div>}{!loading&&q.length<2&&<div className="lms-state">Type at least two letters to begin.</div>}{data&&!loading&&count===0&&<div className="lms-state"><span className="state-face">⌕</span><strong>No matching results</strong><span>Try another word or a broader topic.</span></div>}
    {data&&!loading&&Object.entries(data).map(([group,items])=>items.length>0&&<section key={group}><h2>{labels[group]||group}</h2><div className="adventure-grid">{items.map(x=><button className="adventure-card" style={{textAlign:'left',color:'inherit',cursor:'pointer',width:'100%'}} key={x.id} onClick={()=>navigate(`/${paths[group]}/${group==='lessons'?x.course_id:x.id}`)}><div className="card-copy"><span className="card-kicker">{x.subject||x.role||group}</span><h2>{x.title||x.name}</h2><p>{x.description||x.summary||x.email||x.grade_level}</p></div><span aria-hidden="true">→</span></button>)}</div></section>)}
  </main>}
