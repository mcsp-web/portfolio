<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * AskDoc becomes "AskDoc RAG", the big brother of AskDoc Lite. Each field changes only if it
 * still holds the original seeded text, so edits made in the admin are kept.
 */
return new class extends Migration
{
    private const OLD_SENTENCE = 'The live demo is paused to save server resources. These screenshots use a sample employee handbook.';

    private const NEW_SENTENCE = 'This is the big brother of AskDoc Lite: built for large document libraries and heavy use, where sending whole files to the AI on every question would be slow and costly. It runs on its own server. These screenshots use a sample employee handbook.';

    public function up(): void
    {
        $askdoc = DB::table('projects')->where('slug', 'askdoc')->first();
        if (! $askdoc) {
            return;
        }

        $changes = [];
        if ($askdoc->title === 'AskDoc') {
            $changes['title'] = 'AskDoc RAG';
        }
        if ($askdoc->tagline === 'Ask questions of any document and get answers with page citations') {
            $changes['tagline'] = 'Document Q&A for large libraries: retrieval, local embeddings, cited answers';
        }
        if (is_string($askdoc->body) && str_contains($askdoc->body, self::OLD_SENTENCE)) {
            $changes['body'] = str_replace(self::OLD_SENTENCE, self::NEW_SENTENCE, $askdoc->body);
        }

        if ($changes) {
            DB::table('projects')->where('id', $askdoc->id)->update($changes + ['updated_at' => now()]);
        }
    }

    public function down(): void
    {
        //
    }
};
