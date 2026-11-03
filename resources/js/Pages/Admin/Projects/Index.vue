<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

defineProps({ projects: { type: Array, required: true } });
const opts = { preserveScroll: true };
const toggle = (p, field) => router.patch(route('admin.projects.toggle', p.id), { field }, opts);
const move = (p, direction) => router.patch(route('admin.projects.move', p.id), { direction }, opts);
</script>

<template>
    <Head title="Projects" />
    <AdminLayout title="Projects">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-muted">Hidden projects stay here but don't appear on the public site. Order here is the order on the site.</p>
            <Link :href="route('admin.projects.create')" class="btn-primary">Add project</Link>
        </div>
        <ul class="divide-y divide-line overflow-hidden rounded-xl bg-white ring-1 ring-line">
            <li v-for="(p, i) in projects" :key="p.id" class="flex flex-wrap items-center gap-4 p-4" :class="!p.isPublished && 'bg-paper/60'">
                <div class="flex flex-col">
                    <button type="button" class="px-1 text-muted hover:text-ink disabled:opacity-30" :disabled="i === 0" :aria-label="`Move ${p.title} up`" @click="move(p, 'up')">▲</button>
                    <button type="button" class="px-1 text-muted hover:text-ink disabled:opacity-30" :disabled="i === projects.length - 1" :aria-label="`Move ${p.title} down`" @click="move(p, 'down')">▼</button>
                </div>
                <img v-if="p.cover" :src="p.cover" alt="" class="h-14 w-24 rounded object-cover object-top ring-1 ring-line" :class="!p.isPublished && 'opacity-50 grayscale'" />
                <div v-else class="h-14 w-24 rounded bg-paper ring-1 ring-line" />
                <div class="min-w-0 flex-1">
                    <Link :href="route('admin.projects.edit', p.id)" class="font-semibold hover:text-teal-ink">{{ p.title }}</Link>
                    <p class="text-xs text-muted">{{ p.categoryLabel }}, {{ p.statusLabel }}. {{ p.screenshotCount }} screenshots, {{ p.techCount }} technologies</p>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" :checked="p.isFeatured" class="rounded border-line" @change="toggle(p, 'is_featured')" /> Featured
                </label>
                <button type="button" class="btn min-w-28 ring-1" :class="p.isPublished ? 'bg-emerald-50 text-emerald-800 ring-emerald-200' : 'bg-white text-muted ring-line'" @click="toggle(p, 'is_published')">
                    {{ p.isPublished ? 'Shown' : 'Hidden' }}
                </button>
                <Link :href="route('admin.projects.edit', p.id)" class="btn-quiet">Edit</Link>
            </li>
            <li v-if="!projects.length" class="p-8 text-center text-muted">No projects yet. Add your first one.</li>
        </ul>
    </AdminLayout>
</template>
