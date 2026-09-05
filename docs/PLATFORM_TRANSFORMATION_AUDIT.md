# Libya Skills Platform — transformation audit

This audit was completed before implementation. The guiding rule is to preserve working Laravel APIs, Sanctum authentication, RBAC, assignments, submissions, reviews, progress, certificates, and reusable React foundations while replacing the old primary-school presentation.

| Old page / feature | Decision | New page or purpose |
|---|---|---|
| Cartoon-led landing page | Modify | Professional Arabic-ready national skills landing page built around **Learn → Apply → Earn** |
| Worksheets catalogue | Modify | Projects & Assignments resource engine; existing upload/download and access policies stay intact |
| Worksheet submissions and teacher reviews | Keep + relabel | Real project delivery, resubmission, instructor feedback, and evaluation workflow |
| Courses | Modify | Public course marketplace with search, category and level filters; authenticated enrollment and progress remain backend-connected |
| Course detail / lesson completion | Modify | Professional course overview and course player using existing course, lesson, enrollment and completion APIs |
| Activities / quizzes | Modify | Assessments attached to learning and project readiness |
| Workbooks / bundles | Modify | Learning resources and project toolkits, hidden from primary navigation |
| Student dashboard | Modify | My Learning, projects, certificates, mentorship, live classes, notifications, and opportunities |
| Teacher dashboard | Modify | Instructor workspace for courses, learners, assignments/projects, reviews, live classes and performance |
| Admin dashboard | Modify | Ministry-ready impact and governance view populated only from backend counts |
| Parent dashboard | Keep, secondary | Connected learner support; no longer a public platform pillar |
| Calendar | Modify | Live classes, sessions, deadlines, and events; external meeting links are Phase 1 |
| Certificates | Modify | Official-looking learner records with platform verification wording and `LIB-EDU-…` display IDs |
| Messages / notifications / profile | Keep + restyle | Professional communication and learning portfolio |
| Subjects / school classes | Keep backend, reduce prominence | Categories, cohorts and institution training groups |
| Billing | Defer | LYD-ready pricing foundation; payments are Phase 2 |
| Fox/panda/dinosaur companions and bouncing sidebar decoration | Remove from active UI | Clean navigation and mature technology-focused visual language |
| Kids learning content | Move | Dedicated future Kids Learning area; not the main platform identity |
| Services | Add foundation | Design, research, presentations, development and digital services request pathway |
| Government training | Add foundation | Government & Professional Training category and organization CTA |
| Projects | Add | Real-world practice listings mapped to the current assignment/submission engine |
| Opportunities | Add | MVP job/freelance/internship discovery interface; application backend is Phase 2 |
| Mentorship | Add | MVP mentor discovery and session request foundation |

## Artifact review

- `.git/`: old remote points to the previous `lms-s` repository; remove metadata as explicitly requested. No commits or branches will be created.
- `Dockerfile`, `docker-compose.yml`, `.dockerignore`, `docker/`: self-contained previous deployment setup and not referenced by Laravel/Vite runtime; safe to remove for this local phase.
- `composer-build.log`: obsolete Docker build output; safe to remove.
- `docs/`: previous-project documentation is superseded by this audit and a new project README; backend architecture references remain useful and are not deleted blindly.
- `.agents/`: empty; no runtime dependency.
- Core migrations, models, controllers, policies, tests and storage conventions: retained.

## Phase boundary

Phase 1 provides the professional concept, navigable user flows and existing-data integration. Payments, opportunity applications, mentor booking infrastructure, institutional tenancy, advanced analytics and certificate verification services remain explicit Phase 2 work.
