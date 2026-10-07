<script setup>
import { Head } from '@inertiajs/vue3';

// Props come straight from Inertia::render() in routes/web.php — proof the
// server → client data handoff works.
defineProps({
    appName: { type: String, required: true },
    laravelVersion: { type: String, required: true },
    phpVersion: { type: String, required: true },
});
</script>

<template>
    <Head title="Welcome" />

    <main class="grid min-h-screen place-items-center bg-slate-50 p-6 text-slate-900">
        <div class="w-full max-w-xl rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-medium text-indigo-600">{{ appName }}</p>

            <h1 class="mt-2 text-3xl font-semibold tracking-tight">
                Inertia + Vue 3 is wired up
            </h1>

            <p class="mt-3 text-slate-600">
                This page is a Vue single-file component rendered through Inertia.
                If you can read this, the front-end build is working.
            </p>

            <dl class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-lg bg-slate-50 p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Laravel</dt>
                    <dd class="mt-1 font-semibold">{{ laravelVersion }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">PHP</dt>
                    <dd class="mt-1 font-semibold">{{ phpVersion }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Ziggy route()</dt>
                    <dd class="mt-1 font-semibold">{{ route('home') }}</dd>
                </div>
            </dl>
        </div>
    </main>
</template>
