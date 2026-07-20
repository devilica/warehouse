<script setup>
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    labels: { type: Array, default: () => [] },
    unitsOrdered: { type: Array, default: () => [] },
    orderCount: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
});

const chartSeries = computed(() => [
    { name: 'Units Ordered', type: 'area', data: props.unitsOrdered },
    { name: 'Purchase Orders', type: 'line', data: props.orderCount },
]);

const chartOptions = computed(() => ({
    chart: {
        type: 'line',
        height: 320,
        toolbar: { show: false },
        fontFamily: 'inherit',
        animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 600,
        },
    },
    colors: ['#6366f1', '#f59e0b'],
    stroke: {
        curve: 'smooth',
        width: [0, 3],
    },
    fill: {
        type: ['gradient', 'solid'],
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [0, 90, 100],
        },
    },
    dataLabels: { enabled: false },
    grid: {
        borderColor: '#e2e8f0',
        strokeDashArray: 4,
        xaxis: { lines: { show: false } },
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right',
        fontSize: '13px',
        labels: { colors: '#64748b' },
        markers: { radius: 12 },
    },
    xaxis: {
        categories: props.labels,
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
            style: { colors: '#94a3b8', fontSize: '12px' },
            rotate: -45,
            rotateAlways: props.labels.length > 14,
        },
    },
    yaxis: [
        {
            title: { text: 'Units', style: { color: '#94a3b8', fontWeight: 500 } },
            labels: { style: { colors: '#94a3b8' } },
        },
        {
            opposite: true,
            title: { text: 'Orders', style: { color: '#94a3b8', fontWeight: 500 } },
            labels: { style: { colors: '#94a3b8' } },
        },
    ],
    tooltip: {
        shared: true,
        intersect: false,
        theme: 'light',
        y: {
            formatter: (value, { seriesIndex }) => {
                if (seriesIndex === 0) {
                    return new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 }).format(value ?? 0);
                }
                return `${value ?? 0} orders`;
            },
        },
    },
}));
</script>

<template>
    <div class="relative min-h-[320px]">
        <div v-if="loading" class="absolute inset-0 flex items-center justify-center text-sm text-slate-400">
            Loading chart...
        </div>
        <VueApexCharts
            v-else
            type="line"
            height="320"
            :options="chartOptions"
            :series="chartSeries"
        />
    </div>
</template>
