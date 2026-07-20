import { defineStore } from 'pinia';
import { authApi } from '@/api';
import { getErrorMessage } from '@/api/client';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('wms_token'),
        user: JSON.parse(localStorage.getItem('wms_user') || 'null'),
        loading: false,
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
        permissions: (state) => state.user?.permissions ?? [],
        roles: (state) => state.user?.roles ?? [],
    },
    actions: {
        can(permission) {
            if (this.roles.includes('super-admin')) return true;
            return this.permissions.includes(permission);
        },
        canAny(list) {
            return list.some((p) => this.can(p));
        },
        persist(token, user) {
            this.token = token;
            this.user = user;
            localStorage.setItem('wms_token', token);
            localStorage.setItem('wms_user', JSON.stringify(user));
        },
        clear() {
            this.token = null;
            this.user = null;
            localStorage.removeItem('wms_token');
            localStorage.removeItem('wms_user');
        },
        async login(credentials) {
            this.loading = true;
            try {
                const result = await authApi.login(credentials);
                this.persist(result.token, result.user);
                return result;
            } catch (error) {
                throw getErrorMessage(error);
            } finally {
                this.loading = false;
            }
        },
        async fetchMe() {
            if (!this.token) return null;
            try {
                const user = await authApi.me();
                this.user = user;
                localStorage.setItem('wms_user', JSON.stringify(user));
                return user;
            } catch {
                this.clear();
                return null;
            }
        },
        async logout() {
            try {
                if (this.token) await authApi.logout();
            } finally {
                this.clear();
            }
        },
    },
});
