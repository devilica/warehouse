<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Modal from '@/components/ui/Modal.vue';
import FormField from '@/components/ui/FormField.vue';
import AppButton from '@/components/ui/AppButton.vue';
import Badge from '@/components/ui/Badge.vue';
import { transfersApi, warehousesApi } from '@/api';
import { unwrapPaginated } from '@/api/client';
import { usePagination } from '@/composables/usePagination';
import { useToastStore } from '@/stores/toast';
import { usePermission } from '@/composables/usePermission';

const router = useRouter();
const toast = useToastStore();
const { can } = usePermission();
const { items, meta, loading, params, load, goToPage, setSort, setStatus } = usePagination(transfersApi.list);
const statuses = ['draft', 'approved', 'shipped', 'received', 'completed', 'cancelled'];
const warehouses = ref([]);
const showModal = ref(false);
const form = ref({ from_warehouse_id: '', to_warehouse_id: '' });

onMounted(() => {
    load();
    warehousesApi.list({ per_page: 100 }).then((res) => { warehouses.value = unwrapPaginated(res).data; });
});
async function create() { await transfersApi.create(form.value); toast.success('Transfer created'); showModal.value = false; await load(); }
async function action(fn, row, msg) { await fn(row.id); toast.success(msg); await load(); }
</script>

<template>
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div><h1 class="text-2xl font-bold">Stock Transfers</h1></div>
            <AppButton v-if="can('stock-transfers.create')" @click="showModal = true">New Transfer</AppButton>
        </div>
        <DataTable :columns="[{key:'id',label:'#',sortable:true},{key:'status',label:'Status',sortable:true}]" :rows="items" :loading="loading" :meta="meta" :sort="params.sort" :statuses="statuses" @page="goToPage" @sort="setSort" @status="setStatus">
            <template #cell-status="{ row }"><Badge>{{ row.status }}</Badge></template>
            <template #actions="{ row }">
                <AppButton v-if="can('stock-transfers.update') && row.status === 'draft'" size="sm" @click="action(transfersApi.approve, row, 'Approved')">Approve</AppButton>
                <AppButton v-else-if="can('stock-transfers.update') && row.status === 'approved'" size="sm" @click="action(transfersApi.ship, row, 'Shipped')">Ship</AppButton>
                <AppButton v-else-if="can('stock-transfers.update') && row.status === 'shipped'" size="sm" @click="action(transfersApi.receive, row, 'Received')">Receive</AppButton>
                <AppButton v-else-if="can('stock-transfers.update') && row.status === 'received'" size="sm" @click="action(transfersApi.complete, row, 'Completed')">Complete</AppButton>
                <span v-else aria-hidden="true" />
                <AppButton size="sm" variant="secondary" @click="router.push(`/inventory/transfers/${row.id}`)">View</AppButton>
            </template>
        </DataTable>
        <Modal :open="showModal" title="Create Transfer" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="create">
                <FormField v-model="form.from_warehouse_id" label="From warehouse" type="select" required>
                    <option value="">Select a warehouse</option>
                    <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">{{ warehouse.name }}</option>
                </FormField>
                <FormField v-model="form.to_warehouse_id" label="To warehouse" type="select" required>
                    <option value="">Select a warehouse</option>
                    <option v-for="warehouse in warehouses" :key="`to-${warehouse.id}`" :value="warehouse.id">{{ warehouse.name }}</option>
                </FormField>
                <div class="flex justify-end gap-2 pt-2">
                    <AppButton variant="secondary" @click="showModal = false">Cancel</AppButton>
                    <AppButton type="submit">Create</AppButton>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
