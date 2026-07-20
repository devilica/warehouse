<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Modal from '@/components/ui/Modal.vue';
import FormField from '@/components/ui/FormField.vue';
import AppButton from '@/components/ui/AppButton.vue';
import { employeesApi } from '@/api';
import { usePagination } from '@/composables/usePagination';
import { useToastStore } from '@/stores/toast';
import { usePermission } from '@/composables/usePermission';

const toast = useToastStore();
const { can } = usePermission();
const { items, meta, loading, load, goToPage } = usePagination(employeesApi.list);
const showModal = ref(false);
const editing = ref(null);
const form = ref({ first_name: '', last_name: '', phone: '', department_id: '' });
const saving = ref(false);

const columns = [
    { key: 'first_name', label: 'First Name' },
    { key: 'last_name', label: 'Last Name' },
    { key: 'phone', label: 'Phone' },
];

onMounted(load);

function openCreate() {
    editing.value = null;
    form.value = { first_name: '', last_name: '', phone: '', department_id: '' };
    showModal.value = true;
}

function openEdit(row) {
    editing.value = row;
    form.value = { first_name: row.first_name, last_name: row.last_name, phone: row.phone ?? '', department_id: row.department_id ?? '' };
    showModal.value = true;
}

async function save() {
    saving.value = true;
    try {
        if (editing.value) {
            await employeesApi.update(editing.value.id, form.value);
            toast.success('Employee updated');
        } else {
            await employeesApi.create(form.value);
            toast.success('Employee created');
        }
        showModal.value = false;
        await load();
    } catch {
        toast.error('Failed to save employee');
    } finally {
        saving.value = false;
    }
}

async function remove(row) {
    if (!confirm(`Delete ${row.first_name} ${row.last_name}?`)) return;
    await employeesApi.remove(row.id);
    toast.success('Employee deleted');
    await load();
}
</script>

<template>
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Employees</h1>
                <p class="text-sm text-slate-500">Company staff records</p>
            </div>
            <AppButton v-if="can('employees.create')" @click="openCreate">Add Employee</AppButton>
        </div>
        <DataTable :columns="columns" :rows="items" :loading="loading" :meta="meta" @page="goToPage">
            <template #actions="{ row }">
                <div class="flex justify-end gap-2">
                    <AppButton v-if="can('employees.update')" size="sm" variant="secondary" @click="openEdit(row)">Edit</AppButton>
                    <AppButton v-if="can('employees.delete')" size="sm" variant="danger" @click="remove(row)">Delete</AppButton>
                </div>
            </template>
        </DataTable>
        <Modal :open="showModal" :title="editing ? 'Edit Employee' : 'Create Employee'" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="save">
                <FormField v-model="form.first_name" label="First Name" required />
                <FormField v-model="form.last_name" label="Last Name" required />
                <FormField v-model="form.phone" label="Phone" />
                <div class="flex justify-end gap-2 pt-2">
                    <AppButton variant="secondary" @click="showModal = false">Cancel</AppButton>
                    <AppButton type="submit" :loading="saving">Save</AppButton>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
