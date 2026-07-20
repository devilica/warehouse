<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import FormField from '@/components/ui/FormField.vue';
import AppButton from '@/components/ui/AppButton.vue';

const auth = useAuthStore();
const toast = useToastStore();
const router = useRouter();

const form = ref({ email: '', password: '', device_name: 'web' });
const error = ref('');

async function submit() {
    error.value = '';
    try {
        await auth.login(form.value);
        toast.success('Welcome back!');
        router.push('/');
    } catch (e) {
        error.value = typeof e === 'string' ? e : 'Invalid credentials';
    }
}

onMounted(() => {
    if (auth.isAuthenticated) router.push('/');
});
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 p-4">
        <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl">
            <div class="mb-8 text-center">
                <div class="text-xs font-semibold uppercase tracking-widest text-indigo-600">Company WMS</div>
                <h1 class="mt-2 text-3xl font-bold text-slate-900">Sign in</h1>
                <p class="mt-2 text-sm text-slate-500">Internal warehouse management for your company</p>
            </div>
            <form class="space-y-4" @submit.prevent="submit">
                <FormField v-model="form.email" label="Email" type="email" required />
                <FormField v-model="form.password" label="Password" type="password" required />
                <p v-if="error" class="rounded-xl bg-red-50 px-3 py-2 text-sm text-red-600">{{ error }}</p>
                <AppButton type="submit" class="w-full" size="lg" :loading="auth.loading">Sign in</AppButton>
            </form>
            <p class="mt-6 text-center text-xs text-slate-400">
                Accounts are created by administrators.<br>
                Demo: admin@wms.test / password
            </p>
        </div>
    </div>
</template>
