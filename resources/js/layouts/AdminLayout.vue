<script setup>
import AppSidebar from '@/components/ui/AppSidebar.vue';
import AppTopbar from '@/components/ui/AppTopbar.vue';
</script>

<template>
    <div class="flex min-h-screen bg-slate-50">
        <AppSidebar />
        <div class="flex min-h-screen flex-1 flex-col">
            <AppTopbar />
            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
