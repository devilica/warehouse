<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { searchApi } from '@/api';
import NavIcon from '@/components/ui/NavIcon.vue';

const auth = useAuthStore();
const query = ref('');
const results = ref([]);
const searching = ref(false);
const searchInput = ref(null);

const initials = computed(() => {
    const parts = (auth.user?.name ?? '').trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return '?';
    return (parts[0][0] + (parts[1]?.[0] ?? '')).toUpperCase();
});

const roleLabel = computed(() => {
    const role = auth.roles?.[0];
    if (!role) return 'Member';
    return role.replace(/-/g, ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
});

async function onSearch() {
    if (!query.value.trim()) {
        results.value = [];
        return;
    }
    searching.value = true;
    try {
        results.value = await searchApi.search(query.value);
    } finally {
        searching.value = false;
    }
}

function onKeydown(event) {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        searchInput.value?.focus();
    }
}

onMounted(() => window.addEventListener('keydown', onKeydown));
onUnmounted(() => window.removeEventListener('keydown', onKeydown));
</script>

<template>
    <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/80 backdrop-blur">
        <div class="flex items-center gap-4 px-6 py-3.5">
            <div class="relative max-w-xl flex-1">
                <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="11" cy="11" r="7" />
                    <path stroke-linecap="round" d="M20 20l-3.5-3.5" />
                </svg>
                <input
                    ref="searchInput"
                    v-model="query"
                    type="search"
                    placeholder="Search products, warehouses, orders..."
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pl-11 pr-16 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                    @keyup.enter="onSearch"
                />
                <kbd class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded-lg border border-slate-200 bg-white px-1.5 py-0.5 text-[11px] font-medium text-slate-400 sm:inline">Ctrl+K</kbd>
                <div v-if="results.length" class="absolute left-0 right-0 top-full z-30 mt-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                    <button
                        v-for="item in results.slice(0, 8)"
                        :key="item.id + item.type"
                        class="block w-full rounded-xl px-3 py-2 text-left text-sm hover:bg-slate-50"
                        @click="results = []"
                    >
                        <span class="font-medium">{{ item.label ?? item.name ?? item.title }}</span>
                        <span class="ml-2 text-xs text-slate-400">{{ item.type }}</span>
                    </button>
                </div>
            </div>
            <RouterLink
                to="/notifications"
                class="flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-indigo-200 hover:text-indigo-600"
                aria-label="Notifications"
            >
                <NavIcon name="bell" />
            </RouterLink>
            <RouterLink to="/profile" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white py-1.5 pl-1.5 pr-3 transition hover:border-indigo-200">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-sm font-semibold text-white">{{ initials }}</span>
                <span class="hidden text-left sm:block">
                    <span class="block text-sm font-semibold leading-tight text-slate-900">{{ auth.user?.name }}</span>
                    <span class="block text-xs text-slate-500">{{ roleLabel }}</span>
                </span>
            </RouterLink>
        </div>
    </header>
</template>
