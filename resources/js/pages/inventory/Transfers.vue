<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Modal from '@/components/ui/Modal.vue';
import FormField from '@/components/ui/FormField.vue';
import AppButton from '@/components/ui/AppButton.vue';
import Badge from '@/components/ui/Badge.vue';
import { transfersApi } from '@/api';
import { usePagination } from '@/composables/usePagination';
import { useToastStore } from '@/stores/toast';
import { usePermission } from '@/composables/usePermission';

const router = useRouter();
const toast = useToastStore();
const { can } = usePermission();
const { items, meta, loading, load, goToPage } = usePagination(transfersApi.list);
const showModal = ref(false);
const form = ref({ from_warehouse_id: '', to_warehouse_id: '' });

onMounted(load);
async function create() { await transfersApi.create(form.value); toast.success('Transfer created'); showModal.value = false; await load(); }
async function action(fn, row, msg) { await fn(row.id); toast.success(msg); await load(); }
</script>

<template>
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div><h1 class="text-2xl font-bold">Stock Transfers</h1></div>
            <AppButton v-if="can('stock-transfers.create')" @click="showModal = true">New Transfer</AppButton>
        </div>
        <DataTable :columns="[{key:'id',label:'#'},{key:'status',label:'Status'}]" :rows="items" :loading="loading" :meta="meta" @page="goToPage">
            <template #cell-status="{ row }"><Badge>{{ row.status }}</Badge></template>
            <template #actions="{ row }">
                <AppButton size="sm" variant="secondary" @click="router.push(`/inventory/transfers/${row.id}`)">View</AppButton>
                <AppButton v-if="can('stock-transfers.update')" size="sm" @click="action(transfersApi.approve, row, 'Approved')">Approve</AppButton>
                <AppButton v-if="can('stock-transfers.update')" size="sm" @click="action(transfersApi.ship, row, 'Shipped')">Ship</AppButton>
                <AppButton v-if="can('stock-transfers.update')" size="sm" @click="action(transfersApi.receive, row, 'Received')">Receive</AppButton>
                <AppButton v-if="can('stock-transfers.update')" size="sm" @click="action(transfersApi.complete, row, 'Completed')">Complete</AppButton>
            </template>
        </DataTable>
        <Modal :open="showModal" title="Create Transfer" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="create">
                <FormField v-model="form.from_warehouse_id" label="From Warehouse ID" required />
                <FormField v-model="form.to_warehouse_id" label="To Warehouse ID" required />
                <AppButton type="submit">Create</AppButton>
            </form>
        </Modal>
    </AdminLayout>
</template>
