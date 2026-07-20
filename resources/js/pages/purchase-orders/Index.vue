<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Modal from '@/components/ui/Modal.vue';
import FormField from '@/components/ui/FormField.vue';
import AppButton from '@/components/ui/AppButton.vue';
import Badge from '@/components/ui/Badge.vue';
import { purchaseOrdersApi } from '@/api';
import { usePagination } from '@/composables/usePagination';
import { useToastStore } from '@/stores/toast';
import { usePermission } from '@/composables/usePermission';

const router = useRouter();
const toast = useToastStore();
const { can } = usePermission();
const { items, meta, loading, load, goToPage } = usePagination(purchaseOrdersApi.list);
const showModal = ref(false);
const form = ref({ supplier_id: '', expected_delivery_date: '' });

onMounted(load);

async function create() {
    await purchaseOrdersApi.create(form.value);
    toast.success('PO created');
    showModal.value = false;
    await load();
}

async function send(row) { await purchaseOrdersApi.send(row.id); toast.success('PO sent'); await load(); }
async function close(row) { await purchaseOrdersApi.close(row.id); toast.success('PO closed'); await load(); }
</script>

<template>
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div><h1 class="text-2xl font-bold">Purchase Orders</h1></div>
            <AppButton v-if="can('purchase-orders.create')" @click="showModal = true">Create PO</AppButton>
        </div>
        <DataTable :columns="[{key:'id',label:'#'},{key:'status',label:'Status'},{key:'supplier_id',label:'Supplier'}]" :rows="items" :loading="loading" :meta="meta" @page="goToPage">
            <template #cell-status="{ row }"><Badge variant="info">{{ row.status }}</Badge></template>
            <template #actions="{ row }">
                <AppButton size="sm" variant="secondary" @click="router.push(`/purchase-orders/${row.id}`)">View</AppButton>
                <AppButton v-if="can('purchase-orders.update') && row.status === 'draft'" size="sm" @click="send(row)">Send</AppButton>
                <AppButton v-if="can('purchase-orders.update')" size="sm" variant="ghost" @click="close(row)">Close</AppButton>
            </template>
        </DataTable>
        <Modal :open="showModal" title="Create Purchase Order" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="create">
                <FormField v-model="form.supplier_id" label="Supplier ID" required />
                <FormField v-model="form.expected_delivery_date" label="Expected Delivery" type="date" />
                <AppButton type="submit">Create</AppButton>
            </form>
        </Modal>
    </AdminLayout>
</template>
