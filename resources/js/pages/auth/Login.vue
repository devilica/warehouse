<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import AppButton from '@/components/ui/AppButton.vue';
import loginBg from '../../../images/login-bg.jpg';

const auth = useAuthStore();
const toast = useToastStore();
const router = useRouter();

const demo = { email: 'admin@wms.test', password: 'password' };
const form = ref({ email: '', password: '', device_name: 'web' });
const error = ref('');
const showPassword = ref(false);

const fieldClass = 'w-full rounded-xl border border-slate-200/80 bg-white/70 py-2.5 pl-11 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20';

async function signIn(redirect = '/') {
    error.value = '';
    try {
        await auth.login(form.value);
        toast.success('Welcome back!');
        router.push(redirect);
    } catch (e) {
        error.value = typeof e === 'string' ? e : 'Invalid credentials';
    }
}

async function signInAsDemo() {
    form.value.email = demo.email;
    form.value.password = demo.password;
    showPassword.value = true;
    await signIn();
}

onMounted(() => {
    if (auth.isAuthenticated) router.push('/');
});
</script>

<template>
    <div
        class="relative flex min-h-screen items-center justify-center bg-slate-900 bg-cover bg-center p-4"
        :style="{ backgroundImage: `url(${loginBg})` }"
    >
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900/70 via-slate-900/55 to-indigo-900/60" />
        <div class="relative w-full max-w-md rounded-3xl border border-white/50 bg-white/85 p-8 shadow-2xl shadow-slate-900/30 backdrop-blur-xl">
            <div class="mb-8 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/30">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8l-9-5-9 5v8l9 5 9-5V8z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 8.2L12 13l8.5-4.8M12 13v8.2" />
                    </svg>
                </div>
                <div class="mt-4 text-xs font-semibold uppercase tracking-widest text-indigo-600">Company WMS</div>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Sign in</h1>
                <p class="mt-2 text-sm text-slate-500">Warehouse operations, in one place</p>
            </div>
            <form class="space-y-4" @submit.prevent="signIn()">
                <label class="block space-y-1.5">
                    <span class="text-sm font-medium text-slate-700">Email</span>
                    <span class="relative block">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16v12H4z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7l8 6 8-6" />
                        </svg>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="username"
                            placeholder="you@company.com"
                            class="pr-4"
                            :class="fieldClass"
                        />
                    </span>
                </label>
                <label class="block space-y-1.5">
                    <span class="text-sm font-medium text-slate-700">Password</span>
                    <span class="relative block">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="5" y="11" width="14" height="9" rx="2" />
                            <path stroke-linecap="round" d="M8 11V8a4 4 0 018 0v3" />
                        </svg>
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autocomplete="current-password"
                            placeholder="Your password"
                            class="pr-11"
                            :class="fieldClass"
                        />
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                            :aria-label="showPassword ? 'Hide password' : 'Show password'"
                            @click="showPassword = !showPassword"
                        >
                            <svg v-if="showPassword" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6A2 2 0 0012 14a2 2 0 001.4-.6M9.9 5.1A10.8 10.8 0 0112 5c5 0 9.3 3.1 11 7-0.6 1.4-1.6 2.7-2.8 3.8M6.1 6.1C4.3 7.3 2.9 9 2 12c1.7 3.9 6 7 10 7 1.5 0 2.9-.3 4.2-.9" />
                            </svg>
                            <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </button>
                    </span>
                </label>
                <p v-if="error" class="flex items-center gap-2 rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-sm text-red-600">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" d="M12 8v5M12 16.5v.5" />
                    </svg>
                    {{ error }}
                </p>
                <AppButton type="submit" class="w-full" size="lg" :loading="auth.loading">Sign in</AppButton>
            </form>
            <button
                type="button"
                class="mt-6 flex w-full items-center justify-between gap-3 rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-left transition hover:border-indigo-400 hover:bg-indigo-100 disabled:opacity-60"
                :disabled="auth.loading"
                @click="signInAsDemo"
            >
                <span>
                    <span class="block text-xs font-semibold uppercase tracking-wide text-indigo-600">Try the demo</span>
                    <span class="mt-0.5 block text-sm font-medium text-slate-800">{{ demo.email }} / {{ demo.password }}</span>
                </span>
                <span class="rounded-lg bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white">Open</span>
            </button>
        </div>
    </div>
</template>
