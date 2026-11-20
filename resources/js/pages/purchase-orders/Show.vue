<script setup>
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Badge from '@/components/ui/Badge.vue';
import { purchaseOrdersApi } from '@/api';

const route = useRoute();
const item = ref(null);
onMounted(async () => { item.value = await purchaseOrdersApi.get(route.params.id); });
</script>

<template>
    <AdminLayout>
        <div v-if="item" class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold">{{ item.number }}</h1>
                <p v-if="item.supplier?.company_name" class="mt-1 text-sm text-slate-500">{{ item.supplier.company_name }}</p>
                <Badge class="mt-3">{{ item.status }}</Badge>
            </div>
            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Ordered</th>
                            <th class="px-4 py-3">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="line in item.items ?? []" :key="line.id" class="border-t border-slate-100">
                            <td class="px-4 py-3">{{ line.product?.name ?? line.product_id }}</td>
                            <td class="px-4 py-3">{{ line.quantity_ordered }}</td>
                            <td class="px-4 py-3">{{ line.unit_price }}</td>
                        </tr>
                        <tr v-if="!(item.items ?? []).length">
                            <td colspan="3" class="px-4 py-8 text-center text-slate-400">No items</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
