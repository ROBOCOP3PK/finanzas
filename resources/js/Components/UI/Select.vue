<template>
    <div class="w-full">
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <select
            :id="id"
            :value="modelValue"
            :disabled="disabled"
            :required="required"
            @change="$emit('update:modelValue', $event.target.value)"
            :class="[
                'block w-full rounded-lg border px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2',
                error
                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                    : 'border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-primary',
                'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100',
                { 'opacity-50 cursor-not-allowed': disabled }
            ]"
        >
            <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
            <option
                v-for="option in options"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>
        <p v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    options: {
        type: Array,
        required: true
    },
    placeholder: String,
    disabled: Boolean,
    required: Boolean,
    error: String,
    id: String
});

defineEmits(['update:modelValue']);

const id = computed(() => props.id || `select-${Math.random().toString(36).substr(2, 9)}`);
</script>
