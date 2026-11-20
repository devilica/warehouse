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
const editingZone = ref(null);
const editingShelf = ref(null);
const editingLocation = ref(null);

async function loadWarehouse() {
    const [item] = await Promise.all([warehousesApi.get(route.params.id), loadZones()]);
    warehouse.value = item;
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

function editZone(row) {
    editingZone.value = row;
    zoneForm.value = { name: row.name, code: row.code ?? '' };
}

async function saveZone() {
    if (editingZone.value) {
        await warehousesApi.updateZone(editingZone.value.id, zoneForm.value);
        toast.success('Zone updated');
    } else {
        await warehousesApi.createZone(route.params.id, zoneForm.value);
        toast.success('Zone created');
    }
    editingZone.value = null;
    zoneForm.value = { name: '', code: '' };
    await loadZones();
}

async function removeZone(row) {
    if (!confirm(`Delete ${row.name}?`)) return;
    await warehousesApi.removeZone(row.id);
    toast.success('Zone deleted');
    if (selectedZone.value === row.id) {
        selectedZone.value = null;
        shelves.value = [];
    }
    await loadZones();
}

function editShelf(row) {
    editingShelf.value = row;
    shelfForm.value = { name: row.name, code: row.code ?? '' };
}

async function saveShelf() {
    if (editingShelf.value) {
        await warehousesApi.updateShelf(editingShelf.value.id, shelfForm.value);
        toast.success('Shelf updated');
    } else {
        await warehousesApi.createShelf(route.params.id, selectedZone.value, shelfForm.value);
        toast.success('Shelf created');
    }
    editingShelf.value = null;
    shelfForm.value = { name: '', code: '' };
    await loadShelves(selectedZone.value);
}

async function removeShelf(row) {
    if (!confirm(`Delete ${row.name}?`)) return;
    await warehousesApi.removeShelf(row.id);
    toast.success('Shelf deleted');
    await loadShelves(selectedZone.value);
}

function editLocation(row) {
    editingLocation.value = row;
    locationForm.value = { name: row.name ?? '', code: row.code ?? '' };
}

async function saveLocation() {
    if (editingLocation.value) {
        await warehousesApi.updateLocation(editingLocation.value.id, locationForm.value);
        toast.success('Location updated');
    } else {
        await warehousesApi.createLocation(route.params.id, selectedZone.value, selectedShelf.value, locationForm.value);
        toast.success('Location created');
    }
    editingLocation.value = null;
    locationForm.value = { name: '', code: '' };
    await loadLocations(selectedShelf.value);
}

async function removeLocation(row) {
    if (!confirm(`Delete ${row.code}?`)) return;
    await warehousesApi.removeLocation(row.id);
    toast.success('Location deleted');
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
            <form class="grid gap-3 rounded-2xl border bg-white p-4 md:grid-cols-3" @submit.prevent="saveZone">
                <FormField v-model="zoneForm.name" label="Zone Name" required />
                <FormField v-model="zoneForm.code" label="Zone Code" required />
                <div class="flex items-end gap-2">
                    <AppButton v-if="editingZone" variant="secondary" @click="editingZone = null; zoneForm = { name: '', code: '' }">Cancel</AppButton>
                    <AppButton type="submit">{{ editingZone ? 'Save Zone' : 'Add Zone' }}</AppButton>
                </div>
            </form>
            <DataTable :columns="[{key:'name',label:'Name'},{key:'code',label:'Code'}]" :rows="zones">
                <template #actions="{ row }">
                    <div class="flex w-full flex-col gap-1">
                        <AppButton size="sm" variant="secondary" class="w-full" @click="editZone(row)">Edit</AppButton>
                        <AppButton size="sm" variant="danger" class="w-full" @click="removeZone(row)">Delete</AppButton>
                    </div>
                    <AppButton size="sm" variant="secondary" @click="loadShelves(row.id)">View Shelves</AppButton>
                </template>
            </DataTable>
        </div>

        <div v-if="tab === 'shelves'" class="space-y-4">
            <form class="grid gap-3 rounded-2xl border bg-white p-4 md:grid-cols-3" @submit.prevent="saveShelf">
                <FormField v-model="shelfForm.name" label="Shelf Name" required />
                <FormField v-model="shelfForm.code" label="Shelf Code" required />
                <div class="flex items-end gap-2">
                    <AppButton v-if="editingShelf" variant="secondary" @click="editingShelf = null; shelfForm = { name: '', code: '' }">Cancel</AppButton>
                    <AppButton type="submit" :disabled="!selectedZone">{{ editingShelf ? 'Save Shelf' : 'Add Shelf' }}</AppButton>
                </div>
            </form>
            <DataTable :columns="[{key:'name',label:'Name'},{key:'code',label:'Code'}]" :rows="shelves">
                <template #actions="{ row }">
                    <div class="flex w-full flex-col gap-1">
                        <AppButton size="sm" variant="secondary" class="w-full" @click="editShelf(row)">Edit</AppButton>
                        <AppButton size="sm" variant="danger" class="w-full" @click="removeShelf(row)">Delete</AppButton>
                    </div>
                    <AppButton size="sm" variant="secondary" @click="loadLocations(row.id)">View Locations</AppButton>
                </template>
            </DataTable>
        </div>

        <div v-if="tab === 'locations'" class="space-y-4">
            <form class="grid gap-3 rounded-2xl border bg-white p-4 md:grid-cols-3" @submit.prevent="saveLocation">
                <FormField v-model="locationForm.name" label="Location Name" />
                <FormField v-model="locationForm.code" label="Location Code" required />
                <div class="flex items-end gap-2">
                    <AppButton v-if="editingLocation" variant="secondary" @click="editingLocation = null; locationForm = { name: '', code: '' }">Cancel</AppButton>
                    <AppButton type="submit" :disabled="!selectedShelf">{{ editingLocation ? 'Save Location' : 'Add Location' }}</AppButton>
                </div>
            </form>
            <DataTable :columns="[{key:'name',label:'Name'},{key:'code',label:'Code'}]" :rows="locations">
                <template #actions="{ row }">
                    <AppButton size="sm" variant="secondary" @click="editLocation(row)">Edit</AppButton>
                    <AppButton size="sm" variant="danger" @click="removeLocation(row)">Delete</AppButton>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
