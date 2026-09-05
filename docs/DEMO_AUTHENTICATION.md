# Development demo authentication

## Purpose

Normal MISR login only authenticates an existing database user whose submitted password matches the stored hash. Demo authentication is a separate local/testing-only endpoint for reserved `.test` identities; it does not alter normal login behavior.

## Environment

```dotenv
AUTH_DEMO_MODE=true
AUTH_DEMO_PASSWORD=MISR-Demo-2026!
AUTH_DEMO_STUDENT_EMAIL=student01@example.test
AUTH_DEMO_INSTRUCTOR_EMAIL=instructor01@example.test
AUTH_DEMO_ADMIN_EMAIL=admin01@example.test
```

Run `php artisan config:clear` after changing these values. Disable the feature by setting `AUTH_DEMO_MODE=false` or removing the variable.

## Security boundaries

- Both `APP_ENV=local|testing` and `AUTH_DEMO_MODE=true` are required.
- `APP_ENV=production` always disables the demo endpoints, even if the flag is accidentally true.
- Only `.test` email addresses are accepted.
- A configured password is required and is hashed normally when a test user is created.
- Existing non-test users cannot be accessed or modified through the demo endpoint.
- Arbitrary `.test` identities receive the learner role only.
- Instructor and administrator roles are assigned only to exact allowlisted configuration addresses.
- Demo users are marked with `is_test_user=true` and verified only inside the guarded demo flow.
- Normal Sanctum tokens, verified middleware and RBAC are used after login.

## Tester flow

1. Open `/login` locally.
2. Enter the configured demo password.
3. Choose Student Demo, Instructor Demo or Admin Demo; or enter a new `.test` address and use the controlled demo endpoint.
4. The first successful request creates an identifiable test user. Later requests reuse it without duplication.

The normal login button continues to call `/api/auth/login`. Demo buttons call the separate `/api/auth/demo-login` endpoint.
