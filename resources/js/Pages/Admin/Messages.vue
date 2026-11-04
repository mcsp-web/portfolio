<script setup>
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';

defineProps({ messages: { type: Array, required: true } });
const opts = { preserveScroll: true };
const toggleRead = (m) => router.patch(route('admin.messages.read', m.id), {}, opts);
const remove = (m) => confirm(`Delete the message from ${m.name}?`) && router.delete(route('admin.messages.destroy', m.id), opts);
</script>

<template>
    <Head title="Messages" />
    <AdminLayout title="Messages">
        <p class="mb-5 max-w-2xl text-sm text-muted">Enquiries from the contact form. Each one is also emailed to your contact email when mail is set up; “Saved only” means the email didn’t go out, but nothing was lost.</p>
        <ul class="space-y-3">
            <li v-for="m in messages" :key="m.id" class="rounded-xl bg-white p-5 ring-1 ring-line" :class="!m.read && 'ring-2 ring-teal/60'">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <p class="font-semibold">
                        <span v-if="!m.read" class="mr-2 rounded bg-teal px-1.5 py-0.5 text-xs font-bold text-ink">New</span>
                        {{ m.name }} <a :href="`mailto:${m.email}`" class="font-normal text-teal-ink underline underline-offset-2">{{ m.email }}</a>
                    </p>
                    <p class="text-xs text-muted">{{ m.received }}<span v-if="!m.emailed" class="ml-2 rounded bg-amber-50 px-1.5 py-0.5 font-semibold text-amber-800 ring-1 ring-amber-200">Saved only</span></p>
                </div>
                <p v-if="m.company || m.projectType || m.budget" class="mt-1 text-sm text-muted">{{ [m.company, m.projectType, m.budget].filter(Boolean).join(', ') }}</p>
                <p class="mt-3 text-[15px] whitespace-pre-wrap">{{ m.body }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a :href="`mailto:${m.email}?subject=${encodeURIComponent('Re: your project enquiry')}`" class="btn-primary px-3 py-1.5">Reply by email</a>
                    <button type="button" class="btn-quiet px-3 py-1.5" @click="toggleRead(m)">{{ m.read ? 'Mark unread' : 'Mark read' }}</button>
                    <button type="button" class="btn-danger px-3 py-1.5" @click="remove(m)">Delete</button>
                </div>
            </li>
            <li v-if="!messages.length" class="rounded-xl bg-white p-8 text-center text-muted ring-1 ring-line">No messages yet. Enquiries from the contact form will show up here.</li>
        </ul>
    </AdminLayout>
</template>
