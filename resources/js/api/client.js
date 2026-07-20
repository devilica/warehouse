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
    const payload = response.data?.data ?? response.data;
    if (payload?.data && payload?.meta) {
        return payload;
    }
    return { data: Array.isArray(payload) ? payload : [], meta: {}, links: {} };
}

export function getErrorMessage(error) {
    return error.response?.data?.message
        ?? error.response?.data?.errors
        ?? error.message
        ?? 'Something went wrong';
}

export default api;
