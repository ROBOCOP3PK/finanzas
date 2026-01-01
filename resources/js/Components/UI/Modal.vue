<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
            >
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-black/50"
                    @click="closeOnBackdrop && $emit('close')"
                ></div>

                <!-- Modal Content -->
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div
                        v-if="show"
                        :class="[
                            'relative w-full bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-2xl shadow-xl max-h-[90vh] flex flex-col',
                            maxWidthClass
                        ]"
                    >
                        <!-- Header -->
                        <div v-if="title" class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
                            <button
                                @click="$emit('close')"
                                class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <XMarkIcon class="w-5 h-5" />
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="p-4 overflow-y-auto flex-1">
                            <slot />
                        </div>

                        <!-- Footer -->
                        <div v-if="$slots.footer" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed, watch } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    show: Boolean,
    title: String,
    maxWidth: {
        type: String,
        default: 'md'
    },
    closeOnBackdrop: {
        type: Boolean,
        default: true
    }
});

defineEmits(['close']);

const maxWidthClass = computed(() => {
    const widths = {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl'
    };
    return widths[props.maxWidth];
});

// Prevent body scroll when modal is open
watch(() => props.show, (show) => {
    if (show) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});
</script>
