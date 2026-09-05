<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'MISR-Demo-2026!';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'auth.demo.enabled' => true,
            'auth.demo.password' => self::PASSWORD,
            'auth.demo.student_email' => 'student01@example.test',
            'auth.demo.instructor_email' => 'instructor01@example.test',
            'auth.demo.admin_email' => 'admin01@example.test',
        ]);
    }

    public function test_new_test_email_creates_verified_student_and_logs_in(): void
    {
        $response = $this->postJson('/api/auth/demo-login', [
            'email' => 'learner99@example.test',
            'password' => self::PASSWORD,
        ])->assertOk()->assertJsonPath('user.role', 'student')->assertJsonPath('user.is_test_user', true);

        $this->assertNotEmpty($response->json('token'));
        $user = User::whereEmail('learner99@example.test')->firstOrFail();
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check(self::PASSWORD, $user->password));
    }

    public function test_repeated_demo_login_reuses_the_same_user(): void
    {
        $payload = ['email' => 'repeat@example.test', 'password' => self::PASSWORD];
        $this->postJson('/api/auth/demo-login', $payload)->assertOk();
        $this->postJson('/api/auth/demo-login', $payload)->assertOk();
        $this->assertSame(1, User::whereEmail('repeat@example.test')->count());
    }

    public function test_existing_normal_user_is_not_modified_or_bypassed(): void
    {
        $user = User::factory()->create(['email' => 'existing@example.test', 'password' => 'NormalPassword123!', 'role' => 'student', 'is_test_user' => false]);
        $this->postJson('/api/auth/demo-login', ['email' => $user->email, 'password' => self::PASSWORD])->assertUnprocessable();
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'NormalPassword123!'])->assertOk();
        $this->assertFalse($user->fresh()->is_test_user);
    }

    public function test_invalid_or_non_test_email_is_rejected(): void
    {
        $this->postJson('/api/auth/demo-login', ['email' => 'not-an-email', 'password' => self::PASSWORD])->assertUnprocessable();
        $this->postJson('/api/auth/demo-login', ['email' => 'person@example.com', 'password' => self::PASSWORD])->assertUnprocessable();
    }

    public function test_only_allowlisted_accounts_receive_elevated_roles(): void
    {
        $this->postJson('/api/auth/demo-login', ['email' => 'instructor01@example.test', 'password' => self::PASSWORD])->assertOk()->assertJsonPath('user.role', 'teacher');
        $this->postJson('/api/auth/demo-login', ['email' => 'admin01@example.test', 'password' => self::PASSWORD])->assertOk()->assertJsonPath('user.role', 'admin');
        $this->postJson('/api/auth/demo-login', ['email' => 'admin-hacker@example.test', 'password' => self::PASSWORD])->assertOk()->assertJsonPath('user.role', 'student');
    }

    public function test_wrong_demo_password_is_rejected(): void
    {
        $this->postJson('/api/auth/demo-login', ['email' => 'learner@example.test', 'password' => 'wrong-password'])->assertUnprocessable();
        $this->assertDatabaseMissing('users', ['email' => 'learner@example.test']);
    }

    public function test_disabled_demo_mode_cannot_bypass_authentication(): void
    {
        config(['auth.demo.enabled' => false]);
        $this->postJson('/api/auth/demo-login', ['email' => 'learner@example.test', 'password' => self::PASSWORD])->assertNotFound();
    }

    public function test_production_environment_forces_demo_mode_off(): void
    {
        $this->app['env'] = 'production';
        $this->getJson('/api/auth/demo-status')->assertOk()->assertJsonPath('enabled', false);
        $this->postJson('/api/auth/demo-login', ['email' => 'learner@example.test', 'password' => self::PASSWORD])->assertNotFound();
    }
}
