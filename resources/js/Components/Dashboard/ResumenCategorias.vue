<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
        <!-- Header con mes -->
        <div class="bg-primary dark:bg-indigo-600 px-4 py-3">
            <h2 class="text-white font-semibold text-lg">{{ nombreMes }} {{ anio }}</h2>
        </div>

        <!-- Lista de categorías -->
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            <div
                v-for="categoria in categorias"
                :key="categoria.categoria_id"
                class="flex items-center gap-3 p-3"
            >
                <!-- Icono y nombre -->
                <div class="flex items-center gap-2 w-28 flex-shrink-0">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
                        {{ categoria.nombre }}
                    </span>
                    <div
                        class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                        :style="{ backgroundColor: categoria.color + '20' }"
                    >
                        <i
                            v-if="categoria.icono"
                            :class="categoria.icono"
                            class="text-sm"
                            :style="{ color: categoria.color }"
                        ></i>
                        <span v-else class="text-xs">📦</span>
                    </div>
                </div>

                <!-- Barra de progreso y valores -->
                <div class="flex-1 min-w-0">
                    <!-- Barra con porcentaje -->
                    <div class="relative h-7 bg-gray-200 dark:bg-gray-700 rounded overflow-hidden">
                        <div
                            class="absolute inset-y-0 left-0 rounded transition-all duration-500"
                            :style="{
                                width: categoria.porcentaje + '%',
                                backgroundColor: categoria.color || '#6366f1'
                            }"
                        ></div>
                        <span class="absolute inset-0 flex items-center px-2 text-sm font-medium text-white drop-shadow">
                            {{ categoria.porcentaje }}%
                        </span>
                    </div>
                    <!-- Valor debajo -->
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ formatCurrency(categoria.total) }}
                    </p>
                </div>
            </div>

            <!-- Estado vacío -->
            <div v-if="categorias.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
                No hay gastos registrados este mes
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useCurrency } from '../../Composables/useCurrency';

const props = defineProps({
    categorias: {
        type: Array,
        default: () => []
    },
    mes: {
        type: Number,
        default: () => new Date().getMonth() + 1
    },
    anio: {
        type: Number,
        default: () => new Date().getFullYear()
    }
});

const { formatCurrency } = useCurrency();

const nombreMes = computed(() => {
    const meses = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    return meses[props.mes - 1] || '';
});
</script>
