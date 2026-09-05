export default function StatCard({ label, value, icon='◎' }){ return <article><i aria-hidden="true">{icon}</i><div><strong>{value}</strong><span>{label}</span></div></article> }
