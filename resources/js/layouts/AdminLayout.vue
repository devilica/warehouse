<script setup>
import AppSidebar from '@/components/ui/AppSidebar.vue';
import AppTopbar from '@/components/ui/AppTopbar.vue';
</script>

<template>
    <div class="flex min-h-screen bg-slate-100/80">
        <AppSidebar />
        <div class="flex min-h-screen min-w-0 flex-1 flex-col">
            <AppTopbar />
            <main class="flex-1 p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
