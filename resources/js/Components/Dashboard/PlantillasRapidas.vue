<template>
    <Card title="Registro Rápido">
        <div v-if="plantillas.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-4">
            <p>No hay plantillas configuradas</p>
            <router-link to="/configuracion" class="text-primary dark:text-indigo-400 text-sm">
                Crear plantilla
            </router-link>
        </div>
        <div v-else class="grid grid-cols-3 gap-2">
            <button
                v-for="plantilla in plantillas"
                :key="plantilla.id"
                @click="$emit('usar', plantilla)"
                class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
            >
                <span class="text-2xl mb-1">{{ getEmoji(plantilla.categoria?.icono) }}</span>
                <span class="text-xs font-medium text-gray-900 dark:text-white text-center truncate w-full">
                    {{ plantilla.nombre }}
                </span>
                <span v-if="plantilla.valor" class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatCurrency(plantilla.valor) }}
                </span>
            </button>
        </div>
    </Card>
</template>

<script setup>
import Card from '../UI/Card.vue';
import { useCurrency } from '../../Composables/useCurrency';

defineProps({
    plantillas: {
        type: Array,
        default: () => []
    }
});

defineEmits(['usar']);

const { formatCurrencyCompact: formatCurrency } = useCurrency();

const getEmoji = (icono) => {
    const emojis = {
        'utensils': '🍽️',
        'car': '🚗',
        'zap': '⚡',
        'film': '🎬',
        'heart-pulse': '❤️',
        'more-horizontal': '📦',
        'credit-card': '💳',
        'smartphone': '📱',
        'banknote': '💵'
    };
    return emojis[icono] || '📝';
};
</script>
