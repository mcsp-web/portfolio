<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 160);
            $table->string('company', 160)->nullable();
            $table->string('project_type', 60)->nullable();
            $table->string('budget', 60)->nullable();
            $table->text('body');
            $table->string('ip', 45)->nullable();
            $table->boolean('emailed')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Fill in the Upwork profile link if it hasn't been set yet.
        $url = 'https://www.upwork.com/freelancers/~01294f831c6e9e058c';
        $existing = DB::table('settings')->where('key', 'upwork_url')->first();
        if (! $existing) {
            DB::table('settings')->insert(['key' => 'upwork_url', 'value' => $url, 'created_at' => now(), 'updated_at' => now()]);
        } elseif (trim((string) $existing->value) === '') {
            DB::table('settings')->where('key', 'upwork_url')->update(['value' => $url, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
