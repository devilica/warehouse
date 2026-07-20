import { ref } from 'vue';
import { unwrapPaginated } from '@/api/client';

export function usePagination(fetchFn, initialParams = {}) {
    const items = ref([]);
    const meta = ref({});
    const loading = ref(false);
    const params = ref({ page: 1, per_page: 15, ...initialParams });

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
        params.value[key] = value;
        params.value.page = 1;
        return load();
    }

    return { items, meta, loading, params, load, goToPage, setFilter };
}
