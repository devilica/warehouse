<script setup>
import { onMounted } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import { auditApi } from '@/api';
import { usePagination } from '@/composables/usePagination';

const { items, meta, loading, load, goToPage } = usePagination(auditApi.list);
onMounted(load);
</script>

<template>
    <AdminLayout>
        <div class="mb-6"><h1 class="text-2xl font-bold">Audit Log</h1></div>
        <DataTable :columns="[{key:'event',label:'Event'},{key:'subject_type',label:'Subject'},{key:'created_at',label:'When'}]" :rows="items" :loading="loading" :meta="meta" @page="goToPage" />
    </AdminLayout>
</template>
