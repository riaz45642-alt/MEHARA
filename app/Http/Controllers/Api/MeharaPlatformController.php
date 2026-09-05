<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MeharaPlatformController extends Controller
{
    public function projects(Request $request)
    {
        return DB::table('projects')->leftJoin('users','users.id','=','projects.created_by')
            ->where('projects.status','open')->when($request->q,fn($q,$v)=>$q->where(fn($x)=>$x->where('projects.title','like','%'.$v.'%')->orWhere('projects.description','like','%'.$v.'%')))
            ->select('projects.*','users.name as owner')->selectSub(fn($q)=>$q->from('project_applications')->selectRaw('count(*)')->whereColumn('project_id','projects.id'),'applicants_count')->latest('projects.created_at')->paginate(15);
    }

    public function storeProject(Request $request)
    {
        $this->staff($request); $d=$request->validate(['course_id'=>['nullable','exists:courses,id'],'title'=>['required','string','max:255'],'description'=>['required','string'],'category'=>['required','string','max:80'],'skills'=>['nullable','array'],'skills.*'=>['string','max:80'],'difficulty'=>['required',Rule::in(['beginner','intermediate','advanced'])],'deadline'=>['nullable','date'],'max_applicants'=>['nullable','integer','min:1'],'status'=>['nullable',Rule::in(['draft','open','closed'])]]);
        $id=DB::table('projects')->insertGetId(array_merge($d,['skills'=>json_encode($d['skills']??[]),'created_by'=>$request->user()->id,'status'=>$d['status']??'open','created_at'=>now(),'updated_at'=>now()]));
        return response()->json(DB::table('projects')->find($id),201);
    }

    public function applyProject(Request $request,int $id)
    {
        abort_unless(DB::table('projects')->where(['id'=>$id,'status'=>'open'])->exists(),404); $d=$request->validate(['cover_note'=>['nullable','string','max:3000']]);
        DB::table('project_applications')->updateOrInsert(['project_id'=>$id,'user_id'=>$request->user()->id],$d+['status'=>'applied','created_at'=>now(),'updated_at'=>now()]);
        return response()->json(['message'=>'Project application submitted.'],201);
    }

    public function projectApplications(Request $request,int $id)
    {
        $this->staff($request); return DB::table('project_applications')->join('users','users.id','=','project_applications.user_id')->where('project_id',$id)->get(['project_applications.*','users.name','users.email']);
    }

    public function reviewProjectApplication(Request $request,int $id)
    {
        $this->staff($request); $d=$request->validate(['status'=>['required',Rule::in(['accepted','in_progress','submitted','completed','rejected'])],'score'=>['nullable','integer','min:0','max:100'],'feedback'=>['nullable','string','max:5000']]);
        abort_unless(DB::table('project_applications')->where('id',$id)->update($d+['updated_at'=>now()]),404); return ['message'=>'Project application updated.'];
    }

    public function opportunities(Request $request)
    {
        return DB::table('opportunities')->where('status','open')->when($request->q,fn($q,$v)=>$q->where(fn($x)=>$x->where('title','like','%'.$v.'%')->orWhere('description','like','%'.$v.'%')->orWhere('organization','like','%'.$v.'%')))
            ->select('*')->selectSub(fn($q)=>$q->from('opportunity_applications')->selectRaw('count(*)')->whereColumn('opportunity_id','opportunities.id'),'applicants_count')->latest()->paginate(15);
    }

    public function storeOpportunity(Request $request)
    {
        $this->admin($request); $d=$request->validate(['title'=>['required','string','max:255'],'organization'=>['required','string','max:255'],'description'=>['required','string'],'type'=>['required',Rule::in(['job','internship','freelance','paid_task'])],'category'=>['required','string','max:80'],'skills'=>['nullable','array'],'location'=>['nullable','string','max:120'],'is_remote'=>['boolean'],'budget_min_lyd'=>['nullable','integer','min:0'],'budget_max_lyd'=>['nullable','integer','gte:budget_min_lyd'],'deadline'=>['nullable','date'],'status'=>['nullable',Rule::in(['draft','open','closed'])]]);
        $id=DB::table('opportunities')->insertGetId(array_merge($d,['skills'=>json_encode($d['skills']??[]),'created_by'=>$request->user()->id,'status'=>$d['status']??'open','created_at'=>now(),'updated_at'=>now()])); return response()->json(DB::table('opportunities')->find($id),201);
    }

    public function applyOpportunity(Request $request,int $id)
    {
        abort_unless(DB::table('opportunities')->where(['id'=>$id,'status'=>'open'])->exists(),404); $d=$request->validate(['cover_note'=>['nullable','string','max:3000'],'portfolio_url'=>['nullable','url','max:500']]);
        DB::table('opportunity_applications')->updateOrInsert(['opportunity_id'=>$id,'user_id'=>$request->user()->id],$d+['status'=>'submitted','created_at'=>now(),'updated_at'=>now()]); return response()->json(['message'=>'Opportunity application submitted.'],201);
    }

    public function mentors(Request $request)
    {
        return DB::table('mentor_profiles')->join('users','users.id','=','mentor_profiles.user_id')->where('is_published',true)
            ->when($request->q,fn($q,$v)=>$q->where(fn($x)=>$x->where('users.name','like','%'.$v.'%')->orWhere('headline','like','%'.$v.'%')))
            ->get(['mentor_profiles.*','users.name','users.email']);
    }

    public function saveMentorProfile(Request $request)
    {
        $this->staff($request); $d=$request->validate(['headline'=>['required','string','max:255'],'bio'=>['nullable','string','max:5000'],'expertise'=>['nullable','array'],'experience_years'=>['integer','min:0','max:80'],'session_price_lyd'=>['integer','min:0'],'availability'=>['required',Rule::in(['available','limited','unavailable'])],'is_published'=>['boolean']]);
        DB::table('mentor_profiles')->updateOrInsert(['user_id'=>$request->user()->id],array_merge($d,['expertise'=>json_encode($d['expertise']??[]),'created_at'=>now(),'updated_at'=>now()])); return DB::table('mentor_profiles')->where('user_id',$request->user()->id)->first();
    }

    public function bookMentor(Request $request,int $id)
    {
        abort_unless(DB::table('mentor_profiles')->where(['id'=>$id,'is_published'=>true])->exists(),404); $d=$request->validate(['scheduled_at'=>['required','date','after:now'],'duration_minutes'=>['nullable','integer','min:15','max:180'],'goals'=>['nullable','string','max:3000']]);
        $booking=DB::table('mentor_bookings')->insertGetId($d+['mentor_profile_id'=>$id,'user_id'=>$request->user()->id,'duration_minutes'=>$d['duration_minutes']??45,'status'=>'requested','created_at'=>now(),'updated_at'=>now()]); return response()->json(['id'=>$booking,'message'=>'Mentorship request submitted.'],201);
    }

    public function mentorBookings(Request $request)
    {
        $q=DB::table('mentor_bookings')->join('mentor_profiles','mentor_profiles.id','=','mentor_bookings.mentor_profile_id')->join('users as mentors','mentors.id','=','mentor_profiles.user_id')->join('users as learners','learners.id','=','mentor_bookings.user_id');
        if($request->user()->hasRole('admin')){} elseif($request->user()->hasRole('teacher')) $q->where('mentor_profiles.user_id',$request->user()->id); else $q->where('mentor_bookings.user_id',$request->user()->id);
        return $q->latest('mentor_bookings.scheduled_at')->get(['mentor_bookings.*','mentors.name as mentor_name','learners.name as learner_name']);
    }

    public function serviceRequests(Request $request)
    {
        $q=DB::table('service_requests')->join('users','users.id','=','service_requests.user_id'); if(!$request->user()->hasRole('admin'))$q->where('user_id',$request->user()->id);
        return $q->latest('service_requests.created_at')->get(['service_requests.*','users.name','users.email']);
    }

    public function storeServiceRequest(Request $request)
    {
        $d=$request->validate(['category'=>['required','string','max:80'],'title'=>['required','string','max:255'],'description'=>['required','string','max:5000'],'budget_lyd'=>['nullable','integer','min:0'],'deadline'=>['nullable','date']]);
        $id=DB::table('service_requests')->insertGetId($d+['user_id'=>$request->user()->id,'status'=>'new','created_at'=>now(),'updated_at'=>now()]); return response()->json(['id'=>$id,'message'=>'Service request submitted.'],201);
    }

    public function updateServiceRequest(Request $request,int $id)
    {
        $this->admin($request); $d=$request->validate(['status'=>['required',Rule::in(['new','reviewing','quoted','accepted','in_progress','completed','rejected'])],'management_note'=>['nullable','string','max:3000']]); abort_unless(DB::table('service_requests')->where('id',$id)->update($d+['updated_at'=>now()]),404); return ['message'=>'Service request updated.'];
    }

    public function saveLessonNote(Request $request,int $lesson)
    {
        abort_unless(DB::table('lessons')->where('id',$lesson)->exists(),404); $d=$request->validate(['body'=>['required','string','max:10000'],'timestamp_seconds'=>['nullable','integer','min:0'],'bookmarked'=>['boolean']]);
        DB::table('lesson_notes')->updateOrInsert(['lesson_id'=>$lesson,'user_id'=>$request->user()->id],$d+['created_at'=>now(),'updated_at'=>now()]); return ['message'=>'Note saved.'];
    }

    public function lessonNote(Request $request,int $lesson){return DB::table('lesson_notes')->where(['lesson_id'=>$lesson,'user_id'=>$request->user()->id])->first();}

    public function storeMaterial(Request $request,int $course)
    {
        $this->staff($request); $d=$request->validate(['title'=>['required','string','max:255'],'lesson_id'=>['nullable','exists:lessons,id'],'file'=>['required','file','max:20480']]); $file=$d['file']; $path=$file->store('course-materials/'.$course,'local');
        $id=DB::table('course_materials')->insertGetId(['course_id'=>$course,'lesson_id'=>$d['lesson_id']??null,'uploaded_by'=>$request->user()->id,'title'=>$d['title'],'file_path'=>$path,'original_name'=>$file->getClientOriginalName(),'mime_type'=>$file->getMimeType(),'file_size'=>$file->getSize(),'created_at'=>now(),'updated_at'=>now()]); return response()->json(['id'=>$id],201);
    }

    public function materials(Request $request,int $course){return DB::table('course_materials')->where('course_id',$course)->get(['id','course_id','lesson_id','title','original_name','mime_type','file_size','created_at']);}
    public function downloadMaterial(Request $request,int $id){$m=DB::table('course_materials')->find($id);abort_unless($m&&Storage::disk('local')->exists($m->file_path),404);return Storage::disk('local')->download($m->file_path,$m->original_name);}

    public function supportTickets(Request $request){$q=DB::table('support_tickets');if(!$request->user()->hasRole('admin'))$q->where('user_id',$request->user()->id);return $q->latest()->paginate(20);}
    public function storeSupportTicket(Request $request){$d=$request->validate(['subject'=>['required','string','max:255'],'category'=>['required','string','max:50'],'message'=>['required','string','max:5000'],'priority'=>['nullable',Rule::in(['low','normal','high'])]]);$id=DB::table('support_tickets')->insertGetId($d+['user_id'=>$request->user()->id,'priority'=>$d['priority']??'normal','status'=>'open','created_at'=>now(),'updated_at'=>now()]);return response()->json(['id'=>$id,'message'=>'Support request received.'],201);}

    public function verifyCertificate(string $code){$item=DB::table('certificates')->join('users','users.id','=','certificates.user_id')->join('courses','courses.id','=','certificates.course_id')->where('certificates.code',$code)->first(['certificates.code','certificates.issued_at','users.name as learner','courses.title as course']);abort_unless($item,404);return $item;}

    public function news(Request $request){return DB::table('news_articles')->join('users','users.id','=','news_articles.author_id')->where('is_published',true)->when($request->q,fn($q,$v)=>$q->where(fn($x)=>$x->where('title','like','%'.$v.'%')->orWhere('body','like','%'.$v.'%')))->latest('published_at')->paginate(12,['news_articles.*','users.name as author']);}
    public function article(string $slug){$item=DB::table('news_articles')->join('users','users.id','=','news_articles.author_id')->where(['slug'=>$slug,'is_published'=>true])->first(['news_articles.*','users.name as author']);abort_unless($item,404);return $item;}
    public function adminNews(Request $request){$this->admin($request);return DB::table('news_articles')->latest()->paginate(30);}
    public function storeNews(Request $request){$this->admin($request);$d=$this->newsData($request);$d['slug']=Str::slug($d['title']).'-'.Str::lower(Str::random(5));$d['author_id']=$request->user()->id;$d['published_at']=($d['is_published']??false)?now():null;$id=DB::table('news_articles')->insertGetId($d+['created_at'=>now(),'updated_at'=>now()]);return response()->json(DB::table('news_articles')->find($id),201);}
    public function updateNews(Request $request,int $id){$this->admin($request);abort_unless(DB::table('news_articles')->where('id',$id)->exists(),404);$d=$this->newsData($request,true);if(array_key_exists('is_published',$d))$d['published_at']=$d['is_published']?now():null;DB::table('news_articles')->where('id',$id)->update($d+['updated_at'=>now()]);return DB::table('news_articles')->find($id);}
    public function destroyNews(Request $request,int $id){$this->admin($request);abort_unless(DB::table('news_articles')->where('id',$id)->delete(),404);return response()->noContent();}

    public function reportSummary(Request $request)
    {
        $this->admin($request); return ['learners'=>DB::table('users')->where('role','student')->count(),'instructors'=>DB::table('users')->where('role','teacher')->count(),'courses'=>DB::table('courses')->count(),'active_courses'=>DB::table('courses')->where('is_published',true)->count(),'enrollments'=>DB::table('course_enrollments')->count(),'certificates'=>DB::table('certificates')->count(),'projects_completed'=>DB::table('project_applications')->where('status','completed')->count(),'opportunity_connections'=>DB::table('opportunity_applications')->whereIn('status',['shortlisted','accepted'])->count(),'revenue_lyd'=>DB::table('financial_transactions')->where('type','income')->sum('amount_lyd'),'expenses_lyd'=>DB::table('financial_transactions')->where('type','expense')->sum('amount_lyd')];
    }

    public function learnerOverview(Request $request)
    {
        $id=$request->user()->id;
        return ['profile'=>$request->user()->only(['id','name','email','role','created_at']),
            'enrollments'=>DB::table('course_enrollments')->join('courses','courses.id','=','course_enrollments.course_id')->where('user_id',$id)->latest('course_enrollments.updated_at')->get(['course_enrollments.*','courses.title','courses.course_code','courses.difficulty']),
            'registrations'=>DB::table('course_registrations')->join('courses','courses.id','=','course_registrations.course_id')->where('user_id',$id)->latest('course_registrations.created_at')->get(['course_registrations.*','courses.title']),
            'certificates'=>DB::table('certificates')->join('courses','courses.id','=','certificates.course_id')->where('user_id',$id)->latest('issued_at')->get(['certificates.*','courses.title']),
            'activity'=>DB::table('activity_log')->where('user_id',$id)->latest()->limit(20)->get(),
            'projects'=>DB::table('project_applications')->join('projects','projects.id','=','project_applications.project_id')->where('user_id',$id)->latest('project_applications.updated_at')->get(['project_applications.*','projects.title']),
            'opportunities'=>DB::table('opportunity_applications')->join('opportunities','opportunities.id','=','opportunity_applications.opportunity_id')->where('user_id',$id)->latest('opportunity_applications.updated_at')->get(['opportunity_applications.*','opportunities.title','opportunities.organization']),
            'mentorship'=>DB::table('mentor_bookings')->join('mentor_profiles','mentor_profiles.id','=','mentor_bookings.mentor_profile_id')->join('users as mentors','mentors.id','=','mentor_profiles.user_id')->where('mentor_bookings.user_id',$id)->latest('scheduled_at')->get(['mentor_bookings.*','mentors.name as mentor_name'])];
    }

    public function subscriptions(Request $request){$this->admin($request);return DB::table('billing_interests')->join('users','users.id','=','billing_interests.user_id')->join('billing_plans','billing_plans.id','=','billing_interests.billing_plan_id')->latest('billing_interests.created_at')->paginate(30,['billing_interests.*','users.name','users.email','billing_plans.name as plan_name','billing_plans.price_cents','billing_plans.currency']);}
    public function updateSubscription(Request $request,int $id){$this->admin($request);$d=$request->validate(['status'=>['required',Rule::in(['pending_provider','active','paused','cancelled'])]]);abort_unless(DB::table('billing_interests')->where('id',$id)->update($d+['updated_at'=>now()]),404);return ['message'=>'Subscription updated.'];}
    public function broadcastNotification(Request $request){$this->admin($request);$d=$request->validate(['title'=>['required','string','max:255'],'message'=>['required','string','max:3000'],'user_id'=>['nullable','exists:users,id'],'audience'=>['nullable',Rule::in(['all','students','teachers'])]]);$users=DB::table('users')->when($d['user_id']??null,fn($q,$id)=>$q->where('id',$id))->when(!($d['user_id']??null)&&($d['audience']??'all')==='students',fn($q)=>$q->where('role','student'))->when(!($d['user_id']??null)&&($d['audience']??'all')==='teachers',fn($q)=>$q->where('role','teacher'))->pluck('id');$now=now();foreach($users as $uid)DB::table('notifications')->insert(['id'=>(string)Str::uuid(),'type'=>'App\\Notifications\\PlatformAnnouncement','notifiable_type'=>'App\\Models\\User','notifiable_id'=>$uid,'data'=>json_encode(['title'=>$d['title'],'message'=>$d['message']]),'created_at'=>$now,'updated_at'=>$now]);return ['message'=>'Notification sent.','recipients'=>$users->count()];}
    public function deleteUser(Request $request,int $id){$this->admin($request);abort_if($request->user()->id===$id,422,'You cannot delete your own account.');abort_unless(DB::table('users')->where('id',$id)->delete(),404);return response()->noContent();}

    private function staff(Request $request):void{abort_unless($request->user()->hasRole('admin','teacher'),403);}
    private function admin(Request $request):void{abort_unless($request->user()->hasRole('admin'),403);}
    private function newsData(Request $request,bool $partial=false):array{return $request->validate(['title'=>[$partial?'sometimes':'required','string','max:255'],'excerpt'=>['nullable','string','max:500'],'body'=>[$partial?'sometimes':'required','string'],'category'=>['nullable','string','max:80'],'cover_image'=>['nullable','string','max:500'],'language'=>['nullable',Rule::in(['ar','en','both'])],'is_featured'=>['boolean'],'is_published'=>['boolean']]);}
}
