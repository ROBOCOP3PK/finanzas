<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <SharedDataNav
            :ownerName="sharedDashboard.ownerName"
            :activeTab="activeTab"
            @back="goBack"
            @tab-change="activeTab = $event"
        />

        <div class="p-4 pb-24">
            <!-- Loading -->
            <div v-if="sharedDashboard.loading && !sharedDashboard.hasData" class="flex justify-center py-12">
                <div class="animate-spin w-8 h-8 border-4 border-primary border-t-transparent rounded-full"></div>
            </div>

            <template v-else>
                <!-- Tab: Nuevo Gasto -->
                <div v-if="activeTab === 'nuevo'">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                        <h3 class="font-medium text-gray-900 dark:text-white mb-4">
                            Solicitar nuevo gasto
                        </h3>
                        <SharedGastoForm
                            :categorias="sharedDashboard.categorias"
                            :mediosPago="sharedDashboard.mediosPago"
                            :loading="dataShareStore.loading"
                            @submit="crearSolicitud"
                        />
                    </div>
                </div>

                <!-- Tab: Resumen -->
                <div v-else-if="activeTab === 'resumen'" class="space-y-4">
                    <!-- Cards principales -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Te debe</p>
                            <p
                                class="text-xl font-bold"
                                :class="sharedDashboard.deudaPersona2 > 0 ? 'text-red-500' : 'text-green-500'"
                            >
                                {{ formatCurrency(sharedDashboard.deudaPersona2) }}
                            </p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Gasto este mes</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ formatCurrency(sharedDashboard.gastoMesActual) }}
                            </p>
                        </div>
                    </div>

                    <!-- Resumen del mes -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                        <h3 class="font-medium text-gray-900 dark:text-white mb-3">Resumen del Mes</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Personal</span>
                                <span class="text-gray-900 dark:text-white font-medium">
                                    {{ formatCurrency(sharedDashboard.resumenMes.gastos_personal || 0) }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">{{ configStore.getNombreTipo('pareja') }}</span>
                                <span class="text-gray-900 dark:text-white font-medium">
                                    {{ formatCurrency(sharedDashboard.resumenMes.gastos_pareja || 0) }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Compartido</span>
                                <span class="text-gray-900 dark:text-white font-medium">
                                    {{ formatCurrency(sharedDashboard.resumenMes.gastos_compartido || 0) }}
                                </span>
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                                <div class="flex justify-between text-sm font-medium">
                                    <span class="text-gray-900 dark:text-white">Total</span>
                                    <span class="text-gray-900 dark:text-white">
                                        {{ formatCurrency(sharedDashboard.resumenMes.total_gastos || 0) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Por categoria -->
                    <div v-if="sharedDashboard.porCategoria.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                        <h3 class="font-medium text-gray-900 dark:text-white mb-3">Por Categoria</h3>
                        <div class="space-y-3">
                            <div v-for="cat in sharedDashboard.porCategoria" :key="cat.categoria_id" class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-lg flex items-center justify-center"
                                    :style="{ backgroundColor: cat.color + '20' }"
                                >
                                    <i v-if="cat.icono" :class="cat.icono" :style="{ color: cat.color }"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-700 dark:text-gray-300">{{ cat.nombre }}</span>
                                        <span class="text-gray-900 dark:text-white font-medium">{{ formatCurrency(cat.total) }}</span>
                                    </div>
                                    <div class="h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div
                                            class="h-full rounded-full"
                                            :style="{ width: cat.porcentaje + '%', backgroundColor: cat.color }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ultimos movimientos -->
                    <div v-if="sharedDashboard.ultimosMovimientos.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                        <h3 class="font-medium text-gray-900 dark:text-white mb-3">Ultimos Movimientos</h3>
                        <div class="space-y-3">
                            <div
                                v-for="mov in sharedDashboard.ultimosMovimientos"
                                :key="mov.id"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full flex items-center justify-center"
                                        :style="{ backgroundColor: (mov.categoria_color || '#6B7280') + '20' }"
                                    >
                                        <ArrowUpIcon class="w-4 h-4 text-red-500" />
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-900 dark:text-white">{{ mov.concepto }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatDateShort(mov.fecha) }}</p>
                                    </div>
                                </div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ formatCurrency(mov.valor) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Historial -->
                <div v-else-if="activeTab === 'historial'">
                    <!-- Filtros -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Desde</label>
                                <input
                                    v-model="filtros.desde"
                                    type="date"
                                    class="w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    @change="aplicarFiltros"
                                />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Hasta</label>
                                <input
                                    v-model="filtros.hasta"
                                    type="date"
                                    class="w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    @change="aplicarFiltros"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Lista de gastos -->
                    <div v-if="sharedDashboard.gastos.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
                        <div
                            v-for="gasto in sharedDashboard.gastos"
                            :key="gasto.id"
                            class="p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-lg flex items-center justify-center"
                                        :style="{ backgroundColor: (gasto.categoria?.color || '#6B7280') + '20' }"
                                    >
                                        <i
                                            v-if="gasto.categoria?.icono"
                                            :class="gasto.categoria.icono"
                                            :style="{ color: gasto.categoria?.color || '#6B7280' }"
                                        ></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ gasto.concepto }}</p>
                                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                            <span>{{ formatDateShort(gasto.fecha) }}</span>
                                            <span v-if="gasto.medio_pago">· {{ gasto.medio_pago.nombre }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900 dark:text-white">{{ formatCurrency(gasto.valor) }}</p>
                                    <span
                                        class="text-xs px-1.5 py-0.5 rounded"
                                        :class="{
                                            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': gasto.tipo === 'personal',
                                            'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400': gasto.tipo === 'pareja',
                                            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': gasto.tipo === 'compartido'
                                        }"
                                    >
                                        {{ gasto.tipo }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                        No hay gastos registrados
                    </div>

                    <!-- Paginacion -->
                    <div v-if="sharedDashboard.gastosMeta.last_page > 1" class="flex justify-center gap-2 mt-4">
                        <button
                            @click="cargarPagina(sharedDashboard.gastosMeta.current_page - 1)"
                            :disabled="sharedDashboard.gastosMeta.current_page === 1"
                            class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded disabled:opacity-50"
                        >
                            Anterior
                        </button>
                        <span class="px-3 py-1 text-gray-600 dark:text-gray-400">
                            {{ sharedDashboard.gastosMeta.current_page }} / {{ sharedDashboard.gastosMeta.last_page }}
                        </span>
                        <button
                            @click="cargarPagina(sharedDashboard.gastosMeta.current_page + 1)"
                            :disabled="sharedDashboard.gastosMeta.current_page === sharedDashboard.gastosMeta.last_page"
                            class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded disabled:opacity-50"
                        >
                            Siguiente
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <Toast :show="showToast" :message="toastMessage" :type="toastType" @close="showToast = false" />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ArrowUpIcon } from '@heroicons/vue/24/outline';
import SharedDataNav from '../../Components/Shared/SharedDataNav.vue';
import SharedGastoForm from '../../Components/Shared/SharedGastoForm.vue';
import Toast from '../../Components/UI/Toast.vue';
import { useDataShareStore } from '../../Stores/dataShare';
import { useSharedDashboardStore } from '../../Stores/sharedDashboard';
import { useConfigStore } from '../../Stores/config';
import { useCurrency } from '../../Composables/useCurrency';

const route = useRoute();
const router = useRouter();
const dataShareStore = useDataShareStore();
const sharedDashboard = useSharedDashboardStore();
const configStore = useConfigStore();
const { formatCurrency } = useCurrency();

const shareId = ref(route.params.shareId);
const activeTab = ref('nuevo');
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const filtros = ref({
    desde: '',
    hasta: ''
});

onMounted(async () => {
    await sharedDashboard.cargarTodo(shareId.value);
});

onUnmounted(() => {
    sharedDashboard.limpiar();
});

const goBack = () => {
    router.push('/shared-data');
};

const formatDateShort = (date) => {
    return new Date(date).toLocaleDateString('es-CO', {
        day: '2-digit',
        month: 'short'
    });
};

const crearSolicitud = async (data) => {
    const result = await dataShareStore.createPendingExpense(shareId.value, data);

    if (result.success) {
        toastMessage.value = result.message || 'Solicitud enviada';
        toastType.value = 'success';
    } else {
        toastMessage.value = result.error || 'Error al enviar solicitud';
        toastType.value = 'error';
    }
    showToast.value = true;
};

const aplicarFiltros = () => {
    sharedDashboard.setFiltros(filtros.value);
    sharedDashboard.cargarGastos(shareId.value, 1);
};

const cargarPagina = (page) => {
    sharedDashboard.cargarGastos(shareId.value, page);
};
</script>
