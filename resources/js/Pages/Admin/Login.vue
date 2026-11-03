<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ email: '', password: '', remember: true });
const submit = () => form.post(route('login.store'), { onFinish: () => form.reset('password') });
</script>

<template>
    <Head title="Admin sign in" />
    <div class="grid min-h-screen place-items-center bg-paper px-5">
        <form class="w-full max-w-sm rounded-2xl bg-white p-8 ring-1 ring-line" @submit.prevent="submit">
            <h1 class="display text-2xl font-bold">Portfolio admin</h1>
            <p class="mt-1 text-sm text-muted">Sign in to manage projects and screenshots.</p>
            <div class="mt-6">
                <label for="email" class="field-label">Email</label>
                <input id="email" v-model="form.email" type="email" class="input" autocomplete="username" required autofocus />
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-700">{{ form.errors.email }}</p>
            </div>
            <div class="mt-4">
                <label for="password" class="field-label">Password</label>
                <input id="password" v-model="form.password" type="password" class="input" autocomplete="current-password" required />
            </div>
            <label class="mt-4 flex items-center gap-2 text-sm"><input v-model="form.remember" type="checkbox" class="rounded border-line" /> Keep me signed in</label>
            <button type="submit" class="btn-primary mt-6 w-full" :disabled="form.processing">Sign in</button>
        </form>
    </div>
</template>
