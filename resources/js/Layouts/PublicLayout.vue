<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ profile: { type: Object, required: true } });
const open = ref(false);
</script>

<template>
    <div class="min-h-screen bg-paper">
        <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded focus:bg-white focus:px-3 focus:py-2">Skip to content</a>
        <header class="sticky top-0 z-40 border-b border-line/70 bg-paper/90 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-5 sm:px-8">
                <Link :href="route('home')" class="flex items-baseline gap-2">
                    <span class="display text-lg font-bold">{{ profile.name }}</span>
                    <span v-if="profile.company" class="hidden text-sm text-muted sm:inline">{{ profile.company }}</span>
                </Link>
                <nav class="hidden items-center gap-7 text-sm font-medium md:flex" aria-label="Main">
                    <a :href="route('home') + '#work'" class="hover:text-teal-ink">Work</a>
                    <a :href="route('home') + '#stack'" class="hover:text-teal-ink">Tech stack</a>
                    <a :href="route('home') + '#approach'" class="hover:text-teal-ink">How I work</a>
                    <a :href="route('home') + '#contact'" class="rounded-full bg-ink px-4 py-2 text-white hover:bg-ink-2">Start a project</a>
                </nav>
                <button class="md:hidden" :aria-expanded="open" aria-controls="mnav" @click="open = !open">
                    <span class="sr-only">Menu</span>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path v-if="!open" d="M4 7h16M4 12h16M4 17h16" /><path v-else d="M6 6l12 12M18 6L6 18" /></svg>
                </button>
            </div>
            <nav v-show="open" id="mnav" class="border-t border-line px-5 py-3 text-base md:hidden" aria-label="Mobile">
                <a v-for="[href, label] in [['#work','Work'],['#stack','Tech stack'],['#approach','How I work'],['#contact','Start a project']]" :key="href"
                   :href="route('home') + href" class="block py-2" @click="open = false">{{ label }}</a>
            </nav>
        </header>

        <main id="main"><slot /></main>

        <footer class="border-t border-line">
            <div class="mx-auto flex max-w-6xl flex-col gap-3 px-5 py-8 text-sm text-muted sm:flex-row sm:items-center sm:justify-between sm:px-8">
                <p>© {{ new Date().getFullYear() }} {{ profile.company || profile.name }}. Screens shown use demo data; client details are withheld.</p>
                <div class="flex gap-5">
                    <a v-if="profile.upwork_url" :href="profile.upwork_url" class="hover:text-ink" rel="noopener" target="_blank">Upwork</a>
                    <a v-if="profile.fiverr_url" :href="profile.fiverr_url" class="hover:text-ink" rel="noopener" target="_blank">Fiverr</a>
                    <a v-if="profile.linkedin_url" :href="profile.linkedin_url" class="hover:text-ink" rel="noopener" target="_blank">LinkedIn</a>
                    <a v-if="profile.github_url" :href="profile.github_url" class="hover:text-ink" rel="noopener" target="_blank">GitHub</a>
                    <a v-if="profile.website_url" :href="profile.website_url" class="hover:text-ink" rel="noopener" target="_blank">Website</a>
                </div>
            </div>
        </footer>
    </div>
</template>
