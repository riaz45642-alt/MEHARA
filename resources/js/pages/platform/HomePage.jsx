import { useEffect, useState } from 'react'
import { Link } from '../../router/Router'
import Reveal from '../../components/Reveal'
import { CourseCard } from '../Platform'
import { showcaseCourses } from '../../data/platformData'
import { useApp } from '../../context/AppContext'
import './HomePage.css'

const categories = [['</>','البرمجة'],['✎','التصميم'],['▤','قواعد البيانات'],['✦','الذكاء الاصطناعي'],['▣','Microsoft Office'],['◉','التسويق']]
const benefits = [['⚙','تعلم عملي','تطبيق مباشر على مهارات مطلوبة'],['◇','مشاريع حقيقية','أعمال تضيفها إلى معرضك'],['▣','شهادات موثقة','سجل إنجاز قابل للتحقق'],['♙','مدربون محترفون','خبراء بخبرة عملية'],['◷','تعلم في أي وقت','تقدم بالسرعة التي تناسبك'],['↗','متابعة التقدم','صورة واضحة لرحلتك']]

export default function HomePage(){
  const {language}=useApp(); const t=(ar,en)=>language==='ar'?ar:en
  const [courses,setCourses]=useState(showcaseCourses)
  useEffect(()=>{fetch('/api/public/courses').then(r=>r.ok?r.json():Promise.reject()).then(r=>r.data?.length&&setCourses(r.data)).catch(()=>{})},[])
  const moveScene=e=>{const b=e.currentTarget.getBoundingClientRect();e.currentTarget.style.setProperty('--mx',`${((e.clientX-b.left)/b.width-.5)*8}px`);e.currentTarget.style.setProperty('--my',`${((e.clientY-b.top)/b.height-.5)*8}px`)}
  return <main className="meh-home">
    <section className="meh-hero"><div className="hero-grid-field" aria-hidden="true"></div><div className="hero-coordinate" aria-hidden="true">32.8872° N · 13.1913° E</div><div className="wrap meh-hero-grid">
      <Reveal className="meh-hero-copy"><span className="meh-kicker"><i></i>{t('منصة تعليمية عملية لبناء مهارات المستقبل','A practical learning platform for future-ready skills')}</span><h1>{t('لا تتعلّم فقط.','Don’t just learn.')}<br/><em>{t('اصنع ما يثبت','Build proof of')}</em><br/>{t('قدرتك.','your ability.')}</h1><p>{t('منصة تعليمية تساعدك على اكتساب المهارات المطلوبة لسوق العمل من خلال دورات عملية ومسارات تعليمية احترافية.','Gain workplace skills through practical courses, guided pathways and real applied work.')}</p><div className="meh-actions"><Link className="btn btn-primary" to="/courses">{t('ابدأ رحلتك الآن','Start your journey')} <b>↗</b></Link><Link className="btn btn-ghost" to="/courses">{t('استكشف الدورات','Explore courses')} <span>06</span></Link></div><div className="meh-trust"><span>{t('تعلم عملي','Practical learning')}</span><span>{t('مدربون محترفون','Professional trainers')}</span><span>{t('شهادات موثقة','Verifiable certificates')}</span></div></Reveal>
      <Reveal className="meh-photo-stage" variant="left" onPointerMove={moveScene} onPointerLeave={e=>{e.currentTarget.style.setProperty('--mx','0px');e.currentTarget.style.setProperty('--my','0px')}}><div className="hero-frame-label">MEHARA / LEARNER 01</div><img src="/images/misr/misr-student-learning-hero.png" alt="طالب يتعلم على الحاسوب المحمول في بيئة تعليمية حديثة"/><div className="hero-scan" aria-hidden="true"></div><div className="meh-progress-card"><small>تقدمك هذا الأسبوع</small><strong>75<sup>%</sup></strong><span>12 درساً مكتملًا</span></div><div className="meh-path-card"><small>رحلتك الحالية</small><strong>تطوير الويب</strong><span>تابع من الدرس التالي ←</span></div><div className="hero-skill-orbit" aria-hidden="true"><i>UI</i><i>AI</i><i>01</i></div></Reveal>
    </div></section>
    <section className="wrap meh-categories" aria-label="فئات الدورات"><div className="category-command"><small>EXPLORE BY SKILL</small><strong>⌘ K</strong></div>{categories.map(([icon,label],index)=><Link to="/courses" key={label}><span>{String(index+1).padStart(2,'0')}</span><i>{icon}</i><strong>{label}</strong></Link>)}</section>
    <section className="p-section wrap"><Reveal className="meh-section-head"><div><span>دورات مختارة بعناية</span><h2>الدورات الأكثر طلباً</h2></div><Link to="/courses">عرض جميع الدورات ←</Link></Reveal><Reveal className="p-course-grid meh-course-grid" stagger>{courses.slice(0,5).map((c,i)=><CourseCard key={c.id} course={{...showcaseCourses[i%showcaseCourses.length],...c}}/>)}</Reveal></section>
    <section className="meh-benefit-section"><div className="wrap"><Reveal className="meh-section-head"><div><span>تعليم يحقق نتيجة</span><h2>لماذا مِهارة؟</h2></div></Reveal><div className="meh-benefits">{benefits.map(([icon,title,copy])=><article key={title}><i>{icon}</i><div><h3>{title}</h3><p>{copy}</p></div></article>)}</div></div></section>
    <section className="meh-journey"><div className="journey-orb" aria-hidden="true"></div><div className="wrap"><span>رحلة تعلم متكاملة</span><h2>من المعرفة إلى أثر حقيقي.</h2><div className="journey-track">{['اختر','تعلّم','طبّق','أنجز','اعتمد'].map((step,index)=><div key={step}><i>{index+1}</i><strong>{step}</strong></div>)}</div><p>كل خطوة مرتبطة بما بعدها، من أول درس إلى مشروع حقيقي يثبت مهارتك.</p><Link className="btn btn-primary" to="/curriculum">استكشف المسارات التعليمية</Link></div></section>
  </main>
}
