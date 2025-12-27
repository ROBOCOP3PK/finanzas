<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-4"
        >
            <div
                v-if="show"
                class="fixed bottom-20 left-4 right-4 z-50 sm:left-auto sm:right-4 sm:max-w-sm"
            >
                <div
                    :class="[
                        'flex items-center gap-3 p-4 rounded-lg shadow-lg',
                        variantClasses
                    ]"
                >
                    <component :is="icon" class="w-5 h-5 flex-shrink-0" />
                    <p class="flex-1 text-sm font-medium">{{ message }}</p>
                    <button
                        @click="$emit('close')"
                        class="p-1 rounded hover:bg-black/10"
                    >
                        <XMarkIcon class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed, watch } from 'vue';
import {
    XMarkIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
    InformationCircleIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    show: Boolean,
    message: String,
    type: {
        type: String,
        default: 'info',
        validator: (v) => ['success', 'error', 'warning', 'info'].includes(v)
    },
    duration: {
        type: Number,
        default: 3000
    }
});

const emit = defineEmits(['close']);

const icon = computed(() => {
    const icons = {
        success: CheckCircleIcon,
        error: ExclamationCircleIcon,
        warning: ExclamationTriangleIcon,
        info: InformationCircleIcon
    };
    return icons[props.type];
});

const variantClasses = computed(() => {
    const variants = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        warning: 'bg-yellow-500 text-white',
        info: 'bg-blue-500 text-white'
    };
    return variants[props.type];
});

// Auto-hide
watch(() => props.show, (show) => {
    if (show && props.duration > 0) {
        setTimeout(() => emit('close'), props.duration);
    }
});
</script>
