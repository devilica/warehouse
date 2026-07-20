<script setup>
defineProps({
    variant: { type: String, default: 'primary' },
    size: { type: String, default: 'md' },
    loading: { type: Boolean, default: false },
    type: { type: String, default: 'button' },
});
</script>

<template>
    <button
        :type="type"
        :disabled="loading"
        class="inline-flex items-center justify-center gap-2 rounded-xl font-medium transition focus:outline-none focus:ring-2 focus:ring-indigo-500/40 disabled:opacity-60"
        :class="{
            'bg-indigo-600 text-white hover:bg-indigo-500 shadow-sm': variant === 'primary',
            'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50': variant === 'secondary',
            'bg-red-600 text-white hover:bg-red-500': variant === 'danger',
            'text-slate-600 hover:bg-slate-100': variant === 'ghost',
            'px-3 py-1.5 text-sm': size === 'sm',
            'px-4 py-2 text-sm': size === 'md',
            'px-5 py-2.5': size === 'lg',
        }"
    >
        <svg v-if="loading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
        </svg>
        <slot />
    </button>
</template>
