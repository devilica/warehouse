<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import AppButton from '@/components/ui/AppButton.vue';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();

const reports = [
    { type: 'inventory-valuation', label: 'Inventory Valuation' },
    { type: 'stock-movements', label: 'Stock Movements' },
    { type: 'purchase-history', label: 'Purchase History' },
    { type: 'low-stock', label: 'Low Stock' },
    { type: 'product-history', label: 'Product History' },
];

async function download(type, format) {
    const token = localStorage.getItem('wms_token');
    try {
        const response = await fetch(`/api/v1/reports/${type}?format=${format}`, {
            headers: { Authorization: `Bearer ${token}`, Accept: 'application/octet-stream' },
        });
        if (!response.ok) throw new Error('Download failed');
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${type}.${format}`;
        a.click();
        window.URL.revokeObjectURL(url);
    } catch {
        toast.error('Failed to download report');
    }
}
</script>

<template>
    <AdminLayout>
        <div class="mb-6"><h1 class="text-2xl font-bold">Reports</h1></div>
        <div class="grid gap-4 md:grid-cols-2">
            <div v-for="report in reports" :key="report.type" class="rounded-2xl border bg-white p-5 shadow-sm">
                <h2 class="font-semibold">{{ report.label }}</h2>
                <div class="mt-4 flex gap-2">
                    <AppButton size="sm" @click="download(report.type, 'pdf')">PDF</AppButton>
                    <AppButton size="sm" variant="secondary" @click="download(report.type, 'xlsx')">Excel</AppButton>
                    <AppButton size="sm" variant="ghost" @click="download(report.type, 'csv')">CSV</AppButton>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
