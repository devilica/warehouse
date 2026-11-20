<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Modal from '@/components/ui/Modal.vue';
import FormField from '@/components/ui/FormField.vue';
import AppButton from '@/components/ui/AppButton.vue';
import { categoriesApi } from '@/api';
import { unwrapPaginated } from '@/api/client';
import { usePagination } from '@/composables/usePagination';
import { useToastStore } from '@/stores/toast';
import { usePermission } from '@/composables/usePermission';

const toast = useToastStore();
const { can } = usePermission();
const { items, meta, loading, params, load, goToPage, setSearch, setSort } = usePagination(categoriesApi.list);
const parents = ref([]);
const showModal = ref(false);
const editing = ref(null);
const form = ref({ name: '', parent_id: '' });
const saving = ref(false);

onMounted(() => {
    load();
    refreshParents();
});

function refreshParents() {
    categoriesApi.list({ per_page: 100 }).then((res) => { parents.value = unwrapPaginated(res).data; });
}

async function save() {
    saving.value = true;
    try {
        if (editing.value) await categoriesApi.update(editing.value.id, form.value);
        else await categoriesApi.create(form.value);
        toast.success('Category saved');
        showModal.value = false;
        await load();
        refreshParents();
    } catch { toast.error('Failed to save category'); }
    finally { saving.value = false; }
}

function openCreate() { editing.value = null; form.value = { name: '', parent_id: '' }; showModal.value = true; }
function openEdit(row) { editing.value = row; form.value = { name: row.name, parent_id: row.parent_id ?? '' }; showModal.value = true; }
async function remove(row) { if (!confirm('Delete?')) return; await categoriesApi.remove(row.id); await load(); }
</script>

<template>
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div><h1 class="text-2xl font-bold">Categories</h1></div>
            <AppButton v-if="can('categories.create')" @click="openCreate">Add Category</AppButton>
        </div>
        <DataTable :columns="[{key:'name',label:'Name',sortable:true}]" :rows="items" :loading="loading" :meta="meta" :sort="params.sort" searchable search-placeholder="Search name" @page="goToPage" @search="setSearch" @sort="setSort">
            <template #actions="{ row }">
                <AppButton v-if="can('categories.update')" size="sm" variant="secondary" @click="openEdit(row)">Edit</AppButton>
                <span v-else aria-hidden="true" />
                <AppButton v-if="can('categories.delete')" size="sm" variant="danger" @click="remove(row)">Delete</AppButton>
                <span v-else aria-hidden="true" />
            </template>
        </DataTable>
        <Modal :open="showModal" title="Category" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="save">
                <FormField v-model="form.name" label="Name" required />
                <FormField v-model="form.parent_id" label="Parent" type="select">
                    <option value="">None</option>
                    <option v-for="parent in parents.filter((item) => item.id !== editing?.id)" :key="parent.id" :value="parent.id">{{ parent.name }}</option>
                </FormField>
                <div class="flex justify-end gap-2 pt-2">
                    <AppButton variant="secondary" @click="showModal = false">Cancel</AppButton>
                    <AppButton type="submit" :loading="saving">Save</AppButton>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
