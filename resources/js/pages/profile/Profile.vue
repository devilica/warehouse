<script setup>
import { computed, onMounted, ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import FormField from '@/components/ui/FormField.vue';
import AppButton from '@/components/ui/AppButton.vue';
import { useAuthStore } from '@/stores/auth';
import { authApi } from '@/api';
import { useToastStore } from '@/stores/toast';

const auth = useAuthStore();
const toast = useToastStore();
const profile = ref({ name: '', email: '', locale: 'en' });
const passwords = ref({ current_password: '', password: '', password_confirmation: '' });
const saving = ref(false);
const demoAccount = computed(() => auth.user?.email === 'admin@wms.test');

onMounted(() => {
    profile.value = {
        name: auth.user?.name ?? '',
        email: auth.user?.email ?? '',
        locale: auth.user?.locale ?? 'en',
    };
});

async function saveProfile() {
    if (demoAccount.value) return;
    saving.value = true;
    try {
        await authApi.updateProfile(profile.value);
        await auth.fetchMe();
        toast.success('Profile updated');
    } catch {
        toast.error('Failed to update profile');
    } finally {
        saving.value = false;
    }
}

async function changePassword() {
    if (demoAccount.value) return;
    saving.value = true;
    try {
        await authApi.changePassword(passwords.value);
        toast.success('Password changed');
        passwords.value = { current_password: '', password: '', password_confirmation: '' };
    } catch {
        toast.error('Failed to change password');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <AdminLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold">Profile</h1>
            <p v-if="demoAccount" class="mt-2 text-sm text-amber-700">Not allowed on the demo account.</p>
        </div>
        <div class="grid gap-6 lg:grid-cols-2">
            <form class="rounded-2xl border bg-white p-6 shadow-sm space-y-4" @submit.prevent="saveProfile">
                <h2 class="font-semibold">Account</h2>
                <FormField v-model="profile.name" label="Name" required />
                <FormField v-model="profile.email" label="Email" type="email" required />
                <FormField v-model="profile.locale" label="Locale" />
                <AppButton type="submit" :loading="saving" :disabled="demoAccount">Save Profile</AppButton>
            </form>
            <form class="rounded-2xl border bg-white p-6 shadow-sm space-y-4" @submit.prevent="changePassword">
                <h2 class="font-semibold">Change Password</h2>
                <FormField v-model="passwords.current_password" label="Current Password" type="password" required />
                <FormField v-model="passwords.password" label="New Password" type="password" required />
                <FormField v-model="passwords.password_confirmation" label="Confirm Password" type="password" required />
                <AppButton type="submit" :loading="saving" :disabled="demoAccount">Update Password</AppButton>
            </form>
        </div>
    </AdminLayout>
</template>
