# MISR product filesystem audit

This mapping records the on-disk transformation performed for the MISR Phase 1 experience. Backend tables and APIs with legacy names are retained where they still power reusable course, assignment, submission, grading, or authentication behavior.

| Existing area | Decision | MISR purpose |
| --- | --- | --- |
| Authentication and verification | KEEP / MODIFY | Single learner signup, MISR login branding, existing secure token flow |
| Parent dashboard and parent navigation | REMOVE from UI | Replaced by the individual learner dashboard |
| Teacher portal | MODIFY | Role-gated instructor workspace and course authoring |
| Student portal | MODIFY | Learning, projects, certificates, mentorship and opportunities |
| Worksheets | MODIFY | Applied projects and assignments; backend endpoint retained for compatibility |
| Courses and lessons | KEEP / MODIFY | Marketplace, course details and structured course player |
| Grades and submissions | KEEP | Project and assignment evaluation foundation |
| School-specific public pages | REMOVE from UI | Government and organization training foundation |
| Child-focused main experience | REMOVE from main UI | Separate Kids Learning area for age-appropriate digital skills |
| Old mascot and cartoon assets | REMOVE | Original professional MISR learning imagery and restrained UI graphics |
| Old Masar/EduSphere branding | REMOVE | MISR wordmark, metadata, auth, navigation and footer |

## New and synchronized route areas

- `/` MISR landing and Learn → Practice → Build → Certify → Work → Grow journey
- `/courses` and `/courses/:id` marketplace and course detail
- `/learning/:id` course player
- `/dashboard` independent learner dashboard
- `/projects`, `/assignments`, `/opportunities`, `/mentorship`, `/live-classes`, `/certificates`
- `/instructor` plus instructor courses, learners, projects and analytics
- `/admin` platform impact and operations dashboard
- `/government-training`, `/kids-learning`, `/services`, `/profile`

## Compatibility decisions

- Database migrations, models and APIs were not deleted merely because they use legacy role or worksheet terminology.
- Public signup creates an independent learner. Instructor/admin access remains role-gated for existing managed accounts.
- No ministry-recognition claim is made for certificates.
