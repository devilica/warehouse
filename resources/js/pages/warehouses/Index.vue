<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Modal from '@/components/ui/Modal.vue';
import FormField from '@/components/ui/FormField.vue';
import AppButton from '@/components/ui/AppButton.vue';
import { warehousesApi } from '@/api';
import { usePagination } from '@/composables/usePagination';
import { useToastStore } from '@/stores/toast';
import { usePermission } from '@/composables/usePermission';

const route = useRoute();
const router = useRouter();
const toast = useToastStore();
const { can } = usePermission();
const { items, meta, loading, params, load, goToPage, setSearch, setSort } = usePagination(warehousesApi.list);
const showModal = ref(false);
const editing = ref(null);
const form = ref({ name: '', address: '', capacity: '' });
const saving = ref(false);

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'address', label: 'Address' },
    { key: 'capacity', label: 'Capacity' },
];

onMounted(load);

function openCreate() {
    editing.value = null;
    form.value = { name: '', address: '', capacity: '' };
    showModal.value = true;
}

function openEdit(row) {
    editing.value = row;
    form.value = { name: row.name, address: row.address ?? '', capacity: row.capacity ?? '' };
    showModal.value = true;
}

async function save() {
    saving.value = true;
    try {
        if (editing.value) {
            await warehousesApi.update(editing.value.id, form.value);
            toast.success('Warehouse updated');
        } else {
            await warehousesApi.create(form.value);
            toast.success('Warehouse created');
        }
        showModal.value = false;
        await load();
    } catch {
        toast.error('Failed to save warehouse');
    } finally {
        saving.value = false;
    }
}

async function remove(row) {
    if (!confirm(`Delete ${row.name}?`)) return;
    await warehousesApi.remove(row.id);
    toast.success('Warehouse deleted');
    await load();
}
</script>

<template>
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Warehouses</h1>
                <p class="text-sm text-slate-500">Manage warehouse locations and structure</p>
            </div>
            <AppButton v-if="can('warehouses.create')" @click="openCreate">Add Warehouse</AppButton>
        </div>
        <DataTable :columns="columns" :rows="items" :loading="loading" :meta="meta" :sort="params.sort" searchable search-placeholder="Search name" @page="goToPage" @search="setSearch" @sort="setSort">
            <template #actions="{ row }">
                <div class="flex w-full flex-col gap-1">
                    <AppButton v-if="can('warehouses.update')" size="sm" variant="secondary" class="w-full" @click="openEdit(row)">Edit</AppButton>
                    <AppButton v-if="can('warehouses.delete')" size="sm" variant="danger" class="w-full" @click="remove(row)">Delete</AppButton>
                </div>
                <AppButton size="sm" variant="secondary" @click="router.push(`/warehouses/${row.id}`)">Manage</AppButton>
            </template>
        </DataTable>
        <Modal :open="showModal" :title="editing ? 'Edit Warehouse' : 'Create Warehouse'" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="save">
                <FormField v-model="form.name" label="Name" required />
                <FormField v-model="form.address" label="Address" />
                <FormField v-model="form.capacity" label="Capacity" type="number" />
                <div class="flex justify-end gap-2 pt-2">
                    <AppButton variant="secondary" @click="showModal = false">Cancel</AppButton>
                    <AppButton type="submit" :loading="saving">Save</AppButton>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
