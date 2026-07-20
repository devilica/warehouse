<script setup>
import { onMounted } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Badge from '@/components/ui/Badge.vue';
import { inventoryApi } from '@/api';
import { usePagination } from '@/composables/usePagination';

const { items, meta, loading, load, goToPage } = usePagination(inventoryApi.transactions);
onMounted(load);
</script>

<template>
    <AdminLayout>
        <div class="mb-6"><h1 class="text-2xl font-bold">Inventory Transactions</h1></div>
        <DataTable :columns="[{key:'type',label:'Type'},{key:'product_id',label:'Product'},{key:'quantity',label:'Qty'},{key:'created_at',label:'Date'}]" :rows="items" :loading="loading" :meta="meta" @page="goToPage">
            <template #cell-type="{ row }"><Badge>{{ row.type }}</Badge></template>
        </DataTable>
    </AdminLayout>
</template>
