<?php

namespace Tests\Feature;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeharaRegistrationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_course_requires_review_before_access(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        StudentProfile::create(['user_id' => $student->id]);
        $course = DB::table('courses')->insertGetId(['created_by' => $admin->id, 'title' => 'Python عملي', 'slug' => 'python-mehara', 'course_code' => 'MEHARA-PY-001', 'difficulty' => 'beginner', 'price_lyd' => 199, 'is_published' => true, 'created_at' => now(), 'updated_at' => now()]);

        Sanctum::actingAs($student);
        $registration = $this->postJson("/api/courses/$course/register")->assertCreated()->assertJsonPath('status', 'pending_payment')->json('id');
        $this->assertDatabaseMissing('course_enrollments', ['course_id' => $course, 'user_id' => $student->id]);
        $this->post("/api/course-registrations/$registration/payment-proof", ['proof' => UploadedFile::fake()->create('receipt.pdf', 120, 'application/pdf')], ['Accept' => 'application/json'])->assertCreated()->assertJsonPath('status', 'pending_review');

        Sanctum::actingAs($admin);
        $this->patchJson("/api/admin/course-registrations/$registration", ['status' => 'approved'])->assertOk();
        $this->assertDatabaseHas('course_enrollments', ['course_id' => $course, 'user_id' => $student->id]);
        $this->assertDatabaseHas('course_registrations', ['id' => $registration, 'status' => 'approved', 'reviewed_by' => $admin->id]);
    }

    public function test_free_course_is_approved_and_enrolled_immediately(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        StudentProfile::create(['user_id' => $student->id]);
        $course = DB::table('courses')->insertGetId(['created_by' => $admin->id, 'title' => 'Digital Basics', 'slug' => 'digital-basics', 'difficulty' => 'beginner', 'price_lyd' => 0, 'is_published' => true, 'created_at' => now(), 'updated_at' => now()]);
        Sanctum::actingAs($student);
        $this->postJson("/api/courses/$course/register")->assertCreated()->assertJsonPath('status', 'approved');
        $this->assertDatabaseHas('course_enrollments', ['course_id' => $course, 'user_id' => $student->id]);
    }
}
