<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { dashboardApi } from '@/api';

const summary = ref(null);
const activity = ref([]);
const warehouseStats = ref([]);
const loading = ref(true);

onMounted(async () => {
    try {
        [summary.value, activity.value, warehouseStats.value] = await Promise.all([
            dashboardApi.summary(),
            dashboardApi.recentActivity(),
            dashboardApi.warehouseStats(),
        ]);
    } finally {
        loading.value = false;
    }
});

function money(v) {
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'EUR' }).format(v ?? 0);
}
</script>

<template>
    <AdminLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
            <p class="text-sm text-slate-500">Warehouse overview and recent activity</p>
        </div>

        <div v-if="loading" class="text-slate-400">Loading dashboard...</div>
        <template v-else>
            <div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm text-slate-500">Stock Value</div>
                    <div class="mt-2 text-2xl font-bold text-slate-900">{{ money(summary?.stock_value) }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm text-slate-500">Low Stock Items</div>
                    <div class="mt-2 text-2xl font-bold text-amber-600">{{ summary?.low_stock_count ?? 0 }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm text-slate-500">Out of Stock</div>
                    <div class="mt-2 text-2xl font-bold text-red-600">{{ summary?.out_of_stock_count ?? 0 }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm text-slate-500">Pending POs</div>
                    <div class="mt-2 text-2xl font-bold text-indigo-600">{{ summary?.pending_purchase_orders ?? 0 }}</div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 font-semibold text-slate-900">Recent Activity</h2>
                    <div class="space-y-3">
                        <div v-for="item in activity.slice(0, 8)" :key="item.id" class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm">
                            <span>{{ item.product?.name ?? 'Movement' }}</span>
                            <span class="text-slate-400">{{ item.type }}</span>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 font-semibold text-slate-900">Warehouse Stats</h2>
                    <div class="space-y-3">
                        <div v-for="wh in warehouseStats" :key="wh.warehouse_id" class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm">
                            <span>{{ wh.warehouse?.name ?? `Warehouse #${wh.warehouse_id}` }}</span>
                            <span class="font-medium">{{ wh.total_quantity }} units</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AdminLayout>
</template>
