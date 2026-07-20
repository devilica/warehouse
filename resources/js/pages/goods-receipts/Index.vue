<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Modal from '@/components/ui/Modal.vue';
import FormField from '@/components/ui/FormField.vue';
import AppButton from '@/components/ui/AppButton.vue';
import Badge from '@/components/ui/Badge.vue';
import { goodsReceiptsApi } from '@/api';
import { usePagination } from '@/composables/usePagination';
import { useToastStore } from '@/stores/toast';
import { usePermission } from '@/composables/usePermission';

const toast = useToastStore();
const { can } = usePermission();
const { items, meta, loading, load, goToPage } = usePagination(goodsReceiptsApi.list);
const showModal = ref(false);
const form = ref({ purchase_order_id: '', warehouse_id: '' });

onMounted(load);
async function create() { await goodsReceiptsApi.create(form.value); toast.success('Receipt created'); showModal.value = false; await load(); }
async function confirm(row) { await goodsReceiptsApi.confirm(row.id); toast.success('Receipt confirmed'); await load(); }
</script>

<template>
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div><h1 class="text-2xl font-bold">Goods Receipts</h1></div>
            <AppButton v-if="can('goods-receipts.create')" @click="showModal = true">Create Receipt</AppButton>
        </div>
        <DataTable :columns="[{key:'id',label:'#'},{key:'status',label:'Status'}]" :rows="items" :loading="loading" :meta="meta" @page="goToPage">
            <template #cell-status="{ row }"><Badge>{{ row.status }}</Badge></template>
            <template #actions="{ row }">
                <AppButton v-if="can('goods-receipts.confirm')" size="sm" @click="confirm(row)">Confirm</AppButton>
            </template>
        </DataTable>
        <Modal :open="showModal" title="Create Goods Receipt" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="create">
                <FormField v-model="form.purchase_order_id" label="Purchase Order ID" required />
                <FormField v-model="form.warehouse_id" label="Warehouse ID" required />
                <AppButton type="submit">Create</AppButton>
            </form>
        </Modal>
    </AdminLayout>
</template>
