import { ref } from 'vue';
import { unwrapPaginated } from '@/api/client';

export function usePagination(fetchFn, initialParams = {}) {
    const items = ref([]);
    const meta = ref({});
    const loading = ref(false);
    const params = ref({ page: 1, per_page: 10, ...initialParams });

    async function load(extra = {}) {
        loading.value = true;
        try {
            const response = await fetchFn({ ...params.value, ...extra });
            const payload = unwrapPaginated(response);
            items.value = payload.data;
            meta.value = payload.meta;
        } finally {
            loading.value = false;
        }
    }

    function goToPage(page) {
        params.value.page = page;
        return load();
    }

    function setFilter(key, value) {
        params.value.filter = { ...(params.value.filter ?? {}) };
        if (value) {
            params.value.filter[key] = value;
        } else {
            delete params.value.filter[key];
        }
        params.value.page = 1;
        return load();
    }

    function setSearch(value) {
        return setFilter('search', value?.trim?.() ? value.trim() : '');
    }

    function setStatus(value) {
        return setFilter('status', value);
    }

    function setSort(key) {
        const current = params.value.sort;
        if (current === key) {
            params.value.sort = `-${key}`;
        } else if (current === `-${key}`) {
            delete params.value.sort;
        } else {
            params.value.sort = key;
        }
        params.value.page = 1;
        return load();
    }

    return { items, meta, loading, params, load, goToPage, setFilter, setSearch, setStatus, setSort };
}
