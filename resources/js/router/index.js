import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
    { path: '/login', name: 'login', component: () => import('@/pages/auth/Login.vue'), meta: { guest: true } },
    { path: '/', name: 'dashboard', component: () => import('@/pages/Dashboard.vue'), meta: { auth: true } },
    { path: '/users', component: () => import('@/pages/users/Index.vue'), meta: { auth: true, permission: 'users.view' } },
    { path: '/employees', component: () => import('@/pages/employees/Index.vue'), meta: { auth: true, permission: 'employees.view' } },
    { path: '/warehouses', component: () => import('@/pages/warehouses/Index.vue'), meta: { auth: true, permission: 'warehouses.view' } },
    { path: '/warehouses/:id', component: () => import('@/pages/warehouses/Show.vue'), meta: { auth: true, permission: 'warehouses.view' } },
    { path: '/products', component: () => import('@/pages/products/Index.vue'), meta: { auth: true, permission: 'products.view' } },
    { path: '/categories', component: () => import('@/pages/categories/Index.vue'), meta: { auth: true, permission: 'categories.view' } },
    { path: '/suppliers', component: () => import('@/pages/suppliers/Index.vue'), meta: { auth: true, permission: 'suppliers.view' } },
    { path: '/inventory/stock-levels', component: () => import('@/pages/inventory/StockLevels.vue'), meta: { auth: true, permission: 'inventory.view' } },
    { path: '/inventory/transactions', component: () => import('@/pages/inventory/Transactions.vue'), meta: { auth: true, permission: 'inventory.view' } },
    { path: '/inventory/adjustments', component: () => import('@/pages/inventory/Adjustments.vue'), meta: { auth: true, permission: 'inventory-adjustments.view' } },
    { path: '/inventory/transfers', component: () => import('@/pages/inventory/Transfers.vue'), meta: { auth: true, permission: 'stock-transfers.view' } },
    { path: '/inventory/transfers/:id', component: () => import('@/pages/inventory/TransferShow.vue'), meta: { auth: true, permission: 'stock-transfers.view' } },
    { path: '/inventory/counts', component: () => import('@/pages/inventory/Counts.vue'), meta: { auth: true, permission: 'inventory-counts.view' } },
    { path: '/inventory/counts/:id', component: () => import('@/pages/inventory/CountShow.vue'), meta: { auth: true, permission: 'inventory-counts.view' } },
    { path: '/purchase-orders', component: () => import('@/pages/purchase-orders/Index.vue'), meta: { auth: true, permission: 'purchase-orders.view' } },
    { path: '/purchase-orders/:id', component: () => import('@/pages/purchase-orders/Show.vue'), meta: { auth: true, permission: 'purchase-orders.view' } },
    { path: '/goods-receipts', component: () => import('@/pages/goods-receipts/Index.vue'), meta: { auth: true, permission: 'goods-receipts.view' } },
    { path: '/reports', component: () => import('@/pages/reports/Index.vue'), meta: { auth: true, permission: 'reports.view' } },
    { path: '/audit', component: () => import('@/pages/audit/Index.vue'), meta: { auth: true, permission: 'audit.view' } },
    { path: '/notifications', component: () => import('@/pages/notifications/Index.vue'), meta: { auth: true } },
    { path: '/profile', component: () => import('@/pages/profile/Profile.vue'), meta: { auth: true } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (auth.token && !auth.user) {
        await auth.fetchMe();
    }

    if (to.meta.auth && !auth.isAuthenticated) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guest && auth.isAuthenticated) {
        return { name: 'dashboard' };
    }

    if (to.meta.permission && !auth.can(to.meta.permission)) {
        return { name: 'dashboard' };
    }

    return true;
});

export default router;
