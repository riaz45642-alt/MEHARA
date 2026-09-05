<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeharaCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $owner=User::whereIn('role',['admin','teacher'])->first(); if(!$owner)return;
        DB::table('mentor_profiles')->updateOrInsert(['user_id'=>$owner->id],['headline'=>'Digital skills and applied learning mentor','bio'=>'Supports learners with projects, portfolios and professional growth.','expertise'=>json_encode(['Digital Skills','Project Feedback','Career Readiness']),'experience_years'=>8,'session_price_lyd'=>60,'availability'=>'available','rating'=>4.80,'is_published'=>true,'created_at'=>now(),'updated_at'=>now()]);
        foreach ([
            ['title'=>'Build a responsive organization website','description'=>'Create an accessible bilingual website from a professional brief and submit the deployed result.','category'=>'Development','skills'=>['HTML','CSS','JavaScript'],'difficulty'=>'intermediate'],
            ['title'=>'Create a public-awareness presentation','description'=>'Turn research findings into a clear professional presentation for a local audience.','category'=>'Presentations','skills'=>['Research','Storytelling','Visual Design'],'difficulty'=>'beginner'],
        ] as $p) DB::table('projects')->updateOrInsert(['title'=>$p['title']],array_merge($p,['skills'=>json_encode($p['skills']),'created_by'=>$owner->id,'status'=>'open','deadline'=>now()->addDays(30),'created_at'=>now(),'updated_at'=>now()]));
        foreach ([
            ['title'=>'Junior Frontend Internship','organization'=>'Libyan Digital Studio','description'=>'Join a supervised product team and contribute to accessible web interfaces.','type'=>'internship','category'=>'Development','skills'=>['JavaScript','React','Git'],'location'=>'Tripoli','is_remote'=>false,'budget_min_lyd'=>500,'budget_max_lyd'=>900],
            ['title'=>'Remote Presentation Designer','organization'=>'Independent Client Network','description'=>'Design a concise bilingual deck from an approved content outline.','type'=>'freelance','category'=>'Design','skills'=>['PowerPoint','Typography','Arabic Layout'],'location'=>'Libya','is_remote'=>true,'budget_min_lyd'=>250,'budget_max_lyd'=>450],
        ] as $o) DB::table('opportunities')->updateOrInsert(['title'=>$o['title'],'organization'=>$o['organization']],array_merge($o,['skills'=>json_encode($o['skills']),'created_by'=>$owner->id,'status'=>'open','deadline'=>now()->addDays(21),'created_at'=>now(),'updated_at'=>now()]));
        DB::table('news_articles')->updateOrInsert(['slug'=>'mehara-digital-skills-update'],['author_id'=>$owner->id,'title'=>'مهارات رقمية لمستقبل العمل | Digital skills for the future of work','excerpt'=>'تحديث مِهارة حول المهارات التقنية والعملية الأكثر أهمية للمتعلمين.','body'=>'تجمع مسارات مِهارة بين التعلم المنظم والمشاريع العملية حتى يتمكن المتعلم من بناء دليل واضح على مهاراته والاستعداد لفرص حقيقية.','category'=>'Technology & Learning','cover_image'=>null,'language'=>'both','is_featured'=>true,'is_published'=>true,'published_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
    }
}
