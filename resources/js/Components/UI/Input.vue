<template>
    <div class="w-full">
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <div class="relative">
            <input
                :id="id"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :min="min"
                :max="max"
                :step="step"
                @input="$emit('update:modelValue', $event.target.value)"
                @blur="$emit('blur', $event)"
                @focus="$emit('focus', $event)"
                :class="[
                    'block w-full rounded-lg border px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2',
                    error
                        ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                        : 'border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-primary dark:focus:border-indigo-500 dark:focus:ring-indigo-500',
                    'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100',
                    'placeholder-gray-400 dark:placeholder-gray-500',
                    { 'opacity-50 cursor-not-allowed': disabled }
                ]"
            />
        </div>
        <p v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</p>
        <p v-else-if="hint" class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ hint }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    type: {
        type: String,
        default: 'text'
    },
    placeholder: String,
    disabled: Boolean,
    required: Boolean,
    error: String,
    hint: String,
    min: [String, Number],
    max: [String, Number],
    step: [String, Number],
    id: String
});

defineEmits(['update:modelValue', 'blur', 'focus']);

const id = computed(() => props.id || `input-${Math.random().toString(36).substr(2, 9)}`);
</script>
