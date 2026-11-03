<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// The one bold moment on the page: real product screens fanned out like a hand of cards.
const props = defineProps({ projects: { type: Array, required: true } });

const cards = computed(() => {
    const withCover = props.projects.filter((p) => p.cover);
    const desktop = withCover.filter((p) => p.cover.kind !== 'mobile').slice(0, 2);
    const phone = withCover.find((p) => p.cover.kind === 'mobile');
    return { desktop, phone };
});
</script>

<template>
    <div class="relative mx-auto h-[340px] w-full max-w-[560px] sm:h-[420px]" aria-label="Screens from recent projects">
        <Link v-for="(p, i) in cards.desktop" :key="p.slug" :href="route('projects.show', p.slug)"
              class="hero-card absolute w-[82%] overflow-hidden rounded-xl border border-ink/10 bg-white shadow-[0_40px_80px_-30px_rgba(12,42,69,.55)] transition-transform duration-300 hover:-translate-y-1"
              :class="i === 0 ? 'card-a left-0 top-2 z-10' : 'card-b right-0 top-24 z-20 sm:top-28'">
            <div class="flex h-6 items-center gap-1.5 border-b border-line bg-paper px-3" aria-hidden="true">
                <span class="size-2 rounded-full bg-line" /><span class="size-2 rounded-full bg-line" /><span class="size-2 rounded-full bg-line" />
                <span class="ml-2 truncate text-[11px] font-medium text-muted">{{ p.title }}</span>
            </div>
            <img :src="p.cover.url" :alt="`${p.title}: ${p.cover.caption || p.tagline}`" class="block aspect-[16/10] w-full object-cover object-top" />
        </Link>
        <Link v-if="cards.phone" :href="route('projects.show', cards.phone.slug)"
              class="hero-card card-c absolute bottom-0 left-[6%] z-30 w-[24%] min-w-[96px] rounded-[1.4rem] bg-ink p-1.5 shadow-[0_30px_60px_-20px_rgba(12,42,69,.6)] transition-transform duration-300 hover:-translate-y-1">
            <img :src="cards.phone.cover.url" :alt="`${cards.phone.title} on a phone`" class="block aspect-[390/844] w-full rounded-[1.1rem] object-cover object-top" />
        </Link>
    </div>
</template>

<style scoped>
.hero-card { animation: deal 0.9s cubic-bezier(.2,.8,.2,1) both; }
.card-a { --r: -3deg; animation-delay: .05s; transform: rotate(var(--r)); }
.card-b { --r: 2.5deg; animation-delay: .2s; transform: rotate(var(--r)); }
.card-c { --r: -5deg; animation-delay: .35s; transform: rotate(var(--r)); }
.hero-card:hover { transform: rotate(0deg) translateY(-4px); }
@keyframes deal {
    from { opacity: 0; transform: translateY(40px) rotate(0deg) scale(.96); }
    to { opacity: 1; transform: rotate(var(--r)); }
}
</style>
