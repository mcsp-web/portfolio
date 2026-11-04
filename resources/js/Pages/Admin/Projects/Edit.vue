<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

const props = defineProps({
    project: { type: Object, default: null },
    categories: { type: Object, required: true },
    statuses: { type: Object, required: true },
    kinds: { type: Object, required: true },
    layers: { type: Object, required: true },
    technologies: { type: Array, required: true },
});

const p = props.project;
const form = useForm({
    title: p?.title ?? '',
    slug: p?.slug ?? '',
    tagline: p?.tagline ?? '',
    category: p?.category ?? 'saas',
    status: p?.status ?? 'live',
    client_label: p?.client_label ?? '',
    role: p?.role ?? '',
    year: p?.year ?? new Date().getFullYear(),
    summary: p?.summary ?? '',
    body: p?.body ?? '',
    highlights: p?.highlights ?? '',
    engineering: p?.engineering ?? '',
    live_url: p?.live_url ?? '',
    show_live_url: p?.show_live_url ?? false,
    is_published: p?.is_published ?? true,
    is_featured: p?.is_featured ?? false,
    show_feature_list: p?.show_feature_list ?? true,
    stats: p?.stats ?? '',
    technology_ids: p?.technology_ids ?? [],
});

const save = () => (p ? form.put(route('admin.projects.update', p.id), { preserveScroll: true }) : form.post(route('admin.projects.store')));
const destroy = () => {
    if (confirm(`Delete “${p.title}” and all its screenshots? This can't be undone. To just hide it, untick "Show on the portfolio" instead.`)) {
        router.delete(route('admin.projects.destroy', p.id));
    }
};

const techByLayer = computed(() =>
    Object.entries(props.layers).map(([key, label]) => ({ key, label, items: props.technologies.filter((t) => t.layer === key) })).filter((l) => l.items.length),
);

// Screenshots
const upload = useForm({ images: [], kind: 'public' });
const fileInput = ref(null);
const sendUpload = () =>
    upload.post(route('admin.screenshots.store', p.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            upload.reset('images');
            if (fileInput.value) fileInput.value.value = '';
        },
    });
const opts = { preserveScroll: true };
const updateShot = (s, changes) => router.patch(route('admin.screenshots.update', s.id), { caption: s.caption, kind: s.kind, is_cover: s.isCover, ...changes }, opts);
const moveShot = (s, direction) => router.patch(route('admin.screenshots.move', s.id), { direction }, opts);
const removeShot = (s) => confirm('Remove this screenshot?') && router.delete(route('admin.screenshots.destroy', s.id), opts);

// Feature list
const groups = computed(() => {
    const map = new Map();
    (p?.features ?? []).forEach((f) => {
        if (!map.has(f.group_name)) map.set(f.group_name, []);
        map.get(f.group_name).push(f);
    });
    return [...map.entries()].map(([name, items]) => ({ name, items, shown: items.filter((f) => f.is_visible).length }));
});
const featureTotals = computed(() => ({
    total: p?.features?.length ?? 0,
    shown: (p?.features ?? []).filter((f) => f.is_visible).length,
}));
const open = reactive(new Set());
const toggleOpen = (name) => (open.has(name) ? open.delete(name) : open.add(name));
const patchFeature = (f, changes) => router.patch(route('admin.features.update', f.id), changes, opts);
const removeFeature = (f) => confirm(`Remove “${f.name}”?`) && router.delete(route('admin.features.destroy', f.id), opts);
const setGroupVisible = (g, visible) => router.patch(route('admin.features.group', p.id), { group: g.name, is_visible: visible }, opts);
const renameGroup = (g, value) => {
    const name = value.trim();
    if (name && name !== g.name) router.patch(route('admin.features.group', p.id), { group: g.name, rename: name }, opts);
};
const moveGroup = (g, direction) => router.patch(route('admin.features.moveGroup', p.id), { group: g.name, direction }, opts);
const newFeature = useForm({ group_name: '', name: '', detail: '' });
const addFeature = () =>
    newFeature.post(route('admin.features.store', p.id), {
        preserveScroll: true,
        onSuccess: () => {
            open.add(newFeature.group_name);
            newFeature.reset('name', 'detail');
        },
    });
</script>

<template>
    <Head :title="p ? `Edit ${p.title}` : 'New project'" />
    <AdminLayout :title="p ? p.title : 'New project'">
        <div class="mb-6 flex flex-wrap items-center gap-3 text-sm">
            <Link :href="route('admin.projects.index')" class="text-muted hover:text-ink">← All projects</Link>
            <a v-if="p && p.is_published" :href="route('projects.show', p.slug)" target="_blank" class="text-teal-ink underline underline-offset-4">Open public page</a>
        </div>

        <form class="grid gap-8 lg:grid-cols-[1fr_320px]" @submit.prevent="save">
            <div class="space-y-6 rounded-xl bg-white p-6 ring-1 ring-line">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="field-label" for="title">Project name</label>
                        <input id="title" v-model="form.title" class="input" required />
                        <p v-if="form.errors.title" class="mt-1 text-sm text-red-700">{{ form.errors.title }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label" for="tagline">One-line description</label>
                        <input id="tagline" v-model="form.tagline" class="input" />
                    </div>
                    <div>
                        <label class="field-label" for="category">Category</label>
                        <select id="category" v-model="form.category" class="input"><option v-for="(l, k) in categories" :key="k" :value="k">{{ l }}</option></select>
                    </div>
                    <div>
                        <label class="field-label" for="status">Status</label>
                        <select id="status" v-model="form.status" class="input"><option v-for="(l, k) in statuses" :key="k" :value="k">{{ l }}</option></select>
                    </div>
                    <div>
                        <label class="field-label" for="client">Client (as shown publicly)</label>
                        <input id="client" v-model="form.client_label" class="input" placeholder="e.g. Mobile detailing business, Miami" />
                        <p class="field-hint">Only name a client if they've agreed to it.</p>
                    </div>
                    <div>
                        <label class="field-label" for="role">My role</label>
                        <input id="role" v-model="form.role" class="input" placeholder="Design, full-stack build, hosting" />
                    </div>
                    <div>
                        <label class="field-label" for="year">Year</label>
                        <input id="year" v-model="form.year" type="number" class="input" />
                    </div>
                    <div>
                        <label class="field-label" for="slug">Web address</label>
                        <input id="slug" v-model="form.slug" class="input" placeholder="made from the name" />
                        <p class="field-hint">/work/{{ form.slug || 'project-name' }}</p>
                    </div>
                </div>
                <div>
                    <label class="field-label" for="summary">Summary</label>
                    <textarea id="summary" v-model="form.summary" rows="3" class="input" />
                    <p class="field-hint">Shown on the home page and at the top of the project page.</p>
                </div>
                <div>
                    <label class="field-label" for="body">Longer description</label>
                    <textarea id="body" v-model="form.body" rows="5" class="input" />
                    <p class="field-hint">Leave a blank line between paragraphs.</p>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="field-label" for="highlights">What it does</label>
                        <textarea id="highlights" v-model="form.highlights" rows="8" class="input" />
                        <p class="field-hint">One feature per line.</p>
                    </div>
                    <div>
                        <label class="field-label" for="engineering">Under the hood</label>
                        <textarea id="engineering" v-model="form.engineering" rows="8" class="input" />
                        <p class="field-hint">One engineering note per line.</p>
                    </div>
                </div>
                <div>
                    <label class="field-label" for="stats">Headline numbers</label>
                    <textarea id="stats" v-model="form.stats" rows="3" class="input" placeholder="1,300+ | automated tests" />
                    <p class="field-hint">One per line as “number | label”. Shown at the top of the project page next to the feature count.</p>
                </div>
                <div class="grid gap-5 sm:grid-cols-[1fr_auto] sm:items-end">
                    <div>
                        <label class="field-label" for="live">Live URL</label>
                        <input id="live" v-model="form.live_url" type="url" class="input" placeholder="https://" />
                        <p v-if="form.errors.live_url" class="mt-1 text-sm text-red-700">{{ form.errors.live_url }}</p>
                    </div>
                    <label class="flex items-center gap-2 pb-2 text-sm"><input v-model="form.show_live_url" type="checkbox" class="rounded border-line" /> Link it publicly</label>
                </div>
            </div>

            <div class="space-y-6">
                <div class="space-y-3 rounded-xl bg-white p-5 ring-1 ring-line">
                    <label class="flex items-center gap-2 text-sm font-medium"><input v-model="form.is_published" type="checkbox" class="rounded border-line" /> Show on the portfolio</label>
                    <label class="flex items-center gap-2 text-sm font-medium"><input v-model="form.is_featured" type="checkbox" class="rounded border-line" /> Feature at the top</label>
                    <label class="flex items-center gap-2 text-sm font-medium"><input v-model="form.show_feature_list" type="checkbox" class="rounded border-line" /> Show full feature list</label>
                    <button type="submit" class="btn-primary w-full" :disabled="form.processing">{{ p ? 'Save changes' : 'Create project' }}</button>
                    <button v-if="p" type="button" class="btn-danger w-full" @click="destroy">Delete project</button>
                </div>
                <fieldset class="rounded-xl bg-white p-5 ring-1 ring-line">
                    <legend class="px-1 text-sm font-semibold">Technologies used</legend>
                    <div v-for="layer in techByLayer" :key="layer.key" class="mt-3">
                        <p class="text-xs font-semibold text-teal-ink">{{ layer.label }}</p>
                        <div class="mt-1 flex flex-wrap gap-1.5">
                            <label v-for="t in layer.items" :key="t.id" class="cursor-pointer rounded-md px-2 py-1 text-xs ring-1"
                                   :class="form.technology_ids.includes(t.id) ? 'bg-ink text-white ring-ink' : 'bg-white ring-line'">
                                <input v-model="form.technology_ids" type="checkbox" :value="t.id" class="sr-only" />{{ t.name }}
                            </label>
                        </div>
                    </div>
                    <Link :href="route('admin.technologies.index')" class="mt-4 inline-block text-xs text-teal-ink underline">Add or rename technologies</Link>
                </fieldset>
            </div>
        </form>

        <!-- Feature list -->
        <section v-if="p" class="mt-10 rounded-xl bg-white p-6 ring-1 ring-line">
            <div class="flex flex-wrap items-baseline justify-between gap-3">
                <h2 class="display text-xl font-bold">Full feature list</h2>
                <p class="text-sm text-muted">
                    {{ featureTotals.shown }} of {{ featureTotals.total }} features shown
                    <span v-if="!form.show_feature_list" class="ml-2 rounded bg-amber-50 px-2 py-0.5 font-semibold text-amber-800 ring-1 ring-amber-200">Whole list hidden</span>
                </p>
            </div>
            <p class="mt-1 text-sm text-muted">Untick a feature to hide it, or hide a whole group. Hidden items stay here so you can bring them back. Click a group name to open it.</p>

            <ul class="mt-5 space-y-3">
                <li v-for="(g, gi) in groups" :key="g.name" class="rounded-lg ring-1 ring-line" :class="g.shown === 0 && 'bg-paper'">
                    <div class="flex flex-wrap items-center gap-2 px-3 py-2">
                        <button type="button" class="text-muted" :aria-expanded="open.has(g.name)" :aria-label="`Open ${g.name}`" @click="toggleOpen(g.name)">{{ open.has(g.name) ? '▾' : '▸' }}</button>
                        <input :value="g.name" class="input w-auto min-w-48 flex-1 border-transparent py-1 font-semibold shadow-none hover:border-line" :aria-label="`Group name for ${g.name}`" @change="renameGroup(g, $event.target.value)" />
                        <span class="text-xs text-muted">{{ g.shown }}/{{ g.items.length }} shown</span>
                        <button type="button" class="btn-quiet px-2 py-1 text-xs" @click="setGroupVisible(g, g.shown === 0)">{{ g.shown === 0 ? 'Show group' : 'Hide group' }}</button>
                        <button type="button" class="btn-quiet px-2 py-1 text-xs" :disabled="gi === 0" aria-label="Move group up" @click="moveGroup(g, 'up')">▲</button>
                        <button type="button" class="btn-quiet px-2 py-1 text-xs" :disabled="gi === groups.length - 1" aria-label="Move group down" @click="moveGroup(g, 'down')">▼</button>
                    </div>
                    <ul v-if="open.has(g.name)" class="divide-y divide-line border-t border-line">
                        <li v-for="f in g.items" :key="f.id" class="grid items-start gap-2 px-3 py-2 sm:grid-cols-[auto_1fr_1.3fr_auto]" :class="!f.is_visible && 'opacity-60'">
                            <input type="checkbox" class="mt-2 rounded border-line" :checked="f.is_visible" :aria-label="`Show ${f.name}`" @change="patchFeature(f, { is_visible: $event.target.checked })" />
                            <input :value="f.name" class="input py-1" aria-label="Feature" @change="patchFeature(f, { name: $event.target.value })" />
                            <input :value="f.detail" class="input py-1" placeholder="Optional detail" aria-label="Detail" @change="patchFeature(f, { detail: $event.target.value })" />
                            <button type="button" class="mt-1.5 text-xs text-red-700 hover:underline" @click="removeFeature(f)">Remove</button>
                        </li>
                    </ul>
                </li>
                <li v-if="!groups.length" class="rounded-lg bg-paper p-4 text-sm text-muted">No features yet. Add the first one below.</li>
            </ul>

            <form class="mt-5 grid gap-3 rounded-lg bg-paper p-4 sm:grid-cols-[1fr_1.4fr_1.4fr_auto] sm:items-end" @submit.prevent="addFeature">
                <div>
                    <label class="field-label" for="fg">Group</label>
                    <input id="fg" v-model="newFeature.group_name" list="feature-groups" class="input" required />
                    <datalist id="feature-groups"><option v-for="g in groups" :key="g.name" :value="g.name" /></datalist>
                </div>
                <div><label class="field-label" for="fn">Feature</label><input id="fn" v-model="newFeature.name" class="input" required /></div>
                <div><label class="field-label" for="fd">Detail (optional)</label><input id="fd" v-model="newFeature.detail" class="input" /></div>
                <button class="btn-primary" :disabled="newFeature.processing">Add feature</button>
            </form>
        </section>

        <!-- Screenshots -->
        <section v-if="p" class="mt-10 rounded-xl bg-white p-6 ring-1 ring-line">
            <h2 class="display text-xl font-bold">Screenshots</h2>
            <p class="mt-1 text-sm text-muted">Check every image for real names, emails, phone numbers or keys before uploading. The cover is the image used on the home page.</p>
            <form class="mt-5 flex flex-wrap items-end gap-3 rounded-lg bg-paper p-4" @submit.prevent="sendUpload">
                <div>
                    <label class="field-label" for="files">Images (PNG, JPG or WebP, up to 6 MB each)</label>
                    <input id="files" ref="fileInput" type="file" multiple accept="image/png,image/jpeg,image/webp" class="text-sm" @input="upload.images = [...$event.target.files]" />
                </div>
                <div>
                    <label class="field-label" for="kind">They show</label>
                    <select id="kind" v-model="upload.kind" class="input"><option v-for="(l, k) in kinds" :key="k" :value="k">{{ l }}</option></select>
                </div>
                <button type="submit" class="btn-primary" :disabled="!upload.images.length || upload.processing">Upload</button>
                <p v-if="Object.keys(upload.errors).length" class="w-full text-sm text-red-700">{{ Object.values(upload.errors)[0] }}</p>
            </form>
            <ul class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <li v-for="(s, i) in p.screenshots" :key="s.id" class="overflow-hidden rounded-lg ring-1 ring-line">
                    <div class="relative bg-paper">
                        <img :src="s.url" alt="" class="h-44 w-full object-cover object-top" />
                        <span v-if="s.isCover" class="absolute top-2 left-2 rounded bg-teal px-2 py-0.5 text-xs font-bold text-ink">Cover</span>
                    </div>
                    <div class="space-y-2 p-3">
                        <input v-model="s.caption" class="input" placeholder="Caption" @change="updateShot(s, {})" />
                        <div class="flex flex-wrap items-center gap-2">
                            <select v-model="s.kind" class="input w-auto py-1" @change="updateShot(s, {})"><option v-for="(l, k) in kinds" :key="k" :value="k">{{ l }}</option></select>
                            <button v-if="!s.isCover" type="button" class="text-xs text-teal-ink underline" @click="updateShot(s, { is_cover: true })">Make cover</button>
                            <span class="ml-auto flex gap-1">
                                <button type="button" class="btn-quiet px-2 py-1" :disabled="i === 0" aria-label="Move earlier" @click="moveShot(s, 'up')">◀</button>
                                <button type="button" class="btn-quiet px-2 py-1" :disabled="i === p.screenshots.length - 1" aria-label="Move later" @click="moveShot(s, 'down')">▶</button>
                                <button type="button" class="btn-danger px-2 py-1" @click="removeShot(s)">Remove</button>
                            </span>
                        </div>
                    </div>
                </li>
            </ul>
        </section>
    </AdminLayout>
</template>
