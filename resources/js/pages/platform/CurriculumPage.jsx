import { useEffect, useMemo, useState } from 'react'
import { Link } from '../../router/Router'

const tracks=['البرمجة وتطوير الويب','قواعد البيانات','Microsoft Office','الذكاء الاصطناعي','التصميم','التسويق الرقمي']
export default function CurriculumPage(){
  const [courses,setCourses]=useState([])
  useEffect(()=>{fetch('/api/public/courses').then(r=>r.json()).then(r=>setCourses(r.data||[])).catch(()=>setCourses([]))},[])
  const groups=useMemo(()=>tracks.map((track,index)=>({track,courses:courses.filter((_,i)=>i%tracks.length===index)})),[courses])
  return <main className="p-page" dir="rtl"><section className="p-page-hero"><div className="wrap"><span className="p-eyebrow">المسارات التعليمية</span><h1>خارطة المحتوى التعليمي الكاملة</h1><p>اختر مجالاً، ابدأ من المستوى المناسب، وتقدم عبر دروس ومشاريع مترابطة.</p></div></section><section className="p-section wrap"><div className="p-service-grid">{groups.map(({track,courses:items},i)=><article key={track}><span>{String(i+1).padStart(2,'0')}</span><h3>{track}</h3><p>{items.length?`${items.length} دورة متاحة حالياً`:'سيتم نشر دورات هذا المسار قريباً'}</p>{items.slice(0,3).map(c=><p key={c.id}><Link to={`/courses/${c.id}`}>← {c.title}</Link></p>)}<Link className="btn btn-ghost" to="/courses">استكشف الدورات</Link></article>)}</div></section></main>
}
