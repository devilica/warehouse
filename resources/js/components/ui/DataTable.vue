<script setup>
import { computed, ref, watch } from 'vue';
import AppButton from '@/components/ui/AppButton.vue';

const props = defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    meta: { type: Object, default: () => ({}) },
    searchable: { type: Boolean, default: false },
    searchPlaceholder: { type: String, default: 'Search...' },
    statuses: { type: Array, default: () => [] },
    sort: { type: String, default: '' },
});

const emit = defineEmits(['page', 'search', 'sort', 'status']);

const search = ref('');
const currentPage = computed(() => props.meta?.current_page ?? 1);
const lastPage = computed(() => props.meta?.last_page ?? 1);
const total = computed(() => props.meta?.total ?? props.rows.length);
let searchTimer;

watch(search, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => emit('search', value), 300);
});

function sortMark(key) {
    if (props.sort === key) return '↑';
    if (props.sort === `-${key}`) return '↓';
    return '↕';
}
</script>

<template>
    <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
        <div v-if="searchable || statuses.length" class="flex flex-col gap-3 border-b border-slate-100 px-4 py-3 sm:flex-row sm:items-center">
            <input
                v-if="searchable"
                v-model="search"
                type="search"
                :placeholder="searchPlaceholder"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 sm:max-w-xs"
            />
            <select
                v-if="statuses.length"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                @change="emit('status', $event.target.value)"
            >
                <option value="">All statuses</option>
                <option v-for="status in statuses" :key="status" :value="status">{{ status.replaceAll('_', ' ') }}</option>
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                            :class="col.sortable ? 'cursor-pointer select-none hover:text-slate-800' : ''"
                            @click="col.sortable && emit('sort', col.key)"
                        >
                            <span class="inline-flex items-center gap-1">
                                {{ col.label }}
                                <span v-if="col.sortable" class="text-[10px] text-indigo-500">{{ sortMark(col.key) }}</span>
                            </span>
                        </th>
                        <th v-if="$slots.actions" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="loading">
                        <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-4 py-10 text-center text-slate-400">Loading...</td>
                    </tr>
                    <tr v-else-if="!rows.length">
                        <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-4 py-10 text-center text-slate-400">No records found</td>
                    </tr>
                    <tr v-for="row in rows" :key="row.id" class="transition hover:bg-indigo-50/40">
                        <td v-for="col in columns" :key="col.key" class="px-4 py-3 text-slate-700">
                            <slot :name="`cell-${col.key}`" :row="row">
                                {{ row[col.key] ?? '—' }}
                            </slot>
                        </td>
                        <td v-if="$slots.actions" class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                <slot name="actions" :row="row" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="lastPage > 1" class="flex items-center justify-between border-t border-slate-100 px-4 py-3 text-sm text-slate-500">
            <span>Page {{ currentPage }} of {{ lastPage }} · {{ total }} records</span>
            <div class="flex gap-2">
                <AppButton size="sm" variant="secondary" :disabled="currentPage <= 1" @click="emit('page', currentPage - 1)">Prev</AppButton>
                <AppButton size="sm" variant="secondary" :disabled="currentPage >= lastPage" @click="emit('page', currentPage + 1)">Next</AppButton>
            </div>
        </div>
    </div>
</template>
