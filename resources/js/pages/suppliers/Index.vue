<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Modal from '@/components/ui/Modal.vue';
import FormField from '@/components/ui/FormField.vue';
import AppButton from '@/components/ui/AppButton.vue';
import { suppliersApi } from '@/api';
import { usePagination } from '@/composables/usePagination';
import { useToastStore } from '@/stores/toast';
import { usePermission } from '@/composables/usePermission';

const toast = useToastStore();
const { can } = usePermission();
const { items, meta, loading, load, goToPage } = usePagination(suppliersApi.list);
const showModal = ref(false);
const editing = ref(null);
const form = ref({ name: '', email: '', phone: '' });
const saving = ref(false);

onMounted(load);

function openCreate() { editing.value = null; form.value = { name: '', email: '', phone: '' }; showModal.value = true; }
function openEdit(row) { editing.value = row; form.value = { name: row.name, email: row.email ?? '', phone: row.phone ?? '' }; showModal.value = true; }

async function save() {
    saving.value = true;
    try {
        if (editing.value) await suppliersApi.update(editing.value.id, form.value);
        else await suppliersApi.create(form.value);
        toast.success('Supplier saved');
        showModal.value = false;
        await load();
    } catch { toast.error('Failed to save supplier'); }
    finally { saving.value = false; }
}

async function remove(row) { if (!confirm('Delete?')) return; await suppliersApi.remove(row.id); await load(); }
</script>

<template>
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div><h1 class="text-2xl font-bold">Suppliers</h1></div>
            <AppButton v-if="can('suppliers.create')" @click="openCreate">Add Supplier</AppButton>
        </div>
        <DataTable :columns="[{key:'name',label:'Name'},{key:'email',label:'Email'},{key:'phone',label:'Phone'}]" :rows="items" :loading="loading" :meta="meta" @page="goToPage">
            <template #actions="{ row }">
                <AppButton v-if="can('suppliers.update')" size="sm" variant="secondary" @click="openEdit(row)">Edit</AppButton>
                <AppButton v-if="can('suppliers.delete')" size="sm" variant="danger" @click="remove(row)">Delete</AppButton>
            </template>
        </DataTable>
        <Modal :open="showModal" title="Supplier" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="save">
                <FormField v-model="form.name" label="Name" required />
                <FormField v-model="form.email" label="Email" type="email" />
                <FormField v-model="form.phone" label="Phone" />
                <AppButton type="submit" :loading="saving">Save</AppButton>
            </form>
        </Modal>
    </AdminLayout>
</template>
