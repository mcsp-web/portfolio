<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PublicLayout from '../Layouts/PublicLayout.vue';
import HeroStack from '../Components/HeroStack.vue';
import DeviceShot from '../Components/DeviceShot.vue';

const props = defineProps({
    profile: { type: Object, required: true },
    projects: { type: Array, required: true },
    categories: { type: Array, required: true },
    stack: { type: Array, required: true },
});

const upworkMode = computed(() => usePage().props.upworkMode);
const market = computed(() =>
    usePage().props.marketplace === 'fiverr'
        ? { name: 'Fiverr', url: props.profile.fiverr_url }
        : { name: 'Upwork', url: props.profile.upwork_url },
);
const filter = ref('all');
const cta = (p) => {
    const parts = [p.featureCount >= 10 ? `${p.featureCount} features` : '', p.screenshotCount ? `${p.screenshotCount} screens` : ''].filter(Boolean);
    return parts.length ? `View project: ${parts.join(' and ')}` : 'View project';
};
const visible = computed(() => (filter.value === 'all' ? props.projects : props.projects.filter((p) => p.category === filter.value)));
const featured = computed(() => visible.value.filter((p) => p.isFeatured));
const rest = computed(() => visible.value.filter((p) => !p.isFeatured));
// Featured projects first, so the hero shows the strongest work; any project can supply the phone.
const heroProjects = computed(() => [...props.projects].sort((a, b) => Number(b.isFeatured) - Number(a.isFeatured)));
const techTotal = computed(() => props.stack.reduce((n, l) => n + l.items.filter((t) => t.projects > 0).length, 0));

const statusTone = {
    live: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
    in_development: 'bg-amber-50 text-amber-800 ring-amber-200',
    demo: 'bg-sky-50 text-sky-800 ring-sky-200',
    internal: 'bg-slate-100 text-slate-700 ring-slate-200',
};

const approach = [
    {
        title: 'Written plan first',
        text: 'Every project keeps a living spec: what we are building, the decisions made and why. You can read it, and the next developer can pick it up without a handover call.',
    },
    {
        title: 'Tested before it ships',
        text: 'Automated test suites run before every release. The larger systems here carry hundreds to over a thousand tests, so changes go out without breaking what already works.',
    },
    {
        title: 'Secure by default',
        text: 'Two-factor sign-in, rate limits, role-based access, tenant isolation and secrets kept out of the code. Money, login and data paths get a reviewed plan before any code is written.',
    },
    {
        title: 'You own the result',
        text: 'Your code in your repository, your domain, your data. Deployments are containerized and documented, so you are never locked in to me.',
    },
];
</script>

<template>
    <Head>
        <title>{{ profile.name }}: full-stack and AI developer</title>
        <meta name="description" :content="profile.intro" />
    </Head>

    <PublicLayout :profile="profile">
        <!-- Hero -->
        <section class="mx-auto grid max-w-6xl items-center gap-12 px-5 pt-14 pb-20 sm:px-8 lg:grid-cols-[1.05fr_1fr] lg:pt-20 lg:pb-28">
            <div>
                <p v-if="profile.availability" class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-sm font-medium ring-1 ring-line">
                    <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-teal opacity-60 motion-reduce:hidden" /><span class="relative inline-flex size-2 rounded-full bg-teal" /></span>
                    {{ profile.availability }}
                </p>
                <h1 class="display mt-6 text-[2.6rem] leading-[1.02] font-bold text-balance sm:text-6xl lg:text-[4.2rem]">
                    {{ profile.headline }}
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-muted">{{ profile.intro }}</p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="#work" class="rounded-full bg-ink px-6 py-3 font-semibold text-white hover:bg-ink-2">See the work</a>
                    <a href="#contact" class="rounded-full px-6 py-3 font-semibold ring-1 ring-ink/20 hover:bg-white">Start a project</a>
                </div>
                <dl class="mt-12 grid max-w-md grid-cols-3 gap-6 border-t border-line pt-6">
                    <div><dt class="text-xs text-muted">Projects shipped</dt><dd class="display mt-1 text-2xl font-bold">{{ projects.length }}</dd></div>
                    <div><dt class="text-xs text-muted">Technologies used</dt><dd class="display mt-1 text-2xl font-bold">{{ techTotal }}</dd></div>
                    <div><dt class="text-xs text-muted">Based in</dt><dd class="mt-1 text-sm font-semibold leading-tight">{{ profile.location }}</dd></div>
                </dl>
            </div>
            <HeroStack :projects="heroProjects" />
        </section>

        <!-- Work -->
        <section id="work" class="scroll-mt-20 border-t border-line bg-white">
            <div class="mx-auto max-w-6xl px-5 py-20 sm:px-8">
                <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="display text-4xl font-bold sm:text-5xl">Selected work</h2>
                        <p class="mt-3 max-w-xl text-muted">SaaS platforms, AI tools and client websites, built and deployed end to end. Back-office screens are shown with demo data.</p>
                    </div>
                    <div class="flex flex-wrap gap-2" role="group" aria-label="Filter projects">
                        <button v-for="c in [{ key: 'all', label: 'All work' }, ...categories]" :key="c.key" type="button"
                                class="rounded-full px-4 py-1.5 text-sm font-medium ring-1 transition-colors"
                                :class="filter === c.key ? 'bg-ink text-white ring-ink' : 'bg-white text-ink ring-line hover:ring-ink/40'"
                                :aria-pressed="filter === c.key" @click="filter = c.key">{{ c.label }}</button>
                    </div>
                </div>

                <!-- Featured: wide alternating rows -->
                <div class="mt-14 space-y-20">
                    <article v-for="(p, i) in featured" :key="p.slug" class="grid items-center gap-10 lg:grid-cols-2">
                        <Link :href="route('projects.show', p.slug)" class="block" :class="i % 2 ? 'lg:order-2' : ''" :aria-label="`View ${p.title}`">
                            <DeviceShot :shot="p.cover" :alt="`${p.title}: ${p.cover?.caption || p.tagline}`" />
                        </Link>
                        <div>
                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                <span class="text-muted">{{ p.categoryLabel }}</span>
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold ring-1" :class="statusTone[p.status]">{{ p.statusLabel }}</span>
                            </div>
                            <h3 class="display mt-3 text-3xl font-bold sm:text-4xl">
                                <Link :href="route('projects.show', p.slug)" class="hover:text-teal-ink">{{ p.title }}</Link>
                            </h3>
                            <p class="mt-2 text-lg font-medium text-ink/80">{{ p.tagline }}</p>
                            <p class="mt-4 leading-relaxed text-muted">{{ p.summary }}</p>
                            <ul class="mt-5 flex flex-wrap gap-1.5" aria-label="Technologies">
                                <li v-for="t in p.technologies.slice(0, 9)" :key="t" class="rounded-md bg-paper px-2 py-1 text-xs font-medium text-ink/80 ring-1 ring-line">{{ t }}</li>
                                <li v-if="p.technologies.length > 9" class="px-1 py-1 text-xs text-muted">+{{ p.technologies.length - 9 }} more</li>
                            </ul>
                            <Link :href="route('projects.show', p.slug)" class="mt-6 inline-block font-semibold text-teal-ink underline decoration-teal/40 underline-offset-4 hover:decoration-teal">
                                {{ cta(p) }}
                            </Link>
                        </div>
                    </article>
                </div>

                <!-- The rest: compact two-column list -->
                <div v-if="rest.length" class="mt-20 grid gap-x-10 gap-y-2 md:grid-cols-2" :class="featured.length ? 'border-t border-line pt-12' : ''">
                    <Link v-for="p in rest" :key="p.slug" :href="route('projects.show', p.slug)"
                          class="group flex gap-5 rounded-xl p-3 transition-colors hover:bg-paper">
                        <div class="w-36 shrink-0 overflow-hidden rounded-lg bg-paper ring-1 ring-line sm:w-44">
                            <img v-if="p.cover" :src="p.cover.url" :alt="''" loading="lazy" class="aspect-[4/3] w-full object-cover object-top" />
                        </div>
                        <div class="min-w-0 py-1">
                            <p class="text-xs text-muted">{{ p.categoryLabel }} <span class="ml-1 rounded-full px-1.5 py-0.5 font-semibold ring-1" :class="statusTone[p.status]">{{ p.statusLabel }}</span></p>
                            <h3 class="display mt-1.5 text-xl font-bold group-hover:text-teal-ink">{{ p.title }}</h3>
                            <p class="mt-1 line-clamp-2 text-sm text-muted">{{ p.tagline }}</p>
                            <p class="mt-2 line-clamp-1 text-xs text-ink/70">{{ p.technologies.slice(0, 5).join(', ') }}</p>
                        </div>
                    </Link>
                </div>
                <p v-if="!visible.length" class="mt-14 text-muted">Nothing in this category yet.</p>
            </div>
        </section>

        <!-- Stack -->
        <section id="stack" class="scroll-mt-16 bg-ink text-white">
            <div class="mx-auto max-w-6xl px-5 py-20 sm:px-8">
                <div class="grid gap-8 lg:grid-cols-[1fr_2fr]">
                    <div>
                        <h2 class="display text-4xl font-bold sm:text-5xl">Tech stack</h2>
                        <p class="mt-4 max-w-sm text-white/70">Everything here has been used in production work on this page. The number beside each one is how many of these projects use it.</p>
                    </div>
                    <dl class="divide-y divide-white/10 border-y border-white/10">
                        <div v-for="layer in stack" :key="layer.key" class="grid gap-3 py-5 sm:grid-cols-[180px_1fr]">
                            <dt class="text-sm font-semibold text-aqua">{{ layer.label }}</dt>
                            <dd class="flex flex-wrap gap-x-5 gap-y-2">
                                <span v-for="t in layer.items" :key="t.name" class="text-[15px] font-medium">
                                    {{ t.name }}<sup v-if="t.projects" class="ml-0.5 text-[10px] font-semibold text-white/50" :title="`Used in ${t.projects} project${t.projects === 1 ? '' : 's'}`">{{ t.projects }}</sup>
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </section>

        <!-- Approach -->
        <section id="approach" class="scroll-mt-16 mx-auto max-w-6xl px-5 py-20 sm:px-8">
            <h2 class="display text-4xl font-bold sm:text-5xl">How I work</h2>
            <div class="mt-12 grid gap-x-12 gap-y-10 md:grid-cols-2">
                <div v-for="a in approach" :key="a.title" class="border-l-2 border-teal pl-5">
                    <h3 class="display text-xl font-bold">{{ a.title }}</h3>
                    <p class="mt-2 leading-relaxed text-muted">{{ a.text }}</p>
                </div>
            </div>
            <div v-if="profile.about" class="mt-16 max-w-3xl space-y-4 text-lg leading-relaxed text-ink/85">
                <p v-for="(para, i) in profile.about.split(/\n\s*\n/)" :key="i">{{ para }}</p>
            </div>
        </section>

        <!-- Contact -->
        <section id="contact" class="scroll-mt-16 border-t border-line bg-white">
            <div class="mx-auto max-w-6xl px-5 py-20 sm:px-8">
                <h2 class="display max-w-3xl text-4xl font-bold text-balance sm:text-6xl">Have something to build? Tell me about it.</h2>
                <template v-if="upworkMode">
                    <p class="mt-5 max-w-xl text-lg text-muted">Send me a message on {{ market.name }} with what you're building and your timeline. I reply within one business day.</p>
                    <div class="mt-10 flex flex-wrap items-center gap-4">
                        <a v-if="market.url" :href="market.url" target="_blank" rel="noopener" class="display rounded-full bg-teal px-7 py-4 text-lg font-bold text-ink hover:bg-aqua">Message me on {{ market.name }}</a>
                    </div>
                </template>
                <template v-else>
                    <p class="mt-5 max-w-xl text-lg text-muted">Fixed-price projects, ongoing contracts, or a second pair of hands on an existing Laravel, Vue or AI codebase. Replies within one business day.</p>
                    <div class="mt-10 flex flex-wrap items-center gap-4">
                        <a :href="`mailto:${profile.email}`" class="display rounded-full bg-teal px-7 py-4 text-lg font-bold break-all text-ink hover:bg-aqua">{{ profile.email }}</a>
                        <a v-if="profile.upwork_url" :href="profile.upwork_url" target="_blank" rel="noopener" class="rounded-full px-6 py-4 font-semibold ring-1 ring-ink/20 hover:bg-paper">Hire me on Upwork</a>
                        <a v-if="profile.linkedin_url" :href="profile.linkedin_url" target="_blank" rel="noopener" class="rounded-full px-6 py-4 font-semibold ring-1 ring-ink/20 hover:bg-paper">LinkedIn</a>
                    </div>
                </template>
            </div>
        </section>
    </PublicLayout>
</template>
