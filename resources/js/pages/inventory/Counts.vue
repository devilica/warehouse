<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Modal from '@/components/ui/Modal.vue';
import FormField from '@/components/ui/FormField.vue';
import AppButton from '@/components/ui/AppButton.vue';
import Badge from '@/components/ui/Badge.vue';
import { countsApi } from '@/api';
import { usePagination } from '@/composables/usePagination';
import { useToastStore } from '@/stores/toast';
import { usePermission } from '@/composables/usePermission';

const router = useRouter();
const toast = useToastStore();
const { can } = usePermission();
const { items, meta, loading, load, goToPage } = usePagination(countsApi.list);
const showModal = ref(false);
const form = ref({ warehouse_id: '' });

onMounted(load);
async function create() { await countsApi.create(form.value); toast.success('Count created'); showModal.value = false; await load(); }
async function start(row) { await countsApi.start(row.id); toast.success('Count started'); await load(); }
async function finalize(row) { await countsApi.finalize(row.id); toast.success('Count finalized'); await load(); }
</script>

<template>
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div><h1 class="text-2xl font-bold">Inventory Counts</h1></div>
            <AppButton v-if="can('inventory-counts.create')" @click="showModal = true">New Count</AppButton>
        </div>
        <DataTable :columns="[{key:'id',label:'#'},{key:'status',label:'Status'}]" :rows="items" :loading="loading" :meta="meta" @page="goToPage">
            <template #cell-status="{ row }"><Badge>{{ row.status }}</Badge></template>
            <template #actions="{ row }">
                <AppButton size="sm" variant="secondary" @click="router.push(`/inventory/counts/${row.id}`)">View</AppButton>
                <AppButton v-if="can('inventory-counts.start')" size="sm" @click="start(row)">Start</AppButton>
                <AppButton v-if="can('inventory-counts.finalize')" size="sm" @click="finalize(row)">Finalize</AppButton>
            </template>
        </DataTable>
        <Modal :open="showModal" title="Create Inventory Count" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="create">
                <FormField v-model="form.warehouse_id" label="Warehouse ID" required />
                <AppButton type="submit">Create</AppButton>
            </form>
        </Modal>
    </AdminLayout>
</template>
