<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const form = useForm({ email: '', password: '', remember: true });
const problem = ref('');

// Every failed attempt ends with a visible reason: a validation message from the server,
// or a fallback when the request itself fails (network, server error).
const message = computed(() => form.errors.email || form.errors.password || problem.value);

const submit = () => {
    problem.value = '';
    form.post(route('login.store'), {
        onFinish: () => form.reset('password'),
        onNetworkError: () => {
            problem.value = 'Sign-in failed because the server could not be reached. Try again in a moment.';
            return false;
        },
        onHttpException: () => {
            problem.value = 'Sign-in failed on the server. Try again in a moment.';
            return false;
        },
    });
};
</script>

<template>
    <Head title="Admin sign in" />
    <div class="grid min-h-screen place-items-center bg-paper px-5">
        <form class="w-full max-w-sm rounded-2xl bg-white p-8 ring-1 ring-line" novalidate @submit.prevent="submit">
            <h1 class="display text-2xl font-bold">Portfolio admin</h1>
            <p class="mt-1 text-sm text-muted">Sign in to manage projects and screenshots.</p>

            <div v-if="message" role="alert" class="mt-5 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                {{ message }}
            </div>

            <div class="mt-5">
                <label for="email" class="field-label">Email</label>
                <input id="email" v-model="form.email" type="email" class="input" :class="form.errors.email && 'border-red-400'"
                       autocomplete="username" required autofocus :aria-invalid="!!form.errors.email" />
            </div>
            <div class="mt-4">
                <label for="password" class="field-label">Password</label>
                <input id="password" v-model="form.password" type="password" class="input" :class="form.errors.password && 'border-red-400'"
                       autocomplete="current-password" required :aria-invalid="!!form.errors.password" />
            </div>
            <label class="mt-4 flex items-center gap-2 text-sm"><input v-model="form.remember" type="checkbox" class="rounded border-line" /> Keep me signed in</label>
            <button type="submit" class="btn-primary mt-6 w-full" :disabled="form.processing">
                {{ form.processing ? 'Signing in…' : 'Sign in' }}
            </button>
        </form>
    </div>
</template>
