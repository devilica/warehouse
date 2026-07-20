<script setup>
import { onMounted } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import AppButton from '@/components/ui/AppButton.vue';
import Badge from '@/components/ui/Badge.vue';
import { notificationsApi } from '@/api';
import { usePagination } from '@/composables/usePagination';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();
const { items, meta, loading, load, goToPage } = usePagination(notificationsApi.list);
onMounted(load);

async function markAll() {
    await notificationsApi.markAllRead();
    toast.success('All notifications marked read');
    await load();
}
</script>

<template>
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div><h1 class="text-2xl font-bold">Notifications</h1></div>
            <AppButton variant="secondary" @click="markAll">Mark all read</AppButton>
        </div>
        <DataTable :columns="[{key:'data',label:'Message'},{key:'read_at',label:'Status'}]" :rows="items" :loading="loading" :meta="meta" @page="goToPage">
            <template #cell-data="{ row }">{{ row.data?.message ?? row.type ?? 'Notification' }}</template>
            <template #cell-read_at="{ row }"><Badge :variant="row.read_at ? 'success' : 'warning'">{{ row.read_at ? 'Read' : 'Unread' }}</Badge></template>
        </DataTable>
    </AdminLayout>
</template>
