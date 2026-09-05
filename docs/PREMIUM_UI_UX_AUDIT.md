# MISR premium UI/UX audit

## Existing system assessment

| Area | Decision | Result |
| --- | --- | --- |
| React/Laravel architecture and API services | Keep | Existing functionality and backend contracts preserved |
| Sora + Inter typography | Keep / refine | Strong bilingual-friendly hierarchy with consistent weights and line height |
| Teal, midnight and copper direction | Keep / formalize | Consolidated as MISR trust, learning and achievement tokens |
| Shared buttons, course cards and page layouts | Modify | Clearer hierarchy, skills, actions, focus states and controlled surfaces |
| Sticky navigation | Modify | Custom MISR mark, clearer active state and compact mobile menu |
| Scroll progress and reveal system | Modify | RAF interpolation, Intersection Observer and reduced-motion support |
| Photography-led homepage hero | Replace | Platform-specific skill-path visualization |
| Generic learner dashboard | Replace | Action-led personal learning command center |
| Legacy course detail player | Replace | Responsive curriculum/content/tools learning workspace |
| Generic certificate listing | Replace | Verification-led institutional certificate presentation |
| Childish loading/error language | Remove | Professional skeleton, empty and recovery states |

## Signature product interactions

1. Goal-centered skill map in the homepage hero.
2. Scroll-activated Discover → Learn → Practice → Build → Assess → Certify → Career journey.
3. Dashboard next-step recommendation and capability path.

## Accessibility and responsive decisions

- Visible `:focus-visible` treatment across controls.
- Semantic headings, labels, progress descriptions and live state roles.
- `prefers-reduced-motion` disables non-essential movement.
- Course player reorganizes from three panels to a mobile-first vertical workflow.
- Major public layouts verified at desktop and 390px mobile widths.
