<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Removes screenshot entries that point at bundled images no longer in the repo
 * (for example the Launchpad preview dropped from the website platform project).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('screenshots')->where('disk', 'bundled')->orderBy('id')->get(['id', 'path'])
            ->reject(fn ($s) => is_file(public_path('images/projects/'.$s->path)))
            ->each(fn ($s) => DB::table('screenshots')->where('id', $s->id)->delete());
    }

    public function down(): void
    {
        //
    }
};
