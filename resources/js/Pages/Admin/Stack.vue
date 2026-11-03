<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminLayout from '../../Layouts/AdminLayout.vue';

const props = defineProps({ layers: { type: Object, required: true }, technologies: { type: Array, required: true } });
const grouped = computed(() => Object.entries(props.layers).map(([key, label]) => ({ key, label, items: props.technologies.filter((t) => t.layer === key) })));
const add = useForm({ name: '', layer: 'backend', show_on_profile: true });
const opts = { preserveScroll: true };
const save = (t) => router.put(route('admin.technologies.update', t.id), { name: t.name, layer: t.layer, show_on_profile: t.show_on_profile }, opts);
const remove = (t) => confirm(`Remove ${t.name}? It will be unlinked from ${t.projects} project(s).`) && router.delete(route('admin.technologies.destroy', t.id), opts);
</script>

<template>
    <Head title="Tech stack" />
    <AdminLayout title="Tech stack">
        <p class="mb-5 max-w-2xl text-sm text-muted">These appear in the Tech stack section of the home page, grouped by layer. Untick “Show” to keep a technology on project pages but out of the profile list.</p>
        <form class="mb-8 flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 ring-1 ring-line" @submit.prevent="add.post(route('admin.technologies.store'), { ...opts, onSuccess: () => add.reset('name') })">
            <div><label class="field-label" for="tn">Technology</label><input id="tn" v-model="add.name" class="input" required /></div>
            <div><label class="field-label" for="tl">Layer</label><select id="tl" v-model="add.layer" class="input"><option v-for="(l, k) in layers" :key="k" :value="k">{{ l }}</option></select></div>
            <button class="btn-primary" :disabled="add.processing">Add</button>
            <p v-if="add.errors.name" class="w-full text-sm text-red-700">{{ add.errors.name }}</p>
        </form>
        <div class="grid gap-6 md:grid-cols-2">
            <section v-for="g in grouped" :key="g.key" class="rounded-xl bg-white p-5 ring-1 ring-line">
                <h2 class="font-semibold">{{ g.label }}</h2>
                <ul class="mt-3 divide-y divide-line">
                    <li v-for="t in g.items" :key="t.id" class="flex flex-wrap items-center gap-2 py-2">
                        <input v-model="t.name" class="input w-36 py-1" :aria-label="`Name of ${t.name}`" @change="save(t)" />
                        <select v-model="t.layer" class="input w-auto py-1" @change="save(t)"><option v-for="(l, k) in layers" :key="k" :value="k">{{ l }}</option></select>
                        <label class="flex items-center gap-1 text-xs"><input v-model="t.show_on_profile" type="checkbox" class="rounded border-line" @change="save(t)" /> Show</label>
                        <span class="text-xs text-muted">{{ t.projects }} proj.</span>
                        <button type="button" class="ml-auto text-xs text-red-700 hover:underline" @click="remove(t)">Remove</button>
                    </li>
                    <li v-if="!g.items.length" class="py-2 text-sm text-muted">None yet.</li>
                </ul>
            </section>
        </div>
    </AdminLayout>
</template>
