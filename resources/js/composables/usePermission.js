import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

export function usePermission() {
    const auth = useAuthStore();

    const can = (permission) => auth.can(permission);
    const canAny = (permissions) => auth.canAny(permissions);

    const isAdmin = computed(() => auth.roles.includes('super-admin') || auth.roles.includes('administrator'));

    return { can, canAny, isAdmin };
}
