<script setup>
import { onMounted } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Badge from '@/components/ui/Badge.vue';
import { inventoryApi } from '@/api';
import { usePagination } from '@/composables/usePagination';

const { items, meta, loading, load, goToPage } = usePagination(inventoryApi.stockLevels);
onMounted(load);
</script>

<template>
    <AdminLayout>
        <div class="mb-6"><h1 class="text-2xl font-bold">Stock Levels</h1></div>
        <DataTable :columns="[{key:'product_id',label:'Product'},{key:'warehouse_id',label:'Warehouse'},{key:'quantity',label:'Qty'}]" :rows="items" :loading="loading" :meta="meta" @page="goToPage">
            <template #cell-quantity="{ row }"><Badge variant="info">{{ row.quantity }}</Badge></template>
        </DataTable>
    </AdminLayout>
</template>
