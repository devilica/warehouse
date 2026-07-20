<script setup>
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { transfersApi } from '@/api';

const route = useRoute();
const item = ref(null);
onMounted(async () => { item.value = await transfersApi.get(route.params.id); });
</script>

<template>
    <AdminLayout>
        <div v-if="item" class="rounded-2xl border bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold">Transfer #{{ item.id }}</h1>
            <pre class="mt-4 overflow-auto rounded-xl bg-slate-50 p-4 text-xs">{{ item }}</pre>
        </div>
    </AdminLayout>
</template>
