<script setup>
// Frames a screenshot as a browser window or a phone, depending on what it shows.
defineProps({
    shot: { type: Object, default: null },
    alt: { type: String, default: '' },
    eager: { type: Boolean, default: false },
});
</script>

<template>
    <div v-if="shot && shot.kind === 'mobile'" class="mx-auto w-full max-w-[260px] rounded-[2.2rem] bg-ink p-2 shadow-[0_30px_60px_-20px_rgba(12,42,69,.45)]">
        <img :src="shot.url" :alt="alt" class="block aspect-[390/844] w-full rounded-[1.7rem] object-cover object-top" :loading="eager ? 'eager' : 'lazy'" />
    </div>
    <div v-else-if="shot" class="overflow-hidden rounded-xl border border-ink/10 bg-white shadow-[0_30px_60px_-24px_rgba(12,42,69,.35)]">
        <div class="flex h-7 items-center gap-1.5 border-b border-line bg-paper px-3" aria-hidden="true">
            <span class="size-2.5 rounded-full bg-line" /><span class="size-2.5 rounded-full bg-line" /><span class="size-2.5 rounded-full bg-line" />
        </div>
        <img :src="shot.url" :alt="alt" class="block aspect-[16/10] w-full object-cover object-top" :loading="eager ? 'eager' : 'lazy'" />
    </div>
    <div v-else class="grid aspect-[16/10] place-items-center rounded-xl border border-dashed border-line bg-white text-sm text-muted">Screenshots coming soon</div>
</template>
