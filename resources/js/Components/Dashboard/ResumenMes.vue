<template>
    <Card title="Resumen del Mes">
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">Gastos personales</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(gastosPersonal) }}</span>
            </div>
            <!-- Solo mostrar si hay usuario 2 configurado -->
            <template v-if="tieneUsuario2">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Gastos {{ configStore.nombre_persona_2 }}</span>
                    <span class="font-medium text-red-500">{{ formatCurrency(gastosPareja) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Compartidos ({{ porcentajePersona2 }}% {{ configStore.nombre_persona_2 }})</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(gastosCompartido) }}</span>
                </div>
            </template>
            <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                <div class="flex justify-between items-center">
                    <span class="font-medium text-gray-900 dark:text-white">Total</span>
                    <span class="font-bold text-lg text-gray-900 dark:text-white">{{ formatCurrency(totalGastos) }}</span>
                </div>
            </div>
            <div v-if="tieneUsuario2 && totalAbonos > 0" class="flex justify-between items-center text-green-600 dark:text-green-400">
                <span>Abonos recibidos</span>
                <span class="font-medium">{{ formatCurrency(totalAbonos) }}</span>
            </div>
        </div>
    </Card>
</template>

<script setup>
import { computed } from 'vue';
import Card from '../UI/Card.vue';
import { useCurrency } from '../../Composables/useCurrency';
import { useConfigStore } from '../../Stores/config';

const props = defineProps({
    gastosPersonal: { type: Number, default: 0 },
    gastosPareja: { type: Number, default: 0 },
    gastosCompartido: { type: Number, default: 0 },
    totalAbonos: { type: Number, default: 0 },
    porcentajePersona2: { type: Number, default: 40 },
    tieneUsuario2: { type: Boolean, default: false }
});

const { formatCurrency } = useCurrency();
const configStore = useConfigStore();

// Si no hay usuario 2, el total solo incluye gastos personales
const totalGastos = computed(() => {
    if (props.tieneUsuario2) {
        return props.gastosPersonal + props.gastosPareja + props.gastosCompartido;
    }
    return props.gastosPersonal;
});
</script>
