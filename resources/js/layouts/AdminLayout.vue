<script setup>
import { ref } from 'vue';
import AppSidebar from '@/components/ui/AppSidebar.vue';
import AppTopbar from '@/components/ui/AppTopbar.vue';

const menuOpen = ref(false);
</script>

<template>
    <div class="flex min-h-screen overflow-x-hidden bg-slate-100/80">
        <div v-if="menuOpen" class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" @click="menuOpen = false" />
        <AppSidebar :open="menuOpen" @close="menuOpen = false" />
        <div class="flex min-h-screen min-w-0 flex-1 flex-col">
            <AppTopbar @menu="menuOpen = true" />
            <main class="flex-1 overflow-x-hidden p-4 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
