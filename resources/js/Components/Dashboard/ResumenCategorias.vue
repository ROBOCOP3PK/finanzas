<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
        <!-- Header con mes y flechas de navegación -->
        <div class="bg-primary dark:bg-indigo-600 px-4 py-3 flex items-center justify-between">
            <button
                @click="mesAnterior"
                :disabled="loading"
                class="p-1 rounded-full hover:bg-white/20 transition-colors disabled:opacity-50"
            >
                <ChevronLeftIcon class="w-5 h-5 text-white" />
            </button>

            <h2 class="text-white font-semibold text-lg">
                {{ nombreMes }} {{ anio }}
            </h2>

            <button
                @click="mesSiguiente"
                :disabled="loading || esMesActual"
                class="p-1 rounded-full hover:bg-white/20 transition-colors disabled:opacity-50"
            >
                <ChevronRightIcon class="w-5 h-5 text-white" />
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="p-6 flex justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>

        <!-- Lista de categorías -->
        <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
            <div
                v-for="categoria in categorias"
                :key="categoria.categoria_id"
                class="overflow-hidden"
            >
                <!-- Fila de categoría (clickeable) -->
                <div
                    @click="toggleCategoria(categoria.categoria_id)"
                    class="flex items-center gap-3 p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
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

                    <!-- Icono de expandir/colapsar -->
                    <ChevronDownIcon
                        class="w-5 h-5 text-gray-400 transition-transform duration-200 flex-shrink-0"
                        :class="{ 'rotate-180': expandedCategoria === categoria.categoria_id }"
                    />
                </div>

                <!-- Acordeón: Lista de gastos de la categoría -->
                <transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="max-h-0 opacity-0"
                    enter-to-class="max-h-[500px] opacity-100"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="max-h-[500px] opacity-100"
                    leave-to-class="max-h-0 opacity-0"
                >
                    <div
                        v-if="expandedCategoria === categoria.categoria_id"
                        class="bg-gray-50 dark:bg-gray-900/50 overflow-hidden"
                    >
                        <!-- Loading gastos -->
                        <div v-if="loadingGastos" class="p-4 flex justify-center">
                            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-primary"></div>
                        </div>

                        <!-- Lista de gastos -->
                        <div v-else-if="gastosCategoria.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
                            <div
                                v-for="gasto in gastosCategoria"
                                :key="gasto.id"
                                class="px-4 py-2 flex items-center justify-between"
                            >
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
                                        {{ gasto.concepto }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatFecha(gasto.fecha) }}
                                        <span v-if="gasto.medio_pago" class="ml-1">• {{ gasto.medio_pago }}</span>
                                    </p>
                                </div>
                                <div class="text-right flex-shrink-0 ml-2">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ formatCurrency(gasto.valor) }}
                                    </p>
                                    <span
                                        class="text-xs px-1.5 py-0.5 rounded"
                                        :class="tipoClass(gasto.tipo)"
                                    >
                                        {{ tipoLabel(gasto.tipo) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Sin gastos -->
                        <div v-else class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            No hay gastos en esta categoría
                        </div>
                    </div>
                </transition>
            </div>

            <!-- Estado vacío -->
            <div v-if="categorias.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
                No hay gastos registrados este mes
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { ChevronLeftIcon, ChevronRightIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';
import { useDashboardStore } from '../../Stores/dashboard';
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

const dashboardStore = useDashboardStore();
const { formatCurrency } = useCurrency();

const expandedCategoria = ref(null);
const gastosCategoria = ref([]);
const loadingGastos = ref(false);

const loading = computed(() => dashboardStore.loadingCategorias);

const nombreMes = computed(() => {
    const meses = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    const mes = dashboardStore.porCategoriaMes || props.mes;
    return meses[mes - 1] || '';
});

const anio = computed(() => dashboardStore.porCategoriaAnio || props.anio);

const esMesActual = computed(() => {
    const now = new Date();
    return dashboardStore.porCategoriaMes === (now.getMonth() + 1) &&
           dashboardStore.porCategoriaAnio === now.getFullYear();
});

const mesAnterior = () => {
    expandedCategoria.value = null;
    gastosCategoria.value = [];
    dashboardStore.cambiarMesCategorias('anterior');
};

const mesSiguiente = () => {
    expandedCategoria.value = null;
    gastosCategoria.value = [];
    dashboardStore.cambiarMesCategorias('siguiente');
};

const toggleCategoria = async (categoriaId) => {
    if (expandedCategoria.value === categoriaId) {
        expandedCategoria.value = null;
        gastosCategoria.value = [];
        return;
    }

    expandedCategoria.value = categoriaId;
    loadingGastos.value = true;

    try {
        gastosCategoria.value = await dashboardStore.cargarGastosCategoria(
            categoriaId,
            dashboardStore.porCategoriaMes,
            dashboardStore.porCategoriaAnio
        );
    } finally {
        loadingGastos.value = false;
    }
};

const formatFecha = (fecha) => {
    const date = new Date(fecha + 'T00:00:00');
    return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
};

const tipoLabel = (tipo) => {
    const labels = {
        personal: 'Personal',
        pareja: 'Pareja',
        compartido: 'Compartido'
    };
    return labels[tipo] || tipo;
};

const tipoClass = (tipo) => {
    const classes = {
        personal: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        pareja: 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400',
        compartido: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'
    };
    return classes[tipo] || 'bg-gray-100 text-gray-700';
};

// Cerrar acordeón cuando cambian las categorías
watch(() => props.categorias, () => {
    expandedCategoria.value = null;
    gastosCategoria.value = [];
});
</script>
