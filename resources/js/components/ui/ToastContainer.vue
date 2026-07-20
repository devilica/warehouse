<script setup>
import { storeToRefs } from 'pinia';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();
const { items } = storeToRefs(toast);
</script>

<template>
    <div class="fixed right-4 top-4 z-[100] flex flex-col gap-2">
        <div
            v-for="item in items"
            :key="item.id"
            class="min-w-[280px] rounded-xl px-4 py-3 text-sm font-medium shadow-lg"
            :class="item.type === 'error' ? 'bg-red-600 text-white' : 'bg-slate-900 text-white'"
        >
            {{ item.message }}
        </div>
    </div>
</template>
