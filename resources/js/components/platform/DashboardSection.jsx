export default function DashboardSection({ eyebrow, title, action, children }){
  return <section className="portal-section"><div className="section-title"><div>{eyebrow&&<span className="p-eyebrow">{eyebrow}</span>}<h2>{title}</h2></div>{action}</div>{children}</section>
}
