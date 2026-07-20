import api, { unwrap } from './client';

export const authApi = {
    login: (payload) => api.post('/auth/login', payload).then(unwrap),
    logout: () => api.post('/auth/logout').then(unwrap),
    me: () => api.get('/auth/me').then(unwrap),
    updateProfile: (payload) => api.put('/auth/profile', payload).then(unwrap),
    changePassword: (payload) => api.put('/auth/password', payload).then(unwrap),
};

export const usersApi = {
    list: (params) => api.get('/users', { params }).then((r) => r),
    get: (id) => api.get(`/users/${id}`).then(unwrap),
    create: (payload) => api.post('/users', payload).then(unwrap),
    update: (id, payload) => api.put(`/users/${id}`, payload).then(unwrap),
    remove: (id) => api.delete(`/users/${id}`),
    syncRoles: (id, roles) => api.put(`/users/${id}/roles`, { roles }).then(unwrap),
};

export const rolesApi = {
    list: () => api.get('/roles').then(unwrap),
    get: (id) => api.get(`/roles/${id}`).then(unwrap),
};

export const employeesApi = {
    list: (params) => api.get('/employees', { params }),
    get: (id) => api.get(`/employees/${id}`).then(unwrap),
    create: (payload) => api.post('/employees', payload).then(unwrap),
    update: (id, payload) => api.put(`/employees/${id}`, payload).then(unwrap),
    remove: (id) => api.delete(`/employees/${id}`),
};

export const suppliersApi = {
    list: (params) => api.get('/suppliers', { params }),
    get: (id) => api.get(`/suppliers/${id}`).then(unwrap),
    create: (payload) => api.post('/suppliers', payload).then(unwrap),
    update: (id, payload) => api.put(`/suppliers/${id}`, payload).then(unwrap),
    remove: (id) => api.delete(`/suppliers/${id}`),
};

export const warehousesApi = {
    list: (params) => api.get('/warehouses', { params }),
    get: (id) => api.get(`/warehouses/${id}`).then(unwrap),
    create: (payload) => api.post('/warehouses', payload).then(unwrap),
    update: (id, payload) => api.put(`/warehouses/${id}`, payload).then(unwrap),
    remove: (id) => api.delete(`/warehouses/${id}`),
    zones: (warehouseId, params) => api.get(`/warehouses/${warehouseId}/zones`, { params }),
    createZone: (warehouseId, payload) => api.post(`/warehouses/${warehouseId}/zones`, payload).then(unwrap),
    shelves: (warehouseId, zoneId, params) => api.get(`/warehouses/${warehouseId}/zones/${zoneId}/shelves`, { params }),
    createShelf: (warehouseId, zoneId, payload) => api.post(`/warehouses/${warehouseId}/zones/${zoneId}/shelves`, payload).then(unwrap),
    locations: (warehouseId, zoneId, shelfId, params) => api.get(`/warehouses/${warehouseId}/zones/${zoneId}/shelves/${shelfId}/locations`, { params }),
    createLocation: (warehouseId, zoneId, shelfId, payload) => api.post(`/warehouses/${warehouseId}/zones/${zoneId}/shelves/${shelfId}/locations`, payload).then(unwrap),
};

export const categoriesApi = {
    list: (params) => api.get('/categories', { params }),
    get: (id) => api.get(`/categories/${id}`).then(unwrap),
    create: (payload) => api.post('/categories', payload).then(unwrap),
    update: (id, payload) => api.put(`/categories/${id}`, payload).then(unwrap),
    remove: (id) => api.delete(`/categories/${id}`),
};

export const productsApi = {
    list: (params) => api.get('/products', { params }),
    get: (id) => api.get(`/products/${id}`).then(unwrap),
    create: (payload) => api.post('/products', payload).then(unwrap),
    update: (id, payload) => api.put(`/products/${id}`, payload).then(unwrap),
    remove: (id) => api.delete(`/products/${id}`),
    byBarcode: (code) => api.get(`/products/by-barcode/${code}`).then(unwrap),
};

export const inventoryApi = {
    stockLevels: (params) => api.get('/stock-levels', { params }),
    transactions: (params) => api.get('/inventory-transactions', { params }),
};

export const purchaseOrdersApi = {
    list: (params) => api.get('/purchase-orders', { params }),
    get: (id) => api.get(`/purchase-orders/${id}`).then(unwrap),
    create: (payload) => api.post('/purchase-orders', payload).then(unwrap),
    send: (id) => api.post(`/purchase-orders/${id}/send`).then(unwrap),
    close: (id) => api.post(`/purchase-orders/${id}/close`).then(unwrap),
};

export const goodsReceiptsApi = {
    list: (params) => api.get('/goods-receipts', { params }),
    get: (id) => api.get(`/goods-receipts/${id}`).then(unwrap),
    create: (payload) => api.post('/goods-receipts', payload).then(unwrap),
    confirm: (id) => api.post(`/goods-receipts/${id}/confirm`).then(unwrap),
};

export const adjustmentsApi = {
    list: (params) => api.get('/inventory-adjustments', { params }),
    get: (id) => api.get(`/inventory-adjustments/${id}`).then(unwrap),
    create: (payload) => api.post('/inventory-adjustments', payload).then(unwrap),
    approve: (id) => api.post(`/inventory-adjustments/${id}/approve`).then(unwrap),
};

export const transfersApi = {
    list: (params) => api.get('/stock-transfers', { params }),
    get: (id) => api.get(`/stock-transfers/${id}`).then(unwrap),
    create: (payload) => api.post('/stock-transfers', payload).then(unwrap),
    approve: (id) => api.post(`/stock-transfers/${id}/approve`).then(unwrap),
    ship: (id) => api.post(`/stock-transfers/${id}/ship`).then(unwrap),
    receive: (id) => api.post(`/stock-transfers/${id}/receive`).then(unwrap),
    complete: (id) => api.post(`/stock-transfers/${id}/complete`).then(unwrap),
};

export const countsApi = {
    list: (params) => api.get('/inventory-counts', { params }),
    get: (id) => api.get(`/inventory-counts/${id}`).then(unwrap),
    create: (payload) => api.post('/inventory-counts', payload).then(unwrap),
    start: (id) => api.post(`/inventory-counts/${id}/start`).then(unwrap),
    finalize: (id) => api.post(`/inventory-counts/${id}/finalize`).then(unwrap),
};

export const dashboardApi = {
    summary: () => api.get('/dashboard/summary').then(unwrap),
    arrivalsToday: () => api.get('/dashboard/arrivals-today').then(unwrap),
    recentActivity: () => api.get('/dashboard/recent-activity').then(unwrap),
    warehouseStats: () => api.get('/dashboard/warehouse-stats').then(unwrap),
    employeeActivity: () => api.get('/dashboard/employee-activity').then(unwrap),
    orderTrends: (period = 'day') => api.get('/dashboard/order-trends', { params: { period } }).then(unwrap),
};

export const reportsApi = {
    downloadUrl: (type, format = 'pdf') => `/api/v1/reports/${type}?format=${format}`,
};

export const auditApi = {
    list: (params) => api.get('/audit-logs', { params }),
    get: (id) => api.get(`/audit-logs/${id}`).then(unwrap),
};

export const notificationsApi = {
    list: (params) => api.get('/notifications', { params }),
    markRead: (id) => api.post(`/notifications/${id}/read`).then(unwrap),
    markAllRead: () => api.post('/notifications/read-all').then(unwrap),
};

export const searchApi = {
    search: (q) => api.get('/search', { params: { q } }).then(unwrap),
};
