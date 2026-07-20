<script setup>
import { computed } from 'vue';

const props = defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    meta: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['page']);

const currentPage = computed(() => props.meta?.current_page ?? 1);
const lastPage = computed(() => props.meta?.last_page ?? 1);
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            {{ col.label }}
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
                    <tr v-for="row in rows" :key="row.id" class="hover:bg-slate-50/80">
                        <td v-for="col in columns" :key="col.key" class="px-4 py-3 text-slate-700">
                            <slot :name="`cell-${col.key}`" :row="row">
                                {{ row[col.key] ?? '—' }}
                            </slot>
                        </td>
                        <td v-if="$slots.actions" class="px-4 py-3 text-right">
                            <slot name="actions" :row="row" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="lastPage > 1" class="flex items-center justify-between border-t border-slate-100 px-4 py-3 text-sm text-slate-500">
            <span>Page {{ currentPage }} of {{ lastPage }}</span>
            <div class="flex gap-2">
                <button class="rounded-lg px-3 py-1 hover:bg-slate-100 disabled:opacity-40" :disabled="currentPage <= 1" @click="emit('page', currentPage - 1)">Prev</button>
                <button class="rounded-lg px-3 py-1 hover:bg-slate-100 disabled:opacity-40" :disabled="currentPage >= lastPage" @click="emit('page', currentPage + 1)">Next</button>
            </div>
        </div>
    </div>
</template>
