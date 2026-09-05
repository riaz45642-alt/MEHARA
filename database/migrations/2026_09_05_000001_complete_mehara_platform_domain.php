<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['thumbnail_path','language','status','requirements'] as $column) if (!Schema::hasColumn('courses',$column)) Schema::table('courses', function (Blueprint $table) use ($column) {
            match ($column) { 'thumbnail_path' => $table->string($column)->nullable(), 'language' => $table->string($column,10)->default('ar'), 'status' => $table->string($column,20)->default('draft')->index(), default => $table->text($column)->nullable() };
        });
        if (!Schema::hasColumn('lessons','video_url')) Schema::table('lessons', function (Blueprint $table) { $table->string('video_url')->nullable(); });
        Schema::table('calendar_events', function (Blueprint $table) {
            if (!Schema::hasColumn('calendar_events','course_id')) $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            if (!Schema::hasColumn('calendar_events','meeting_url')) $table->string('meeting_url')->nullable();
            if (!Schema::hasColumn('calendar_events','duration_minutes')) $table->unsignedInteger('duration_minutes')->nullable();
            if (!Schema::hasColumn('calendar_events','status')) $table->string('status', 20)->default('scheduled')->index();
        });

        Schema::create('course_materials', function (Blueprint $table) {
            $table->id(); $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('title'); $table->string('file_path'); $table->string('original_name');
            $table->string('mime_type', 100)->nullable(); $table->unsignedBigInteger('file_size')->default(0); $table->timestamps();
        });
        Schema::create('lesson_notes', function (Blueprint $table) {
            $table->id(); $table->foreignId('lesson_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body'); $table->unsignedInteger('timestamp_seconds')->nullable(); $table->boolean('bookmarked')->default(false); $table->timestamps();
            $table->unique(['lesson_id','user_id']);
        });
        Schema::create('projects', function (Blueprint $table) {
            $table->id(); $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title'); $table->text('description'); $table->string('category', 80); $table->json('skills')->nullable();
            $table->string('difficulty', 20)->default('beginner'); $table->timestamp('deadline')->nullable(); $table->string('status', 20)->default('open')->index();
            $table->unsignedInteger('max_applicants')->nullable(); $table->timestamps();
        });
        Schema::create('project_applications', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('cover_note')->nullable(); $table->string('submission_url')->nullable(); $table->string('submission_path')->nullable();
            $table->string('status', 20)->default('applied')->index(); $table->unsignedTinyInteger('score')->nullable(); $table->text('feedback')->nullable(); $table->timestamps();
            $table->unique(['project_id','user_id']);
        });
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id(); $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->string('title'); $table->string('organization');
            $table->text('description'); $table->string('type', 30); $table->string('category', 80); $table->json('skills')->nullable();
            $table->string('location')->nullable(); $table->boolean('is_remote')->default(false); $table->unsignedInteger('budget_min_lyd')->nullable();
            $table->unsignedInteger('budget_max_lyd')->nullable(); $table->timestamp('deadline')->nullable(); $table->string('status', 20)->default('open')->index(); $table->timestamps();
        });
        Schema::create('opportunity_applications', function (Blueprint $table) {
            $table->id(); $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('cover_note')->nullable(); $table->string('portfolio_url')->nullable(); $table->string('status', 20)->default('submitted')->index(); $table->timestamps();
            $table->unique(['opportunity_id','user_id']);
        });
        Schema::create('mentor_profiles', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); $table->string('headline'); $table->text('bio')->nullable();
            $table->json('expertise')->nullable(); $table->unsignedInteger('experience_years')->default(0); $table->unsignedInteger('session_price_lyd')->default(0);
            $table->string('availability')->default('available'); $table->decimal('rating',3,2)->default(0); $table->boolean('is_published')->default(false); $table->timestamps();
        });
        Schema::create('mentor_bookings', function (Blueprint $table) {
            $table->id(); $table->foreignId('mentor_profile_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('scheduled_at'); $table->unsignedInteger('duration_minutes')->default(45); $table->text('goals')->nullable(); $table->string('meeting_url')->nullable();
            $table->string('status', 20)->default('requested')->index(); $table->timestamps();
        });
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('category', 80); $table->string('title');
            $table->text('description'); $table->unsignedInteger('budget_lyd')->nullable(); $table->timestamp('deadline')->nullable(); $table->string('status', 20)->default('new')->index();
            $table->text('management_note')->nullable(); $table->timestamps();
        });
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('subject'); $table->string('category', 50)->default('general');
            $table->text('message'); $table->string('priority', 20)->default('normal'); $table->string('status', 20)->default('open')->index();
            $table->text('resolution')->nullable(); $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
        });
        Schema::create('organizations', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('type', 40); $table->string('contact_email')->nullable(); $table->string('phone')->nullable();
            $table->string('city')->nullable(); $table->text('notes')->nullable(); $table->string('status',20)->default('active'); $table->timestamps();
        });
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id(); $table->string('type', 20); $table->string('category', 80); $table->unsignedInteger('amount_lyd'); $table->text('description')->nullable();
            $table->foreignId('course_registration_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->date('transaction_date'); $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['financial_transactions','organizations','support_tickets','service_requests','mentor_bookings','mentor_profiles','opportunity_applications','opportunities','project_applications','projects','lesson_notes','course_materials'] as $table) Schema::dropIfExists($table);
        Schema::table('calendar_events', fn (Blueprint $table) => $table->dropConstrainedForeignId('course_id'));
        Schema::table('calendar_events', fn (Blueprint $table) => $table->dropColumn(['meeting_url','duration_minutes','status']));
        Schema::table('lessons', fn (Blueprint $table) => $table->dropColumn('video_url'));
        Schema::table('courses', fn (Blueprint $table) => $table->dropColumn(['thumbnail_path','language','status','requirements']));
    }
};
