<script setup>
defineProps({
    name: { type: String, required: true },
});

const icons = {
    home: ['M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1z'],
    users: ['M16 19v-1.2A3.8 3.8 0 0 0 12.2 14H7.8A3.8 3.8 0 0 0 4 17.8V19', 'M10 11.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6'],
    employees: ['M8 19v-1.2A3.3 3.3 0 0 1 11.3 14.5h1.4A3.3 3.3 0 0 1 16 17.8V19', 'M12 11.5a2.6 2.6 0 1 0 0-5.2 2.6 2.6 0 0 0 0 5.2', 'M4 8h4M16 8h4'],
    box: ['M21 8 12 3 3 8v8l9 5 9-5z', 'M3.3 8.2 12 13l8.7-4.8', 'M12 13v8'],
    tag: ['M20.5 13.2 13.2 20.5a1.5 1.5 0 0 1-2.1 0L3.5 13V3.5H13z', 'M7.5 7.5h.01'],
    building: ['M4 20V7l8-4 8 4v13', 'M9 20v-5h6v5', 'M9 9h.01M15 9h.01M9 13h.01M15 13h.01'],
    warehouse: ['M3 20V10l9-6 9 6v10', 'M9 20v-6h6v6'],
    chart: ['M4 19V5', 'M4 19h16', 'M8 15l3.2-4 2.8 2.2L18 8'],
    refresh: ['M20 12a8 8 0 1 1-2.2-5.5', 'M20 4v5h-5'],
    scale: ['M12 3v18', 'M5 8h14', 'M7.5 8 5 14a2.6 2.6 0 0 0 5 0z', 'M16.5 8 14 14a2.6 2.6 0 0 0 5 0z'],
    truck: ['M3 7h11v8H3z', 'M14 11h4.2L21 14v1h-7', 'M7.5 18.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3', 'M17.5 18.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3'],
    hash: ['M10 4 8 20', 'M16 4l-2 16', 'M4 9h16', 'M4 15h16'],
    cart: ['M6 6h15l-1.6 8H7.2L5.5 3H3', 'M9 20.5a1.3 1.3 0 1 0 0-2.6 1.3 1.3 0 0 0 0 2.6', 'M17 20.5a1.3 1.3 0 1 0 0-2.6 1.3 1.3 0 0 0 0 2.6'],
    inbox: ['M3 13 5.2 6h13.6L21 13', 'M3 13h5l1 2h6l1-2h5v7H3z'],
    document: ['M7 3h7l5 5v13H7z', 'M14 3v5h5', 'M10 13h6', 'M10 17h4'],
    clipboard: ['M9 4h6', 'M8 4.5A2 2 0 0 0 6 6.5V20h12V6.5A2 2 0 0 0 16 4.5', 'M9 11h6', 'M9 15h4'],
    bell: ['M6 16V11a6 6 0 1 1 12 0v5l1.5 2h-15z', 'M10 19a2 2 0 0 0 4 0'],
    settings: ['M4 8h16', 'M4 16h16', 'M9 8a2 2 0 1 0 .01 0', 'M15 16a2 2 0 1 0 .01 0'],
};
</script>

<template>
    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <path
            v-for="(d, index) in icons[name] ?? icons.box"
            :key="index"
            :d="d"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>
</template>
