<script setup>
import { onMounted } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Badge from '@/components/ui/Badge.vue';
import { inventoryApi } from '@/api';
import { usePagination } from '@/composables/usePagination';

const { items, meta, loading, params, load, goToPage, setSort } = usePagination(inventoryApi.stockLevels, { include: 'product,warehouse' });
onMounted(load);
</script>

<template>
    <AdminLayout>
        <div class="mb-6"><h1 class="text-2xl font-bold">Stock Levels</h1></div>
        <DataTable :columns="[{key:'product',label:'Product'},{key:'warehouse',label:'Warehouse'},{key:'quantity',label:'Qty',sortable:true}]" :rows="items" :loading="loading" :meta="meta" :sort="params.sort" @page="goToPage" @sort="setSort">
            <template #cell-product="{ row }">{{ row.product?.name ?? row.product_id }}</template>
            <template #cell-warehouse="{ row }">{{ row.warehouse?.name ?? row.warehouse_id }}</template>
            <template #cell-quantity="{ row }"><Badge variant="info">{{ row.quantity }}</Badge></template>
        </DataTable>
    </AdminLayout>
</template>
