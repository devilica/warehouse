<script setup>
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import FormField from '@/components/ui/FormField.vue';
import AppButton from '@/components/ui/AppButton.vue';
import { warehousesApi } from '@/api';
import { unwrapPaginated } from '@/api/client';
import { useToastStore } from '@/stores/toast';

const route = useRoute();
const toast = useToastStore();
const warehouse = ref(null);
const tab = ref('zones');
const zones = ref([]);
const shelves = ref([]);
const locations = ref([]);
const selectedZone = ref(null);
const selectedShelf = ref(null);
const zoneForm = ref({ name: '', code: '' });
const shelfForm = ref({ name: '', code: '' });
const locationForm = ref({ name: '', code: '' });

async function loadWarehouse() {
    warehouse.value = await warehousesApi.get(route.params.id);
    await loadZones();
}

async function loadZones() {
    const res = await warehousesApi.zones(route.params.id);
    zones.value = unwrapPaginated(res).data;
}

async function loadShelves(zoneId) {
    selectedZone.value = zoneId;
    const res = await warehousesApi.shelves(route.params.id, zoneId);
    shelves.value = unwrapPaginated(res).data;
    tab.value = 'shelves';
}

async function loadLocations(shelfId) {
    selectedShelf.value = shelfId;
    const res = await warehousesApi.locations(route.params.id, selectedZone.value, shelfId);
    locations.value = unwrapPaginated(res).data;
    tab.value = 'locations';
}

async function createZone() {
    await warehousesApi.createZone(route.params.id, zoneForm.value);
    zoneForm.value = { name: '', code: '' };
    toast.success('Zone created');
    await loadZones();
}

async function createShelf() {
    await warehousesApi.createShelf(route.params.id, selectedZone.value, shelfForm.value);
    shelfForm.value = { name: '', code: '' };
    toast.success('Shelf created');
    await loadShelves(selectedZone.value);
}

async function createLocation() {
    await warehousesApi.createLocation(route.params.id, selectedZone.value, selectedShelf.value, locationForm.value);
    locationForm.value = { name: '', code: '' };
    toast.success('Location created');
    await loadLocations(selectedShelf.value);
}

onMounted(loadWarehouse);
</script>

<template>
    <AdminLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold">{{ warehouse?.name ?? 'Warehouse' }}</h1>
            <p class="text-sm text-slate-500">Zones, shelves, and storage locations</p>
        </div>
        <div class="mb-4 flex gap-2">
            <AppButton :variant="tab === 'zones' ? 'primary' : 'secondary'" size="sm" @click="tab = 'zones'">Zones</AppButton>
            <AppButton :variant="tab === 'shelves' ? 'primary' : 'secondary'" size="sm" @click="tab = 'shelves'">Shelves</AppButton>
            <AppButton :variant="tab === 'locations' ? 'primary' : 'secondary'" size="sm" @click="tab = 'locations'">Locations</AppButton>
        </div>

        <div v-if="tab === 'zones'" class="space-y-4">
            <form class="grid gap-3 rounded-2xl border bg-white p-4 md:grid-cols-3" @submit.prevent="createZone">
                <FormField v-model="zoneForm.name" label="Zone Name" required />
                <FormField v-model="zoneForm.code" label="Zone Code" required />
                <div class="flex items-end"><AppButton type="submit">Add Zone</AppButton></div>
            </form>
            <DataTable :columns="[{key:'name',label:'Name'},{key:'code',label:'Code'}]" :rows="zones">
                <template #actions="{ row }">
                    <AppButton size="sm" @click="loadShelves(row.id)">View Shelves</AppButton>
                </template>
            </DataTable>
        </div>

        <div v-if="tab === 'shelves'" class="space-y-4">
            <form class="grid gap-3 rounded-2xl border bg-white p-4 md:grid-cols-3" @submit.prevent="createShelf">
                <FormField v-model="shelfForm.name" label="Shelf Name" required />
                <FormField v-model="shelfForm.code" label="Shelf Code" required />
                <div class="flex items-end"><AppButton type="submit" :disabled="!selectedZone">Add Shelf</AppButton></div>
            </form>
            <DataTable :columns="[{key:'name',label:'Name'},{key:'code',label:'Code'}]" :rows="shelves">
                <template #actions="{ row }">
                    <AppButton size="sm" @click="loadLocations(row.id)">View Locations</AppButton>
                </template>
            </DataTable>
        </div>

        <div v-if="tab === 'locations'" class="space-y-4">
            <form class="grid gap-3 rounded-2xl border bg-white p-4 md:grid-cols-3" @submit.prevent="createLocation">
                <FormField v-model="locationForm.name" label="Location Name" required />
                <FormField v-model="locationForm.code" label="Location Code" required />
                <div class="flex items-end"><AppButton type="submit" :disabled="!selectedShelf">Add Location</AppButton></div>
            </form>
            <DataTable :columns="[{key:'name',label:'Name'},{key:'code',label:'Code'}]" :rows="locations" />
        </div>
    </AdminLayout>
</template>
