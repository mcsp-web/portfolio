<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

defineProps({ title: { type: String, required: true } });
const page = usePage();
const flash = ref(null);
watch(() => page.props.flash?.success, (m) => {
    flash.value = m;
    if (m) setTimeout(() => (flash.value === m ? (flash.value = null) : null), 3500);
}, { immediate: true });

const nav = [
    ['admin.projects.index', 'Projects', 'admin.projects.*'],
    ['admin.messages.index', 'Messages', 'admin.messages.*'],
    ['admin.technologies.index', 'Tech stack', 'admin.technologies.*'],
    ['admin.settings.edit', 'Profile', 'admin.settings.*'],
];
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <div class="min-h-screen bg-paper">
        <header class="bg-ink text-white">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-8 gap-y-2 px-5 py-3">
                <span class="display font-bold">Portfolio admin</span>
                <nav class="-mx-1 flex w-full gap-1 overflow-x-auto px-1 text-sm sm:w-auto" aria-label="Admin">
                    <Link v-for="[name, label, match] in nav" :key="name" :href="route(name)"
                          class="inline-flex shrink-0 items-center whitespace-nowrap rounded-md px-3 py-1.5" :class="route().current(match) ? 'bg-white/15 font-semibold' : 'text-white/75 hover:text-white'">{{ label }}<span v-if="name === 'admin.messages.index' && page.props.unreadMessages" class="ml-1.5 rounded-full bg-teal px-1.5 text-xs font-bold text-ink">{{ page.props.unreadMessages }}</span></Link>
                </nav>
                <div class="ml-auto flex items-center gap-4 text-sm">
                    <a :href="route('home')" target="_blank" class="text-white/75 hover:text-white">View site</a>
                    <span class="hidden text-white/50 sm:inline">{{ user?.email }}</span>
                    <button type="button" class="text-white/75 hover:text-white" @click="router.post(route('admin.logout'))">Log out</button>
                </div>
            </div>
        </header>
        <main class="mx-auto max-w-6xl px-5 py-8">
            <h1 class="display mb-6 text-3xl font-bold">{{ title }}</h1>
            <slot />
        </main>
        <transition enter-from-class="opacity-0 translate-y-2" leave-to-class="opacity-0" enter-active-class="transition" leave-active-class="transition">
            <div v-if="flash" role="status" class="fixed right-5 bottom-5 z-50 rounded-lg bg-ink px-4 py-3 text-sm text-white shadow-lg">{{ flash }}</div>
        </transition>
    </div>
</template>
