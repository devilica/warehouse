<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Modal from '@/components/ui/Modal.vue';
import FormField from '@/components/ui/FormField.vue';
import AppButton from '@/components/ui/AppButton.vue';
import Badge from '@/components/ui/Badge.vue';
import { adjustmentsApi } from '@/api';
import { usePagination } from '@/composables/usePagination';
import { useToastStore } from '@/stores/toast';
import { usePermission } from '@/composables/usePermission';

const toast = useToastStore();
const { can } = usePermission();
const { items, meta, loading, load, goToPage } = usePagination(adjustmentsApi.list);
const showModal = ref(false);
const form = ref({ warehouse_id: '', reason: '' });

onMounted(load);
async function create() { await adjustmentsApi.create(form.value); toast.success('Adjustment created'); showModal.value = false; await load(); }
async function approve(row) { await adjustmentsApi.approve(row.id); toast.success('Approved'); await load(); }
</script>

<template>
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div><h1 class="text-2xl font-bold">Inventory Adjustments</h1></div>
            <AppButton v-if="can('inventory-adjustments.create')" @click="showModal = true">New Adjustment</AppButton>
        </div>
        <DataTable :columns="[{key:'id',label:'#'},{key:'status',label:'Status'},{key:'reason',label:'Reason'}]" :rows="items" :loading="loading" :meta="meta" @page="goToPage">
            <template #cell-status="{ row }"><Badge>{{ row.status }}</Badge></template>
            <template #actions="{ row }">
                <AppButton v-if="can('inventory-adjustments.approve')" size="sm" @click="approve(row)">Approve</AppButton>
            </template>
        </DataTable>
        <Modal :open="showModal" title="Create Adjustment" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="create">
                <FormField v-model="form.warehouse_id" label="Warehouse ID" required />
                <FormField v-model="form.reason" label="Reason" type="textarea" />
                <AppButton type="submit">Create</AppButton>
            </form>
        </Modal>
    </AdminLayout>
</template>
