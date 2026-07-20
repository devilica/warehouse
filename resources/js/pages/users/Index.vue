<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Modal from '@/components/ui/Modal.vue';
import FormField from '@/components/ui/FormField.vue';
import AppButton from '@/components/ui/AppButton.vue';
import Badge from '@/components/ui/Badge.vue';
import { usersApi, rolesApi } from '@/api';
import { getErrorMessage } from '@/api/client';
import { usePagination } from '@/composables/usePagination';
import { useToastStore } from '@/stores/toast';
import { usePermission } from '@/composables/usePermission';

const toast = useToastStore();
const { can } = usePermission();
const { items, meta, loading, load, goToPage } = usePagination(usersApi.list, { include: 'roles' });
const roles = ref([]);
const showModal = ref(false);
const editing = ref(null);
const form = ref({ name: '', email: '', password: '', role: 'viewer' });
const errors = ref({});
const saving = ref(false);

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'roles', label: 'Role' },
];

onMounted(async () => {
    await load();
    roles.value = await rolesApi.list();
});

function openCreate() {
    editing.value = null;
    form.value = { name: '', email: '', password: '', role: 'viewer' };
    errors.value = {};
    showModal.value = true;
}

function openEdit(row) {
    editing.value = row;
    form.value = { name: row.name, email: row.email, password: '', role: row.roles?.[0] ?? 'viewer' };
    errors.value = {};
    showModal.value = true;
}

async function save() {
    saving.value = true;
    errors.value = {};
    try {
        const payload = { ...form.value };
        if (editing.value) {
            if (!payload.password) delete payload.password;
            await usersApi.update(editing.value.id, payload);
            toast.success('User updated');
        } else {
            await usersApi.create(payload);
            toast.success('User created');
        }
        showModal.value = false;
        await load();
    } catch (e) {
        errors.value = e.response?.data?.errors ?? {};
        toast.error(getErrorMessage(e));
    } finally {
        saving.value = false;
    }
}

async function remove(row) {
    if (!confirm(`Delete ${row.name}?`)) return;
    await usersApi.remove(row.id);
    toast.success('User deleted');
    await load();
}
</script>

<template>
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Users</h1>
                <p class="text-sm text-slate-500">Manage company accounts and roles</p>
            </div>
            <AppButton v-if="can('users.create')" @click="openCreate">Add User</AppButton>
        </div>
        <DataTable :columns="columns" :rows="items" :loading="loading" :meta="meta" @page="goToPage">
            <template #cell-roles="{ row }">
                <Badge variant="info">{{ row.roles?.[0] ?? '—' }}</Badge>
            </template>
            <template #actions="{ row }">
                <div class="flex justify-end gap-2">
                    <AppButton v-if="can('users.update')" size="sm" variant="secondary" @click="openEdit(row)">Edit</AppButton>
                    <AppButton v-if="can('users.delete')" size="sm" variant="danger" @click="remove(row)">Delete</AppButton>
                </div>
            </template>
        </DataTable>
        <Modal :open="showModal" :title="editing ? 'Edit User' : 'Create User'" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="save">
                <FormField v-model="form.name" label="Name" required :error="errors.name?.[0]" />
                <FormField v-model="form.email" label="Email" type="email" required :error="errors.email?.[0]" />
                <FormField v-model="form.password" :label="editing ? 'Password (leave blank to keep)' : 'Password (min. 8 characters)'" type="password" :required="!editing" :error="errors.password?.[0]" />
                <FormField v-model="form.role" label="Role" type="select" :error="errors.role?.[0]">
                    <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                </FormField>
                <div class="flex justify-end gap-2 pt-2">
                    <AppButton variant="secondary" @click="showModal = false">Cancel</AppButton>
                    <AppButton type="submit" :loading="saving">Save</AppButton>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
