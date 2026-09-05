import { useEffect, useState } from 'react'
import { useApp } from '../../context/AppContext'
import { useRouter } from '../../router/Router'
import { errorMessage } from '../../services/api'
import '../pages.css'
import '../Portal.css'

export default function ProjectAssignmentsPage() {
  const { api } = useApp()
  const { navigate } = useRouter()
  const [items, setItems] = useState([])
  const [query, setQuery] = useState('')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  useEffect(() => { api.get('/worksheets').then(({ data }) => setItems(data.data || [])).catch((requestError) => setError(errorMessage(requestError))).finally(() => setLoading(false)) }, [api])
  const visible = items.filter((item) => `${item.title} ${item.subject} ${item.grade_level}`.toLowerCase().includes(query.toLowerCase()))
  return <main className="portal worksheet-world"><div className="catalogue-head"><div><span className="eyebrow">Applied learning library</span><h1>Projects & assignments</h1><p>Find a practical brief, complete the work and submit it for professional review.</p></div></div>
    <label className="search-shell"><span aria-hidden="true">⌕</span><input className="portal-search" value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Search projects and assignments" /></label>
    {loading && <p>Loading projects and assignments...</p>}{error && <div className="portal-error">{error}</div>}
    <section className="worksheet-grid">{visible.map((item, index) => <article className={`worksheet-tile tone-${index % 4}`} key={item.id} role="button" tabIndex="0" onClick={() => navigate(`/assignments/${item.id}`)} onKeyDown={(event) => event.key === 'Enter' && navigate(`/assignments/${item.id}`)}><span className="worksheet-glyph" aria-hidden="true">{['⌘','Aa','∑','◎'][index % 4]}</span><div><span className="worksheet-subject">{item.subject}</span><strong>{item.title}</strong><small>{item.grade_level}</small></div><span className="tile-arrow" aria-hidden="true">→</span></article>)}</section>
    {!loading && !visible.length && <p>No projects or assignments match your access and search.</p>}
  </main>
}
