<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id(); $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title'); $table->string('slug')->unique(); $table->string('excerpt',500)->nullable(); $table->longText('body');
            $table->string('category',80)->default('Technology'); $table->string('cover_image')->nullable(); $table->string('language',10)->default('both');
            $table->boolean('is_featured')->default(false); $table->boolean('is_published')->default(false)->index(); $table->timestamp('published_at')->nullable(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('news_articles'); }
};
