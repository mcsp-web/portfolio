<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import PublicLayout from '../Layouts/PublicLayout.vue';

const props = defineProps({
    profile: { type: Object, required: true },
    project: { type: Object, required: true },
    next: { type: Object, default: null },
});

const kinds = computed(() => {
    const seen = new Map();
    props.project.screenshots.forEach((s) => seen.set(s.kind, s.kindLabel));
    return [...seen.entries()].map(([key, label]) => ({ key, label }));
});
const kind = ref('all');
const shots = computed(() => (kind.value === 'all' ? props.project.screenshots : props.project.screenshots.filter((s) => s.kind === kind.value)));
const active = ref(0);
watch(kind, () => (active.value = 0));
const current = computed(() => shots.value[active.value] ?? null);

// Lightbox
const zoom = ref(false);
const step = (d) => (active.value = (active.value + d + shots.value.length) % shots.value.length);
const onKey = (e) => {
    if (!zoom.value) return;
    if (e.key === 'Escape') zoom.value = false;
    if (e.key === 'ArrowRight') step(1);
    if (e.key === 'ArrowLeft') step(-1);
};
watch(zoom, (z) => {
    document.body.style.overflow = z ? 'hidden' : '';
    z ? window.addEventListener('keydown', onKey) : window.removeEventListener('keydown', onKey);
});
onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKey);
    document.body.style.overflow = '';
});

const meta = computed(() =>
    [
        ['Client', props.project.clientLabel],
        ['My role', props.project.role],
        ['Year', props.project.year],
        ['Status', props.project.statusLabel],
    ].filter(([, v]) => v),
);
</script>

<template>
    <Head>
        <title>{{ project.title }}</title>
        <meta name="description" :content="project.summary" />
    </Head>

    <PublicLayout :profile="profile">
        <article>
            <header class="mx-auto max-w-6xl px-5 pt-10 sm:px-8">
                <Link :href="route('home') + '#work'" class="text-sm font-medium text-muted hover:text-ink">← All work</Link>
                <p class="mt-8 text-sm text-muted">{{ project.categoryLabel }}</p>
                <h1 class="display mt-2 text-5xl leading-none font-bold sm:text-7xl">{{ project.title }}</h1>
                <p class="mt-4 max-w-3xl text-xl text-ink/80 sm:text-2xl">{{ project.tagline }}</p>
                <dl class="mt-10 grid grid-cols-2 gap-6 border-t border-line pt-6 sm:grid-cols-4 lg:grid-cols-5">
                    <div v-for="[label, value] in meta" :key="label">
                        <dt class="text-xs text-muted">{{ label }}</dt>
                        <dd class="mt-1 font-semibold">{{ value }}</dd>
                    </div>
                    <div v-if="project.liveUrl">
                        <dt class="text-xs text-muted">Live site</dt>
                        <dd class="mt-1"><a :href="project.liveUrl" target="_blank" rel="noopener" class="font-semibold text-teal-ink underline underline-offset-4">{{ project.liveUrl.replace(/^https?:\/\//, '') }}</a></dd>
                    </div>
                </dl>
            </header>

            <!-- Gallery -->
            <section v-if="project.screenshots.length" class="mx-auto mt-12 max-w-6xl px-5 sm:px-8" aria-label="Screenshots">
                <div v-if="kinds.length > 1" class="mb-5 flex flex-wrap gap-2" role="group" aria-label="Filter screenshots">
                    <button v-for="k in [{ key: 'all', label: 'All screens' }, ...kinds]" :key="k.key" type="button"
                            class="rounded-full px-4 py-1.5 text-sm font-medium ring-1"
                            :class="kind === k.key ? 'bg-ink text-white ring-ink' : 'bg-white ring-line hover:ring-ink/40'"
                            :aria-pressed="kind === k.key" @click="kind = k.key">{{ k.label }}</button>
                </div>
                <figure v-if="current">
                    <button type="button" class="group block w-full cursor-zoom-in rounded-2xl bg-[#e9eef2] p-3 ring-1 ring-line sm:p-6" @click="zoom = true" :aria-label="`Enlarge: ${current.caption || 'screenshot'}`">
                        <img :src="current.url" :alt="current.caption || `${project.title} screenshot`"
                             class="mx-auto rounded-lg shadow-[0_24px_60px_-28px_rgba(12,42,69,.5)]"
                             :class="current.kind === 'mobile' ? 'max-h-[640px] w-auto' : 'max-h-[78vh] w-full object-cover object-top'" />
                    </button>
                    <figcaption class="mt-3 flex items-center justify-between gap-4 text-sm text-muted">
                        <span><span class="font-semibold text-ink">{{ current.kindLabel }}.</span> {{ current.caption }}</span>
                        <span class="shrink-0 tabular-nums">{{ active + 1 }} / {{ shots.length }}</span>
                    </figcaption>
                </figure>
                <div class="mt-4 flex gap-3 overflow-x-auto pb-2">
                    <button v-for="(s, i) in shots" :key="s.id" type="button" @click="active = i"
                            class="shrink-0 overflow-hidden rounded-md ring-2 transition"
                            :class="i === active ? 'ring-teal' : 'ring-transparent opacity-70 hover:opacity-100'"
                            :aria-label="`Show ${s.caption || 'screenshot ' + (i + 1)}`" :aria-current="i === active">
                        <img :src="s.url" alt="" loading="lazy" class="h-20 object-cover object-top" :class="s.kind === 'mobile' ? 'w-12' : 'w-32'" />
                    </button>
                </div>
            </section>

            <!-- Body -->
            <section class="mx-auto mt-16 grid max-w-6xl gap-14 px-5 pb-20 sm:px-8 lg:grid-cols-[1fr_320px]">
                <div class="max-w-2xl">
                    <p class="text-xl leading-relaxed">{{ project.summary }}</p>
                    <div v-if="project.body" class="mt-6 space-y-4 leading-relaxed text-ink/80">
                        <p v-for="(para, i) in project.body.split(/\n\s*\n/)" :key="i">{{ para }}</p>
                    </div>
                    <template v-if="project.highlights.length">
                        <h2 class="display mt-12 text-2xl font-bold">What it does</h2>
                        <ul class="mt-4 space-y-3">
                            <li v-for="h in project.highlights" :key="h" class="flex gap-3 leading-relaxed">
                                <svg class="mt-1.5 size-4 shrink-0 text-teal" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M3 8.5l3 3 7-7" /></svg>
                                <span>{{ h }}</span>
                            </li>
                        </ul>
                    </template>
                    <template v-if="project.engineering.length">
                        <h2 class="display mt-12 text-2xl font-bold">Under the hood</h2>
                        <ul class="mt-4 space-y-3 border-l-2 border-line pl-5">
                            <li v-for="e in project.engineering" :key="e" class="leading-relaxed text-ink/85">{{ e }}</li>
                        </ul>
                    </template>
                </div>
                <aside class="lg:sticky lg:top-24 lg:self-start">
                    <div class="rounded-2xl bg-white p-6 ring-1 ring-line">
                        <h2 class="display text-lg font-bold">Built with</h2>
                        <dl class="mt-4 space-y-4">
                            <div v-for="layer in project.stack" :key="layer.label">
                                <dt class="text-xs font-semibold text-teal-ink">{{ layer.label }}</dt>
                                <dd class="mt-1 text-sm leading-relaxed">{{ layer.items.join(', ') }}</dd>
                            </div>
                        </dl>
                    </div>
                    <a href="/#contact" class="mt-4 block rounded-2xl bg-ink p-6 text-white hover:bg-ink-2">
                        <span class="display text-lg font-bold">Need something like this?</span>
                        <span class="mt-1 block text-sm text-white/70">Tell me what you're building.</span>
                    </a>
                </aside>
            </section>

            <nav v-if="next" class="border-t border-line bg-white" aria-label="Next project">
                <Link :href="route('projects.show', next.slug)" class="group mx-auto flex max-w-6xl items-center justify-between px-5 py-10 sm:px-8">
                    <span class="text-sm text-muted">Next project</span>
                    <span class="display text-2xl font-bold group-hover:text-teal-ink sm:text-4xl">{{ next.title }}</span>
                </Link>
            </nav>
        </article>

        <!-- Lightbox -->
        <div v-if="zoom && current" class="fixed inset-0 z-50 flex justify-center overflow-y-auto bg-ink/95 px-4 py-16" role="dialog" aria-modal="true" :aria-label="current.caption || 'Screenshot'" @click.self="zoom = false">
            <img :src="current.url" :alt="current.caption || ''" class="h-fit max-w-full rounded-lg" :class="current.kind === 'mobile' ? 'max-h-[84vh]' : 'max-w-[min(100%,1500px)]'" @click.self="zoom = false" />
            <button type="button" class="fixed top-4 right-4 rounded-full bg-white/10 px-4 py-2 text-sm text-white hover:bg-white/20" @click="zoom = false">Close</button>
            <template v-if="shots.length > 1">
                <button type="button" class="fixed top-1/2 left-3 rounded-full bg-white/10 p-3 text-white hover:bg-white/20" aria-label="Previous screenshot" @click="step(-1)">‹</button>
                <button type="button" class="fixed top-1/2 right-3 rounded-full bg-white/10 p-3 text-white hover:bg-white/20" aria-label="Next screenshot" @click="step(1)">›</button>
            </template>
            <p class="fixed bottom-4 left-1/2 -translate-x-1/2 text-center text-sm text-white/80">{{ current.caption }}</p>
        </div>
    </PublicLayout>
</template>
