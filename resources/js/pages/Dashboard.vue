<script setup>
import { onMounted, ref, watch } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import TrendChart from '@/components/ui/TrendChart.vue';
import { dashboardApi } from '@/api';

const summary = ref(null);
const activity = ref([]);
const warehouseStats = ref([]);
const orderTrends = ref(null);
const trendPeriod = ref('day');
const loading = ref(true);
const trendsLoading = ref(false);

async function loadTrends() {
    trendsLoading.value = true;
    try {
        orderTrends.value = await dashboardApi.orderTrends(trendPeriod.value);
    } finally {
        trendsLoading.value = false;
    }
}

onMounted(async () => {
    try {
        [summary.value, activity.value, warehouseStats.value, orderTrends.value] = await Promise.all([
            dashboardApi.summary(),
            dashboardApi.recentActivity(),
            dashboardApi.warehouseStats(),
            dashboardApi.orderTrends(trendPeriod.value),
        ]);
    } finally {
        loading.value = false;
    }
});

watch(trendPeriod, loadTrends);

function money(v) {
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'EUR' }).format(v ?? 0);
}

function formatNumber(v) {
    return new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 }).format(v ?? 0);
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

            <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="font-semibold text-slate-900">Procurement Trends</h2>
                        <p class="mt-1 text-sm text-slate-500">Product units ordered over time</p>
                        <div v-if="orderTrends?.totals" class="mt-3 flex flex-wrap gap-2">
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                                {{ formatNumber(orderTrends.totals.units) }} units ordered
                            </span>
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                                {{ formatNumber(orderTrends.totals.orders) }} purchase orders
                            </span>
                        </div>
                    </div>
                    <div class="inline-flex rounded-xl bg-slate-100 p-1">
                        <button
                            type="button"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
                            :class="trendPeriod === 'day' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                            @click="trendPeriod = 'day'"
                        >
                            Daily
                        </button>
                        <button
                            type="button"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
                            :class="trendPeriod === 'month' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                            @click="trendPeriod = 'month'"
                        >
                            Monthly
                        </button>
                    </div>
                </div>
                <TrendChart
                    :labels="orderTrends?.labels ?? []"
                    :units-ordered="orderTrends?.units_ordered ?? []"
                    :order-count="orderTrends?.order_count ?? []"
                    :loading="trendsLoading"
                />
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
