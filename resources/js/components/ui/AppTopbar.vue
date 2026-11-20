<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { notificationsApi, searchApi } from '@/api';
import { unwrapPaginated } from '@/api/client';
import NavIcon from '@/components/ui/NavIcon.vue';
import { notificationTarget } from '@/utils/notificationTarget';

const emit = defineEmits(['menu']);

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const query = ref('');
const results = ref([]);
const searching = ref(false);
const searched = ref(false);
const searchInput = ref(null);
let searchTimer;
const notes = ref([]);
const unreadCount = ref(0);
const notesOpen = ref(false);
const profileOpen = ref(false);

const unreadLabel = computed(() => (unreadCount.value > 9 ? '9+' : String(unreadCount.value)));

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

function scheduleSearch() {
    clearTimeout(searchTimer);
    if (query.value.trim().length < 2) {
        results.value = [];
        searched.value = false;
        return;
    }
    searchTimer = setTimeout(runSearch, 300);
}

async function runSearch() {
    const term = query.value.trim();
    if (term.length < 2) {
        results.value = [];
        searched.value = false;
        return;
    }
    searching.value = true;
    try {
        const payload = await searchApi.search(term);
        results.value = Array.isArray(payload) ? payload : [];
        searched.value = true;
    } catch {
        results.value = [];
        searched.value = true;
    } finally {
        searching.value = false;
    }
}

function searchNow() {
    clearTimeout(searchTimer);
    runSearch();
}

function openResult(item) {
    results.value = [];
    searched.value = false;
    query.value = '';
    router.push(item.to);
}

watch(query, scheduleSearch);

function onKeydown(event) {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        searchInput.value?.focus();
    }
}

function noteStyle(message) {
    return notificationTarget(message);
}

async function openNote(note) {
    notesOpen.value = false;
    if (!note.read_at) {
        await notificationsApi.markRead(note.id);
        await loadNotes();
    }
}

async function markAllNotes() {
    await notificationsApi.markAllRead();
    await loadNotes();
}

async function loadNotes() {
    if (!auth.token) return;
    try {
        const payload = unwrapPaginated(await notificationsApi.list({ per_page: 5 }));
        notes.value = payload.data ?? [];
        unreadCount.value = payload.meta?.unread_count ?? notes.value.filter((note) => !note.read_at).length;
    } catch {
        notes.value = [];
    }
}

function toggleNotes() {
    notesOpen.value = !notesOpen.value;
    if (notesOpen.value) {
        profileOpen.value = false;
        loadNotes();
    }
}

function toggleProfile() {
    profileOpen.value = !profileOpen.value;
    if (profileOpen.value) notesOpen.value = false;
}

async function logout() {
    profileOpen.value = false;
    await auth.logout();
    router.push('/login');
}

onMounted(() => {
    window.addEventListener('keydown', onKeydown);
    window.addEventListener('wms-notifications-changed', loadNotes);
});
onUnmounted(() => {
    clearTimeout(searchTimer);
    window.removeEventListener('keydown', onKeydown);
    window.removeEventListener('wms-notifications-changed', loadNotes);
});
watch(() => route.fullPath, () => {
    notesOpen.value = false;
    profileOpen.value = false;
    loadNotes();
}, { immediate: true });
</script>

<template>
    <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/80 backdrop-blur">
        <div class="flex items-center gap-2 px-3 py-3 lg:gap-4 lg:px-6 lg:py-3.5">
            <button
                type="button"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 lg:hidden"
                aria-label="Open menu"
                @click="emit('menu')"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
            <div class="relative min-w-0 w-full max-w-xl">
                <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="11" cy="11" r="7" />
                    <path stroke-linecap="round" d="M20 20l-3.5-3.5" />
                </svg>
                <input
                    ref="searchInput"
                    v-model="query"
                    type="search"
                    placeholder="Search products, warehouses, orders..."
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pl-11 pr-4 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 lg:pr-16"
                    @keyup.enter="searchNow"
                />
                <kbd class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded-lg border border-slate-200 bg-white px-1.5 py-0.5 text-[11px] font-medium text-slate-400 lg:inline">Ctrl+K</kbd>
                <div v-if="searched" class="absolute left-0 right-0 top-full z-30 mt-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                    <p v-if="!results.length" class="px-3 py-4 text-sm text-slate-400">No results</p>
                    <button
                        v-for="item in results"
                        :key="item.type + item.id"
                        class="block w-full rounded-xl px-3 py-2 text-left text-sm hover:bg-slate-50"
                        @click="openResult(item)"
                    >
                        <span class="font-medium">{{ item.label }}</span>
                        <span class="ml-2 text-xs text-slate-400">{{ item.type }}</span>
                    </button>
                </div>
            </div>
            <div class="ml-auto flex shrink-0 items-center gap-2 lg:gap-3">
            <div class="relative">
                <button
                    type="button"
                    class="relative flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-indigo-200 hover:text-indigo-600"
                    aria-label="Notifications"
                    @click="toggleNotes"
                >
                    <NavIcon name="bell" />
                    <span
                        v-if="unreadCount"
                        class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white"
                    >{{ unreadLabel }}</span>
                </button>
                <div v-if="notesOpen" class="absolute right-0 z-30 mt-2 w-96 max-w-[calc(100vw-2rem)] rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                    <p v-if="!notes.length" class="px-3 py-4 text-sm text-slate-400">No notifications yet.</p>
                    <div class="space-y-1">
                        <RouterLink
                            v-for="note in notes"
                            :key="note.id"
                            :to="noteStyle(note.data?.message).to"
                            class="flex items-start gap-3 rounded-xl px-2 py-2.5 transition hover:bg-slate-50"
                            @click="openNote(note)"
                        >
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" :class="noteStyle(note.data?.message).tone">
                                <NavIcon :name="noteStyle(note.data?.message).icon" />
                            </span>
                            <p class="min-w-0 flex-1 pt-0.5 text-sm leading-snug" :class="note.read_at ? 'text-slate-500' : 'font-medium text-slate-800'">{{ note.data?.message ?? 'Notification' }}</p>
                            <span v-if="!note.read_at" class="mt-2 h-2 w-2 shrink-0 rounded-full bg-indigo-500" />
                        </RouterLink>
                    </div>
                    <div class="mt-1 flex items-center justify-between gap-2 border-t border-slate-100 px-2 pt-2">
                        <button
                            v-if="unreadCount"
                            type="button"
                            class="rounded-xl px-2 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50"
                            @click="markAllNotes"
                        >Mark all as read</button>
                        <RouterLink
                            to="/notifications"
                            class="ml-auto rounded-xl px-2 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-50"
                            @click="notesOpen = false"
                        >View all</RouterLink>
                    </div>
                </div>
            </div>
            <div class="relative shrink-0">
                <button
                    type="button"
                    class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white py-1.5 pl-1.5 pr-1.5 transition hover:border-indigo-200 lg:pr-3"
                    aria-label="Account menu"
                    @click="toggleProfile"
                >
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-sm font-semibold text-white">{{ initials }}</span>
                    <span class="hidden text-left lg:block">
                        <span class="block text-sm font-semibold leading-tight text-slate-900">{{ auth.user?.name }}</span>
                        <span class="block text-xs text-slate-500">{{ roleLabel }}</span>
                    </span>
                </button>
                <div v-if="profileOpen" class="absolute right-0 z-30 mt-2 w-44 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl">
                    <RouterLink
                        to="/profile"
                        class="block rounded-xl px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        @click="profileOpen = false"
                    >Profile</RouterLink>
                    <button
                        type="button"
                        class="block w-full rounded-xl px-3 py-2 text-left text-sm font-medium text-red-600 hover:bg-red-50"
                        @click="logout"
                    >Logout</button>
                </div>
            </div>
            </div>
        </div>
    </header>
</template>
