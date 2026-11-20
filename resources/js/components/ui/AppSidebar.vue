<script setup>
import { computed } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { usePermission } from '@/composables/usePermission';
import AppButton from '@/components/ui/AppButton.vue';
import NavIcon from '@/components/ui/NavIcon.vue';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const { can } = usePermission();

const navGroups = computed(() => [
    {
        title: 'Overview',
        items: [{ name: 'Dashboard', to: '/', icon: 'home' }],
    },
    {
        title: 'People',
        items: [
            { name: 'Users', to: '/users', permission: 'users.view', icon: 'users' },
            { name: 'Employees', to: '/employees', permission: 'employees.view', icon: 'employees' },
        ],
    },
    {
        title: 'Catalog',
        items: [
            { name: 'Products', to: '/products', permission: 'products.view', icon: 'box' },
            { name: 'Categories', to: '/categories', permission: 'categories.view', icon: 'tag' },
            { name: 'Suppliers', to: '/suppliers', permission: 'suppliers.view', icon: 'building' },
        ],
    },
    {
        title: 'Warehouses',
        items: [
            { name: 'Warehouses', to: '/warehouses', permission: 'warehouses.view', icon: 'warehouse' },
        ],
    },
    {
        title: 'Inventory',
        items: [
            { name: 'Stock Levels', to: '/inventory/stock-levels', permission: 'inventory.view', icon: 'chart' },
            { name: 'Transactions', to: '/inventory/transactions', permission: 'inventory.view', icon: 'refresh' },
            { name: 'Adjustments', to: '/inventory/adjustments', permission: 'inventory-adjustments.view', icon: 'scale' },
            { name: 'Transfers', to: '/inventory/transfers', permission: 'stock-transfers.view', icon: 'truck' },
            { name: 'Counts', to: '/inventory/counts', permission: 'inventory-counts.view', icon: 'hash' },
        ],
    },
    {
        title: 'Procurement',
        items: [
            { name: 'Purchase Orders', to: '/purchase-orders', permission: 'purchase-orders.view', icon: 'cart' },
            { name: 'Goods Receipts', to: '/goods-receipts', permission: 'goods-receipts.view', icon: 'inbox' },
        ],
    },
    {
        title: 'System',
        items: [
            { name: 'Reports', to: '/reports', permission: 'reports.view', icon: 'document' },
            { name: 'Audit Log', to: '/audit', permission: 'audit.view', icon: 'clipboard' },
            { name: 'Notifications', to: '/notifications', icon: 'bell' },
            { name: 'Profile', to: '/profile', icon: 'settings' },
        ],
    },
].map((group) => ({
    ...group,
    items: group.items.filter((item) => !item.permission || can(item.permission)),
})).filter((group) => group.items.length));

function isActive(path) {
    if (path === '/') return route.path === '/';
    return route.path.startsWith(path);
}

async function logout() {
    await auth.logout();
    router.push('/login');
}
</script>

<template>
    <aside class="sticky top-0 flex h-screen w-64 shrink-0 flex-col bg-[#12152c] text-slate-300">
        <div class="flex items-center gap-3 px-5 py-5">
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-500 text-white shadow-lg shadow-indigo-900/40">
                <NavIcon name="box" class="h-5 w-5" />
            </div>
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-widest text-indigo-300">Company WMS</div>
                <div class="text-base font-bold leading-tight text-white">Warehouse CMS</div>
            </div>
        </div>
        <nav class="flex-1 overflow-y-auto px-3 py-2">
            <div v-for="group in navGroups" :key="group.title" class="mb-4">
                <div class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ group.title }}</div>
                <RouterLink
                    v-for="item in group.items"
                    :key="item.to"
                    :to="item.to"
                    class="mb-0.5 flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition"
                    :class="isActive(item.to) ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-950/40' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                >
                    <NavIcon :name="item.icon" />
                    <span>{{ item.name }}</span>
                </RouterLink>
            </div>
        </nav>
        <div class="flex items-center gap-3 border-t border-white/10 px-4 py-4">
            <div class="min-w-0 flex-1">
                <div class="truncate text-sm font-medium text-white">{{ auth.user?.name }}</div>
                <div class="truncate text-xs text-slate-500">{{ auth.user?.email }}</div>
            </div>
            <AppButton variant="danger" size="sm" @click="logout">Logout</AppButton>
        </div>
    </aside>
</template>
