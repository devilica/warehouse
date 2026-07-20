<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Modal from '@/components/ui/Modal.vue';
import FormField from '@/components/ui/FormField.vue';
import AppButton from '@/components/ui/AppButton.vue';
import { categoriesApi } from '@/api';
import { usePagination } from '@/composables/usePagination';
import { useToastStore } from '@/stores/toast';
import { usePermission } from '@/composables/usePermission';

const toast = useToastStore();
const { can } = usePermission();
const { items, meta, loading, load, goToPage } = usePagination(categoriesApi.list);
const showModal = ref(false);
const editing = ref(null);
const form = ref({ name: '', code: '' });
const saving = ref(false);

onMounted(load);

async function save() {
    saving.value = true;
    try {
        if (editing.value) await categoriesApi.update(editing.value.id, form.value);
        else await categoriesApi.create(form.value);
        toast.success('Category saved');
        showModal.value = false;
        await load();
    } catch { toast.error('Failed to save category'); }
    finally { saving.value = false; }
}

function openCreate() { editing.value = null; form.value = { name: '', code: '' }; showModal.value = true; }
function openEdit(row) { editing.value = row; form.value = { name: row.name, code: row.code ?? '' }; showModal.value = true; }
async function remove(row) { if (!confirm('Delete?')) return; await categoriesApi.remove(row.id); await load(); }
</script>

<template>
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div><h1 class="text-2xl font-bold">Categories</h1></div>
            <AppButton v-if="can('categories.create')" @click="openCreate">Add Category</AppButton>
        </div>
        <DataTable :columns="[{key:'name',label:'Name'},{key:'code',label:'Code'}]" :rows="items" :loading="loading" :meta="meta" @page="goToPage">
            <template #actions="{ row }">
                <AppButton v-if="can('categories.update')" size="sm" variant="secondary" @click="openEdit(row)">Edit</AppButton>
                <AppButton v-if="can('categories.delete')" size="sm" variant="danger" @click="remove(row)">Delete</AppButton>
            </template>
        </DataTable>
        <Modal :open="showModal" title="Category" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="save">
                <FormField v-model="form.name" label="Name" required />
                <FormField v-model="form.code" label="Code" />
                <AppButton type="submit" :loading="saving">Save</AppButton>
            </form>
        </Modal>
    </AdminLayout>
</template>
