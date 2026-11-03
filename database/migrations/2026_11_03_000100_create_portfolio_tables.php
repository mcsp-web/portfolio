<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technologies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('layer', 32)->index();
            $table->boolean('show_on_profile')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('tagline')->nullable();
            $table->string('category', 32)->index();
            $table->string('status', 32)->default('live');
            $table->string('client_label')->nullable();
            $table->string('role')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->text('summary')->nullable();
            $table->text('body')->nullable();
            $table->json('highlights')->nullable();
            $table->json('engineering')->nullable();
            $table->string('live_url')->nullable();
            $table->boolean('show_live_url')->default(false);
            $table->boolean('is_published')->default(true)->index();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_technology', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->constrained()->cascadeOnDelete();
            $table->primary(['project_id', 'technology_id']);
        });

        Schema::create('screenshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('disk', 32)->default('bundled');
            $table->string('path');
            $table->string('caption')->nullable();
            $table->string('kind', 16)->default('public');
            $table->boolean('is_cover')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('screenshots');
        Schema::dropIfExists('project_technology');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('technologies');
    }
};
