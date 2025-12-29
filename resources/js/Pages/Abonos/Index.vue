<template>
    <div class="p-4">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Abonos</h1>
            <router-link to="/abonos/nuevo">
                <Button size="sm">
                    <PlusIcon class="w-4 h-4 mr-1" />
                    Nuevo
                </Button>
            </router-link>
        </div>

        <div v-if="abonosStore.loading" class="text-center py-8">
            <div class="animate-spin w-8 h-8 border-4 border-primary border-t-transparent rounded-full mx-auto"></div>
        </div>

        <div v-else-if="abonosStore.abonos.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            No hay abonos registrados
        </div>

        <div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
            <div
                v-for="abono in abonosStore.abonos"
                :key="abono.id"
                class="flex items-center justify-between p-4"
            >
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ abono.nota || 'Abono' }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(abono.fecha) }}</p>
                </div>
                <p class="font-semibold text-green-600 dark:text-green-400">
                    +{{ formatCurrency(abono.valor) }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { PlusIcon } from '@heroicons/vue/24/outline';
import Button from '../../Components/UI/Button.vue';
import { useAbonosStore } from '../../Stores/abonos';
import { useCurrency } from '../../Composables/useCurrency';

const abonosStore = useAbonosStore();
const { formatCurrency } = useCurrency();

onMounted(() => {
    abonosStore.cargarAbonos();
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-CO', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};
</script>
