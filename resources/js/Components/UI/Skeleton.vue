<template>
    <div
        :class="[
            'animate-pulse bg-gray-200 dark:bg-gray-700 rounded',
            sizeClasses
        ]"
        :style="customStyle"
    ></div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    width: {
        type: String,
        default: 'full'
    },
    height: {
        type: String,
        default: '4'
    },
    rounded: {
        type: String,
        default: 'md'
    }
});

const sizeClasses = computed(() => {
    const classes = [];

    // Width
    if (props.width === 'full') {
        classes.push('w-full');
    } else if (props.width.includes('px') || props.width.includes('%')) {
        // Custom width handled by style
    } else {
        classes.push(`w-${props.width}`);
    }

    // Height
    if (props.height.includes('px')) {
        // Custom height handled by style
    } else {
        classes.push(`h-${props.height}`);
    }

    // Rounded
    classes.push(`rounded-${props.rounded}`);

    return classes.join(' ');
});

const customStyle = computed(() => {
    const style = {};
    if (props.width.includes('px') || props.width.includes('%')) {
        style.width = props.width;
    }
    if (props.height.includes('px')) {
        style.height = props.height;
    }
    return style;
});
</script>
