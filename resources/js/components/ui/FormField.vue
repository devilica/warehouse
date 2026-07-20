<script setup>
defineProps({
    label: { type: String, required: true },
    modelValue: { type: [String, Number], default: '' },
    type: { type: String, default: 'text' },
    error: { type: String, default: '' },
    required: { type: Boolean, default: false },
});
defineEmits(['update:modelValue']);
</script>

<template>
    <label class="block space-y-1.5">
        <span class="text-sm font-medium text-slate-700">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </span>
        <select
            v-if="type === 'select'"
            :value="modelValue"
            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <slot />
        </select>
        <textarea
            v-else-if="type === 'textarea'"
            :value="modelValue"
            rows="4"
            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <input
            v-else
            :type="type"
            :value="modelValue"
            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
    </label>
</template>
