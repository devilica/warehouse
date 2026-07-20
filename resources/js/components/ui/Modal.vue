<script setup>
defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: '' },
    size: { type: String, default: 'md' },
});
defineEmits(['close']);
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="$emit('close')" />
            <div
                class="relative max-h-[90vh] w-full overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl"
                :class="{ 'max-w-md': size === 'sm', 'max-w-lg': size === 'md', 'max-w-2xl': size === 'lg', 'max-w-4xl': size === 'xl' }"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">{{ title }}</h2>
                    <button class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="$emit('close')">✕</button>
                </div>
                <slot />
            </div>
        </div>
    </Teleport>
</template>
