import { Link } from '../../router/Router'

const pathways = [
  ['Digital workplace skills','Office productivity, collaboration and responsible information handling.'],
  ['Cybersecurity awareness','Practical risk awareness for employees and institutional teams.'],
  ['Digital transformation','Service design, process mapping and measurable modernization.'],
  ['Teacher development','Digital pedagogy, assessment and modern learning delivery.'],
]

export default function GovernmentTrainingPage(){
  return <main className="p-page"><section className="p-page-hero"><div className="wrap"><span className="p-eyebrow">Government & professional training</span><h1>Build institutional capability through practical learning</h1><p>Flexible training foundations for public-sector teams, educators and organizations across Libya.</p><div className="p-actions"><Link className="btn btn-primary" to="/courses">Explore training</Link><Link className="btn btn-ghost" to="/services">Request an organization pathway</Link></div></div></section><section className="p-section wrap"><div className="p-service-grid">{pathways.map(([title,copy],index)=><article key={title}><span>{String(index+1).padStart(2,'0')}</span><h3>{title}</h3><p>{copy}</p></article>)}</div></section></main>
}
