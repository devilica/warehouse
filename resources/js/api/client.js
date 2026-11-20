import axios from 'axios';

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
});

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('wms_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('wms_token');
            localStorage.removeItem('wms_user');
            if (!window.location.pathname.startsWith('/login')) {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

export function unwrap(response) {
    return response.data?.data ?? response.data;
}

export function unwrapPaginated(response) {
    const body = response.data ?? {};
    if (Array.isArray(body.data) && body.meta) {
        return { data: body.data, meta: body.meta, links: body.links ?? {} };
    }
    const payload = body.data ?? body;
    if (payload?.data && (payload?.meta || payload?.current_page)) {
        return {
            data: payload.data,
            meta: payload.meta ?? {
                current_page: payload.current_page,
                last_page: payload.last_page,
                per_page: payload.per_page,
                total: payload.total,
            },
            links: payload.links ?? {},
        };
    }
    return { data: Array.isArray(payload) ? payload : [], meta: {}, links: {} };
}

export function getErrorMessage(error) {
    const data = error.response?.data;
    if (data?.message && data.message !== 'The given data was invalid.') {
        return data.message;
    }
    if (data?.errors) {
        const first = Object.values(data.errors).flat()[0];
        if (first) {
            return first;
        }
    }
    return data?.message ?? error.message ?? 'Something went wrong';
}

export default api;
