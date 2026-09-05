# MEHARA HTML → React implementation map

The supplied archive is a design reference. Application behavior remains in React, Laravel APIs, and SQLite.

| HTML file | Purpose | Classification | React destination | Live data/API |
|---|---|---|---|---|
| `index.html` | Public home | MODIFY | `HomePage.jsx` | public courses |
| `courses.html` | Course catalogue | MODIFY | `CourseMarketplacePage.jsx` | public/auth courses |
| `course-details.html` | Course detail and registration | MODIFY | `CourseDetailsPage.jsx` | course, registration |
| `auth.html` | Login/register/onboarding | MODIFY | `Login.jsx`, `Signup.jsx` | auth APIs |
| `dashboard.html` | Learner dashboard | MODIFY | `StudentDashboardPage.jsx` | courses, events, notifications |
| `course-template.html` | Lesson player | MODIFY | `LearningPlayerPage.jsx` | course, lessons, progress |
| `curriculum.html` | Curriculum catalogue | NEW | `CurriculumPage.jsx` | public courses |
| `progress.html` | Quizzes/projects/certificates | NEW | `LearningProgressPage.jsx` | quizzes, assignments, certificates |
| `certificate-view.html` | Certificate presentation | MODIFY | `CertificatesPage.jsx` | certificates |
| `profile.html` | Profile and notification settings | MODIFY | `ProfessionalProfilePage.jsx` | profile, notifications |
| `community.html` | Learner community | NEW | `CommunityPage.jsx` | messages |
| `instructor-chat.html` | Trainer communication | MODIFY | `ConnectedLms` messages | messages |
| `kids.html` | Kids learning catalogue | MODIFY | `KidsLearningPage.jsx` | public courses |
| `about.html` | About MEHARA | MODIFY | `About.jsx` | static institutional copy |
| `help.html` | Help centre | MODIFY | `Help.jsx` | support contact foundation |
| `forgot-password.html` | Password recovery | KEEP/MODIFY | `ForgotPassword.jsx` | password reset API |
| `privacy.html` | Privacy policy | KEEP/MODIFY | `Privacy.jsx` | static policy |
| `terms.html` | Terms | KEEP/MODIFY | `Terms.jsx` | static policy |

## Connected flow

Courses → details → registration → payment proof → management review → approved enrollment → learning → quiz/project → certificate.

Old parent/primary-school surfaces remain backend-compatible but are not exposed in MEHARA's public or learner navigation.
