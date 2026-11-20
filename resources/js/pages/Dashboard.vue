<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { RouterLink } from 'vue-router';
import VueApexCharts from 'vue3-apexcharts';
import AdminLayout from '@/layouts/AdminLayout.vue';
import TrendChart from '@/components/ui/TrendChart.vue';
import Skeleton from '@/components/ui/Skeleton.vue';
import { dashboardApi } from '@/api';

const summary = ref(null);
const activity = ref([]);
const warehouseStats = ref([]);
const orderTrends = ref(null);
const trendPeriod = ref('day');
const loading = ref(true);
const trendsLoading = ref(false);

const rangeLabel = computed(() => (trendPeriod.value === 'month' ? 'Last 12 months' : 'Last 30 days'));

const health = computed(() => {
    const healthy = Number(summary.value?.in_stock_count ?? 0);
    const low = Number(summary.value?.low_stock_count ?? 0);
    const out = Number(summary.value?.out_of_stock_count ?? 0);
    const total = healthy + low + out;
    const share = (count) => (total ? Math.round((count / total) * 100) : 0);
    return {
        healthy,
        low,
        out,
        total,
        pct: share(healthy),
        rows: [
            { label: 'In Stock', count: healthy, pct: share(healthy), dot: 'bg-emerald-500' },
            { label: 'Low Stock', count: low, pct: share(low), dot: 'bg-amber-500' },
            { label: 'Out of Stock', count: out, pct: share(out), dot: 'bg-red-500' },
        ],
    };
});

const healthSeries = computed(() => [health.value.healthy, health.value.low, health.value.out]);

const healthOptions = computed(() => ({
    chart: { type: 'donut', fontFamily: 'inherit' },
    labels: ['In Stock', 'Low Stock', 'Out of Stock'],
    colors: ['#22c55e', '#f59e0b', '#ef4444'],
    stroke: { width: 0 },
    dataLabels: { enabled: false },
    legend: { show: false },
    plotOptions: {
        pie: {
            donut: {
                size: '74%',
                labels: {
                    show: true,
                    name: { show: true, fontSize: '13px', color: '#64748b' },
                    value: { show: true, fontSize: '28px', fontWeight: 700, color: '#0f172a', formatter: () => `${health.value.pct}%` },
                    total: {
                        show: true,
                        label: 'In Stock',
                        color: '#64748b',
                        formatter: () => `${health.value.pct}%`,
                    },
                },
            },
        },
    },
}));

const maxWarehouseQty = computed(() => Math.max(1, ...warehouseStats.value.map((row) => Number(row.total_quantity) || 0)));

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

function money(value) {
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'EUR' }).format(value ?? 0);
}

function formatNumber(value) {
    return new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 }).format(value ?? 0);
}

function timeAgo(value) {
    if (!value) return '';
    const seconds = Math.max(0, (Date.now() - new Date(value).getTime()) / 1000);
    if (seconds < 60) return 'just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes} min ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} h ago`;
    return `${Math.floor(hours / 24)} d ago`;
}

function activityLabel(type) {
    return (type ?? 'movement').replaceAll('_', ' ');
}

function activityTone(type) {
    if (type === 'purchase') return 'bg-emerald-50 text-emerald-600';
    if (String(type ?? '').startsWith('transfer')) return 'bg-indigo-50 text-indigo-600';
    return 'bg-amber-50 text-amber-600';
}

function warehouseShare(row) {
    return Math.round(((Number(row.total_quantity) || 0) / maxWarehouseQty.value) * 100);
}
</script>

<template>
    <AdminLayout>
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard</h1>
                <p class="text-sm text-slate-500">Warehouse overview and recent activity</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm">
                {{ rangeLabel }}
            </div>
        </div>

        <div v-if="loading" aria-busy="true">
            <span class="sr-only">Loading</span>
            <div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div v-for="n in 4" :key="n" class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                    <Skeleton class="h-4 w-24" />
                    <Skeleton class="mt-4 h-8 w-32" />
                </div>
            </div>
            <div class="mb-6 grid gap-6 xl:grid-cols-3">
                <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm xl:col-span-2">
                    <Skeleton class="h-5 w-40" />
                    <Skeleton class="mt-2 h-4 w-56" />
                    <div class="mt-8 flex h-64 items-end gap-2">
                        <Skeleton v-for="(height, index) in [40, 65, 50, 80, 45, 90, 60, 75]" :key="index" class="min-w-0 flex-1 rounded-t-xl" :style="{ height: `${height}%` }" />
                    </div>
                </div>
                <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                    <Skeleton class="h-5 w-36" />
                    <Skeleton class="mx-auto mt-6 h-40 w-40 rounded-full" />
                    <div class="mt-6 space-y-3">
                        <Skeleton v-for="n in 3" :key="n" class="h-4 w-full" />
                    </div>
                </div>
            </div>
            <div class="grid gap-6 xl:grid-cols-2">
                <div v-for="panel in 2" :key="panel" class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                    <Skeleton class="h-5 w-36" />
                    <div class="mt-5 space-y-4">
                        <div v-for="n in 5" :key="n" class="flex items-center gap-3">
                            <Skeleton class="h-10 w-10 shrink-0 rounded-2xl" />
                            <div class="min-w-0 flex-1 space-y-2">
                                <Skeleton class="h-4 w-2/3" />
                                <Skeleton class="h-3 w-1/3" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <template v-else>
            <div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-violet-600 to-indigo-700 p-5 text-white shadow-lg shadow-indigo-200">
                    <div class="text-sm text-indigo-100">Stock Value</div>
                    <div class="mt-3 text-3xl font-bold tracking-tight">{{ money(summary?.stock_value) }}</div>
                    <svg class="absolute -right-2 bottom-0 h-24 w-24 text-white/20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2 3 7v10l9 5 9-5V7z" />
                    </svg>
                </div>
                <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-50 text-amber-500">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5M12 16.5v.5" /><path stroke-linecap="round" stroke-linejoin="round" d="M10.3 4.5 2.8 18a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3L13.7 4.5a2 2 0 0 0-3.4 0z" /></svg>
                        </span>
                        <span class="text-sm text-slate-500">Low Stock Items</span>
                    </div>
                    <div class="mt-4 text-3xl font-bold text-slate-900">{{ summary?.low_stock_count ?? 0 }}</div>
                </div>
                <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-red-50 text-red-500">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" d="M12 8v5M12 16.5v.5" /></svg>
                        </span>
                        <span class="text-sm text-slate-500">Out of Stock</span>
                    </div>
                    <div class="mt-4 text-3xl font-bold text-slate-900">{{ summary?.out_of_stock_count ?? 0 }}</div>
                </div>
                <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h15l-1.6 8H7.2L5.5 3H3" /><circle cx="9" cy="20" r="1.2" /><circle cx="17" cy="20" r="1.2" /></svg>
                        </span>
                        <span class="text-sm text-slate-500">Pending POs</span>
                    </div>
                    <div class="mt-4 text-3xl font-bold text-slate-900">{{ summary?.pending_purchase_orders ?? 0 }}</div>
                </div>
            </div>

            <div class="mb-6 grid gap-6 xl:grid-cols-3">
                <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm xl:col-span-2">
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
                        <div class="inline-flex rounded-full bg-slate-100 p-1">
                            <button
                                type="button"
                                class="rounded-full px-4 py-1.5 text-sm font-medium transition"
                                :class="trendPeriod === 'day' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                                @click="trendPeriod = 'day'"
                            >
                                Daily
                            </button>
                            <button
                                type="button"
                                class="rounded-full px-4 py-1.5 text-sm font-medium transition"
                                :class="trendPeriod === 'month' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
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

                <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold text-slate-900">Inventory Health</h2>
                    <p class="mt-1 text-sm text-slate-500">Products by stock status</p>
                    <div v-if="health.total" class="mt-2">
                        <VueApexCharts type="donut" height="230" :options="healthOptions" :series="healthSeries" />
                        <ul class="space-y-2">
                            <li v-for="row in health.rows" :key="row.label" class="flex items-center gap-2 text-sm">
                                <span class="h-2.5 w-2.5 rounded-full" :class="row.dot" />
                                <span class="text-slate-600">{{ row.label }}</span>
                                <span class="ml-auto font-medium text-slate-900">{{ formatNumber(row.count) }}</span>
                                <span class="w-12 text-right text-slate-400">{{ row.pct }}%</span>
                            </li>
                        </ul>
                    </div>
                    <p v-else class="mt-8 text-sm text-slate-400">No products to chart yet.</p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="font-semibold text-slate-900">Recent Activity</h2>
                        <RouterLink to="/inventory/transactions" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View All</RouterLink>
                    </div>
                    <div class="space-y-3">
                        <div v-for="item in activity.slice(0, 5)" :key="item.id" class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" :class="activityTone(item.type)">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 12h10M13 8l4 4-4 4" />
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-medium text-slate-900">{{ item.product?.name ?? 'Stock movement' }}</div>
                                <div class="truncate text-xs capitalize text-slate-400">{{ activityLabel(item.type) }}</div>
                            </div>
                            <div class="shrink-0 text-xs text-slate-400">{{ timeAgo(item.created_at) }}</div>
                        </div>
                        <p v-if="!activity.length" class="text-sm text-slate-400">No recent movements.</p>
                    </div>
                </div>
                <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="font-semibold text-slate-900">Warehouse Stats</h2>
                        <RouterLink to="/warehouses" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View All</RouterLink>
                    </div>
                    <div class="space-y-4">
                        <div v-for="wh in warehouseStats.slice(0, 5)" :key="wh.warehouse_id">
                            <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                                <span class="truncate font-medium text-slate-800">{{ wh.warehouse?.name ?? `Warehouse #${wh.warehouse_id}` }}</span>
                                <span class="shrink-0 text-slate-500">{{ formatNumber(wh.total_quantity) }} units</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-400" :style="{ width: `${warehouseShare(wh)}%` }" />
                            </div>
                        </div>
                        <p v-if="!warehouseStats.length" class="text-sm text-slate-400">No warehouse stock yet.</p>
                    </div>
                </div>
            </div>
        </template>
    </AdminLayout>
</template>
