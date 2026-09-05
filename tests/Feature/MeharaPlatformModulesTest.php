<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeharaPlatformModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_publish_projects_and_learners_can_apply(): void
    {
        $teacher=User::factory()->create(['role'=>'teacher','email_verified_at'=>now()]); $learner=User::factory()->create(['role'=>'student','email_verified_at'=>now()]);
        Sanctum::actingAs($teacher); $project=$this->postJson('/api/projects',['title'=>'Portfolio challenge','description'=>'Build and document a practical result.','category'=>'Development','skills'=>['React','CSS'],'difficulty'=>'intermediate'])->assertCreated()->json('id');
        $this->getJson('/api/public/projects')->assertOk()->assertJsonPath('data.0.title','Portfolio challenge');
        Sanctum::actingAs($learner); $this->postJson("/api/projects/$project/apply",['cover_note'=>'Ready to build.'])->assertCreated();
        $this->assertDatabaseHas('project_applications',['project_id'=>$project,'user_id'=>$learner->id]);
    }

    public function test_opportunity_mentorship_service_and_note_flows_persist(): void
    {
        $admin=User::factory()->create(['role'=>'admin','email_verified_at'=>now()]); $learner=User::factory()->create(['role'=>'student','email_verified_at'=>now()]);
        Sanctum::actingAs($admin); $opportunity=$this->postJson('/api/opportunities',['title'=>'Frontend internship','organization'=>'MEHARA Partner','description'=>'Supervised placement.','type'=>'internship','category'=>'Development','skills'=>['React'],'is_remote'=>true])->assertCreated()->json('id');
        $this->putJson('/api/mentor-profile',['headline'=>'Frontend mentor','expertise'=>['React'],'experience_years'=>6,'session_price_lyd'=>50,'availability'=>'available','is_published'=>true])->assertOk();
        $mentor=DB::table('mentor_profiles')->where('user_id',$admin->id)->value('id');
        $course=DB::table('courses')->insertGetId(['created_by'=>$admin->id,'title'=>'React','slug'=>'react-test','difficulty'=>'beginner','is_published'=>true,'created_at'=>now(),'updated_at'=>now()]);
        $lesson=DB::table('lessons')->insertGetId(['course_id'=>$course,'title'=>'Hooks','position'=>1,'is_published'=>true,'created_at'=>now(),'updated_at'=>now()]);
        Sanctum::actingAs($learner);
        $this->postJson("/api/opportunities/$opportunity/apply",['cover_note'=>'Interested'])->assertCreated();
        $this->postJson("/api/mentors/$mentor/book",['scheduled_at'=>now()->addDay()->toISOString(),'goals'=>'Portfolio review'])->assertCreated();
        $this->postJson('/api/service-requests',['category'=>'Design','title'=>'Deck design','description'=>'Create a professional presentation.'])->assertCreated();
        $this->putJson("/api/lessons/$lesson/note",['body'=>'Review this concept.','bookmarked'=>true])->assertOk();
        $this->assertDatabaseHas('lesson_notes',['lesson_id'=>$lesson,'user_id'=>$learner->id,'bookmarked'=>true]);
    }

    public function test_instructor_can_create_complete_course_draft(): void
    {
        $teacher=User::factory()->create(['role'=>'teacher','email_verified_at'=>now()]); Sanctum::actingAs($teacher);
        $id=$this->postJson('/api/courses',['title'=>'Modern Web Skills','description'=>'A practical course.','difficulty'=>'beginner','price_lyd'=>100,'language'=>'both','lessons'=>[['title'=>'Foundations','video_url'=>'https://example.test/video.mp4','duration_minutes'=>20]]])->assertCreated()->json('id');
        $this->assertDatabaseHas('courses',['id'=>$id,'created_by'=>$teacher->id,'price_lyd'=>100]); $this->assertDatabaseHas('lessons',['course_id'=>$id,'title'=>'Foundations']);
    }

    public function test_admin_can_publish_news_and_public_can_read_it(): void
    {
        $admin=User::factory()->create(['role'=>'admin','email_verified_at'=>now()]); Sanctum::actingAs($admin);
        $article=$this->postJson('/api/admin/news',['title'=>'Future skills update','excerpt'=>'A verified technology update.','body'=>'MEHARA learners can now follow current technology and education updates.','category'=>'Technology','language'=>'both','is_published'=>true])->assertCreated()->json();
        $this->getJson('/api/public/news')->assertOk()->assertJsonPath('data.0.title','Future skills update');
        $this->getJson('/api/public/news/'.$article['slug'])->assertOk()->assertJsonPath('author',$admin->name);
    }

    public function test_admin_can_broadcast_and_learner_overview_contains_timestamps(): void
    {
        $admin=User::factory()->create(['role'=>'admin','email_verified_at'=>now()]);$learner=User::factory()->create(['role'=>'student','email_verified_at'=>now()]);
        Sanctum::actingAs($admin);$this->postJson('/api/admin/notifications',['title'=>'New course','message'=>'A new course is available.','audience'=>'students'])->assertOk()->assertJsonPath('recipients',1);
        Sanctum::actingAs($learner);$this->getJson('/api/learner-overview')->assertOk()->assertJsonStructure(['profile'=>['name','email','created_at'],'enrollments','registrations','certificates','activity','projects','opportunities','mentorship']);
        $this->getJson('/api/notifications')->assertOk()->assertJsonPath('data.0.data.title','New course');
    }
}
