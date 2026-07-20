<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { searchApi } from '@/api';
import AppButton from '@/components/ui/AppButton.vue';

const router = useRouter();
const auth = useAuthStore();
const query = ref('');
const results = ref([]);
const searching = ref(false);

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

async function logout() {
    await auth.logout();
    router.push('/login');
}
</script>

<template>
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="flex items-center gap-4 px-6 py-4">
            <div class="relative flex-1 max-w-xl">
                <input
                    v-model="query"
                    type="search"
                    placeholder="Search products, warehouses, orders..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                    @keyup.enter="onSearch"
                />
                <div v-if="results.length" class="absolute left-0 right-0 top-full z-30 mt-2 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                    <button
                        v-for="item in results.slice(0, 8)"
                        :key="item.id + item.type"
                        class="block w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-slate-50"
                        @click="results = []"
                    >
                        <span class="font-medium">{{ item.label ?? item.name ?? item.title }}</span>
                        <span class="ml-2 text-xs text-slate-400">{{ item.type }}</span>
                    </button>
                </div>
            </div>
            <AppButton variant="ghost" size="sm" @click="logout">Logout</AppButton>
        </div>
    </header>
</template>
