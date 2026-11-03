<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';

const props = defineProps({ settings: { type: Object, required: true } });
const form = useForm({ ...props.settings });
const fields = [
    ['name', 'Your name'], ['company', 'Business name'], ['headline', 'Headline', 'The big line at the top of the home page.'],
    ['availability', 'Availability badge', 'Leave empty to hide it.'], ['location', 'Location'], ['email', 'Contact email'],
    ['upwork_url', 'Upwork profile URL'], ['fiverr_url', 'Fiverr profile URL'], ['linkedin_url', 'LinkedIn URL'], ['github_url', 'GitHub URL'], ['website_url', 'Business website'],
];
</script>

<template>
    <Head title="Profile" />
    <AdminLayout title="Profile">
        <form class="max-w-3xl space-y-5 rounded-xl bg-white p-6 ring-1 ring-line" @submit.prevent="form.put(route('admin.settings.update'), { preserveScroll: true })">
            <div class="grid gap-5 sm:grid-cols-2">
                <div v-for="[key, label, hint] in fields" :key="key" :class="key === 'headline' && 'sm:col-span-2'">
                    <label class="field-label" :for="key">{{ label }}</label>
                    <input :id="key" v-model="form[key]" class="input" />
                    <p v-if="hint" class="field-hint">{{ hint }}</p>
                    <p v-if="form.errors[key]" class="mt-1 text-sm text-red-700">{{ form.errors[key] }}</p>
                </div>
            </div>
            <div>
                <label class="field-label" for="intro">Intro</label>
                <textarea id="intro" v-model="form.intro" rows="3" class="input" />
            </div>
            <div>
                <label class="field-label" for="about">About (optional)</label>
                <textarea id="about" v-model="form.about" rows="6" class="input" />
                <p class="field-hint">Shown under “How I work”. Leave a blank line between paragraphs.</p>
            </div>
            <button class="btn-primary" :disabled="form.processing">Save profile</button>
        </form>
    </AdminLayout>
</template>
