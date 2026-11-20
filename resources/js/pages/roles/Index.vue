<script setup>
import { computed, onMounted, ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AppButton from '@/components/ui/AppButton.vue';
import Modal from '@/components/ui/Modal.vue';
import Skeleton from '@/components/ui/Skeleton.vue';
import { rolesApi } from '@/api';
import { getErrorMessage } from '@/api/client';
import { usePermission } from '@/composables/usePermission';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();
const { can } = usePermission();
const roles = ref([]);
const loading = ref(true);
const saving = ref(false);
const showModal = ref(false);
const editing = ref(null);
const form = ref({ name: '', permissions: [] });

const sections = [
    { title: 'Overview', pages: [{ name: 'Dashboard' }] },
    {
        title: 'People',
        pages: [
            { name: 'Users', permission: 'users.view' },
            { name: 'Roles', permission: 'roles.view' },
            { name: 'Employees', permission: 'employees.view' },
        ],
    },
    {
        title: 'Catalog',
        pages: [
            { name: 'Products', permission: 'products.view' },
            { name: 'Categories', permission: 'categories.view' },
            { name: 'Suppliers', permission: 'suppliers.view' },
        ],
    },
    { title: 'Warehouses', pages: [{ name: 'Warehouses', permission: 'warehouses.view' }] },
    {
        title: 'Inventory',
        pages: [
            { name: 'Stock Levels', permission: 'inventory.view' },
            { name: 'Transactions', permission: 'inventory.view' },
            { name: 'Adjustments', permission: 'inventory-adjustments.view' },
            { name: 'Transfers', permission: 'stock-transfers.view' },
            { name: 'Counts', permission: 'inventory-counts.view' },
        ],
    },
    {
        title: 'Procurement',
        pages: [
            { name: 'Purchase Orders', permission: 'purchase-orders.view' },
            { name: 'Goods Receipts', permission: 'goods-receipts.view' },
        ],
    },
    {
        title: 'System',
        pages: [
            { name: 'Reports', permission: 'reports.view' },
            { name: 'Audit Log', permission: 'audit.view' },
            { name: 'Notifications' },
            { name: 'Profile' },
        ],
    },
];

const permissionGroups = [
    {
        title: 'People',
        items: [
            ['users.view', 'Users view'], ['users.create', 'Users create'], ['users.update', 'Users update'], ['users.delete', 'Users delete'],
            ['roles.view', 'Roles view'], ['roles.create', 'Roles create'], ['roles.update', 'Roles update'], ['roles.delete', 'Roles delete'],
            ['employees.view', 'Employees view'], ['employees.create', 'Employees create'], ['employees.update', 'Employees update'], ['employees.delete', 'Employees delete'],
        ],
    },
    {
        title: 'Catalog',
        items: [
            ['products.view', 'Products view'], ['products.create', 'Products create'], ['products.update', 'Products update'], ['products.delete', 'Products delete'],
            ['categories.view', 'Categories view'], ['categories.create', 'Categories create'], ['categories.update', 'Categories update'], ['categories.delete', 'Categories delete'],
            ['suppliers.view', 'Suppliers view'], ['suppliers.create', 'Suppliers create'], ['suppliers.update', 'Suppliers update'], ['suppliers.delete', 'Suppliers delete'],
        ],
    },
    {
        title: 'Warehouses',
        items: [
            ['warehouses.view', 'Warehouses view'], ['warehouses.create', 'Warehouses create'], ['warehouses.update', 'Warehouses update'], ['warehouses.delete', 'Warehouses delete'],
        ],
    },
    {
        title: 'Inventory',
        items: [
            ['inventory.view', 'Stock view'], ['inventory.adjust', 'Stock adjust'],
            ['inventory-adjustments.view', 'Adjustments view'], ['inventory-adjustments.create', 'Adjustments create'], ['inventory-adjustments.update', 'Adjustments update'], ['inventory-adjustments.delete', 'Adjustments delete'], ['inventory-adjustments.approve', 'Adjustments approve'],
            ['stock-transfers.view', 'Transfers view'], ['stock-transfers.create', 'Transfers create'], ['stock-transfers.update', 'Transfers update'], ['stock-transfers.delete', 'Transfers delete'],
            ['inventory.transfer.view', 'Transfer view'], ['inventory.transfer.create', 'Transfer create'], ['inventory.transfer.approve', 'Transfer approve'], ['inventory.transfer.ship', 'Transfer ship'], ['inventory.transfer.receive', 'Transfer receive'],
            ['inventory-counts.view', 'Counts view'], ['inventory-counts.create', 'Counts create'], ['inventory-counts.update', 'Counts update'], ['inventory-counts.start', 'Counts start'], ['inventory-counts.finalize', 'Counts finalize'],
        ],
    },
    {
        title: 'Procurement',
        items: [
            ['purchase-orders.view', 'Orders view'], ['purchase-orders.create', 'Orders create'], ['purchase-orders.update', 'Orders update'], ['purchase-orders.delete', 'Orders delete'], ['purchase-orders.send', 'Orders send'], ['purchase-orders.close', 'Orders close'],
            ['goods-receipts.view', 'Receipts view'], ['goods-receipts.create', 'Receipts create'], ['goods-receipts.update', 'Receipts update'], ['goods-receipts.confirm', 'Receipts confirm'],
        ],
    },
    {
        title: 'System',
        items: [
            ['reports.view', 'Reports view'], ['reports.export', 'Reports export'],
            ['audit.view', 'Audit view'],
            ['notifications.manage', 'Notifications manage'],
            ['settings.manage', 'Settings manage'],
        ],
    },
];

const cards = computed(() => roles.value.map((role) => {
    const allowed = new Set(role.permissions ?? []);
    const groups = sections
        .map((section) => ({
            title: section.title,
            pages: section.pages.filter((page) => !page.permission || allowed.has(page.permission)).map((page) => page.name),
        }))
        .filter((section) => section.pages.length);

    return {
        id: role.id,
        name: role.name,
        label: role.name.replaceAll('-', ' '),
        permissions: role.permissions ?? [],
        groups,
    };
}));

function openCreate() {
    editing.value = null;
    form.value = { name: '', permissions: [] };
    showModal.value = true;
}

function openEdit(role) {
    editing.value = role;
    form.value = { name: role.name, permissions: [...role.permissions] };
    showModal.value = true;
}

function toggle(permission) {
    const selected = new Set(form.value.permissions);
    if (selected.has(permission)) selected.delete(permission);
    else selected.add(permission);
    form.value.permissions = [...selected];
}

async function load() {
    const data = await rolesApi.list();
    roles.value = Array.isArray(data) ? data : (data?.data ?? []);
}

async function save() {
    saving.value = true;
    const payload = {
        name: form.value.name.trim().toLowerCase().replace(/\s+/g, '-'),
        permissions: form.value.permissions,
    };
    try {
        if (editing.value) await rolesApi.update(editing.value.id, payload);
        else await rolesApi.create(payload);
        toast.success(editing.value ? 'Role updated' : 'Role created');
        showModal.value = false;
        await load();
    } catch (error) {
        toast.error(getErrorMessage(error));
    } finally {
        saving.value = false;
    }
}

onMounted(async () => {
    try {
        await load();
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Roles</h1>
                <p class="text-sm text-slate-500">Pages each role can open. The sidebar uses the same rules.</p>
            </div>
            <AppButton v-if="can('roles.create')" @click="openCreate">Add Role</AppButton>
        </div>

        <div v-if="loading" class="grid gap-4 lg:grid-cols-2">
            <div v-for="n in 4" :key="n" class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                <Skeleton class="mb-4 h-6 w-40" />
                <Skeleton class="mb-2 h-4 w-full" />
                <Skeleton class="h-4 w-2/3" />
            </div>
        </div>

        <div v-else class="grid gap-4 lg:grid-cols-2">
            <article v-for="role in cards" :key="role.id" class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold capitalize text-slate-900">{{ role.label }}</h2>
                    <AppButton v-if="can('roles.update') && role.name !== 'super-admin'" size="sm" variant="secondary" @click="openEdit(role)">Edit</AppButton>
                </div>
                <div class="space-y-4">
                    <section v-for="group in role.groups" :key="group.title">
                        <h3 class="mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ group.title }}</h3>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="page in group.pages" :key="page" class="rounded-full bg-indigo-50 px-3 py-1 text-sm text-indigo-700">{{ page }}</span>
                        </div>
                    </section>
                </div>
            </article>
        </div>

        <Modal :open="showModal" :title="editing ? 'Edit Role' : 'Add Role'" size="lg" @close="showModal = false">
            <form class="space-y-5" @submit.prevent="save">
                <label class="block text-sm">
                    <span class="mb-1 block font-medium text-slate-700">Name</span>
                    <input v-model="form.name" required class="w-full rounded-xl border border-slate-200 px-3 py-2" placeholder="floor-lead" />
                </label>
                <fieldset v-for="group in permissionGroups" :key="group.title">
                    <legend class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ group.title }}</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label v-for="[permission, label] in group.items" :key="permission" class="flex items-center gap-2 rounded-xl border border-slate-100 px-3 py-2 text-sm text-slate-700">
                            <input type="checkbox" class="rounded border-slate-300 text-indigo-600" :checked="form.permissions.includes(permission)" @change="toggle(permission)" />
                            {{ label }}
                        </label>
                    </div>
                </fieldset>
                <div class="flex justify-end gap-2">
                    <AppButton variant="secondary" @click="showModal = false">Cancel</AppButton>
                    <AppButton type="submit" :loading="saving">Save</AppButton>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
