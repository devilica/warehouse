<script setup>
import { onMounted } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import { auditApi } from '@/api';
import { usePagination } from '@/composables/usePagination';

const { items, meta, loading, params, load, goToPage, setSort } = usePagination(auditApi.list);
onMounted(load);
</script>

<template>
    <AdminLayout>
        <div class="mb-6"><h1 class="text-2xl font-bold">Audit Log</h1></div>
        <DataTable :columns="[{key:'action',label:'Event'},{key:'auditable_type',label:'Subject'},{key:'created_at',label:'When',sortable:true}]" :rows="items" :loading="loading" :meta="meta" :sort="params.sort" @page="goToPage" @sort="setSort" />
    </AdminLayout>
</template>
