<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Modal from '@/components/ui/Modal.vue';
import FormField from '@/components/ui/FormField.vue';
import AppButton from '@/components/ui/AppButton.vue';
import { productsApi, categoriesApi, suppliersApi } from '@/api';
import { unwrapPaginated } from '@/api/client';
import { usePagination } from '@/composables/usePagination';
import { useToastStore } from '@/stores/toast';
import { usePermission } from '@/composables/usePermission';

const toast = useToastStore();
const { can } = usePermission();
const { items, meta, loading, load, goToPage } = usePagination(productsApi.list);
const categories = ref([]);
const suppliers = ref([]);
const showModal = ref(false);
const editing = ref(null);
const form = ref({ name: '', sku: '', category_id: '', supplier_id: '' });
const saving = ref(false);

const columns = [
    { key: 'name', label: 'Product' },
    { key: 'sku', label: 'SKU' },
    { key: 'category_id', label: 'Category' },
];

onMounted(async () => {
    await load();
    categories.value = unwrapPaginated(await categoriesApi.list({ per_page: 100 })).data;
    suppliers.value = unwrapPaginated(await suppliersApi.list({ per_page: 100 })).data;
});

function openCreate() {
    editing.value = null;
    form.value = { name: '', sku: '', category_id: '', supplier_id: '' };
    showModal.value = true;
}

function openEdit(row) {
    editing.value = row;
    form.value = { name: row.name, sku: row.sku, category_id: row.category_id ?? '', supplier_id: row.supplier_id ?? '' };
    showModal.value = true;
}

async function save() {
    saving.value = true;
    try {
        if (editing.value) {
            await productsApi.update(editing.value.id, form.value);
            toast.success('Product updated');
        } else {
            await productsApi.create(form.value);
            toast.success('Product created');
        }
        showModal.value = false;
        await load();
    } catch {
        toast.error('Failed to save product');
    } finally {
        saving.value = false;
    }
}

async function remove(row) {
    if (!confirm(`Delete ${row.name}?`)) return;
    await productsApi.remove(row.id);
    toast.success('Product deleted');
    await load();
}
</script>

<template>
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Products</h1>
                <p class="text-sm text-slate-500">Product catalog and SKUs</p>
            </div>
            <AppButton v-if="can('products.create')" @click="openCreate">Add Product</AppButton>
        </div>
        <DataTable :columns="columns" :rows="items" :loading="loading" :meta="meta" @page="goToPage">
            <template #actions="{ row }">
                <div class="flex justify-end gap-2">
                    <AppButton v-if="can('products.update')" size="sm" variant="secondary" @click="openEdit(row)">Edit</AppButton>
                    <AppButton v-if="can('products.delete')" size="sm" variant="danger" @click="remove(row)">Delete</AppButton>
                </div>
            </template>
        </DataTable>
        <Modal :open="showModal" :title="editing ? 'Edit Product' : 'Create Product'" size="lg" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="save">
                <FormField v-model="form.name" label="Name" required />
                <FormField v-model="form.sku" label="SKU" required />
                <FormField v-model="form.category_id" label="Category" type="select">
                    <option value="">None</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </FormField>
                <FormField v-model="form.supplier_id" label="Supplier" type="select">
                    <option value="">None</option>
                    <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                </FormField>
                <div class="flex justify-end gap-2 pt-2">
                    <AppButton variant="secondary" @click="showModal = false">Cancel</AppButton>
                    <AppButton type="submit" :loading="saving">Save</AppButton>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
