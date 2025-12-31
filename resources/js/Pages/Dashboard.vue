<template>
    <div class="p-4 space-y-4 max-w-full overflow-hidden">
        <!-- Skeleton mientras carga -->
        <DashboardSkeleton v-if="dashboardStore.loading && !dashboardStore.resumenMes.mes" />

        <template v-else>
        <!-- Cards principales: Deuda y Gasto del Mes -->
        <div class="grid grid-cols-2 gap-3">
            <!-- Card Deuda - clickeable para ir a abonos -->
            <router-link
                to="/abonos"
                class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 block hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
            >
                <div class="flex items-center justify-between mb-1">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Deuda {{ nombrePersona2 }}</p>
                    <ChevronRightIcon class="w-4 h-4 text-gray-400" />
                </div>
                <p class="text-xl font-bold" :class="dashboardStore.deudaPersona2 > 0 ? 'text-red-500' : 'text-green-500'">
                    {{ formatCurrency(dashboardStore.deudaPersona2) }}
                </p>
            </router-link>
            <!-- Card Gasto Mes -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Gasto este mes</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ formatCurrency(dashboardStore.gastoMesActual) }}
                </p>
            </div>
        </div>

        <!-- Alerta Gastos Recurrentes -->
        <AlertaRecurrentes
            :cantidad="dashboardStore.pendientesRecurrentes"
            @registrar="registrarRecurrentes"
        />

        <!-- Resumen por Categorías -->
        <ResumenCategorias
            :categorias="dashboardStore.porCategoria"
            :mes="dashboardStore.resumenMes.mes || new Date().getMonth() + 1"
            :anio="dashboardStore.resumenMes.anio || new Date().getFullYear()"
        />

        <!-- Resumen del Mes -->
        <ResumenMes
            :gastosPersonal="dashboardStore.resumenMes.gastos_personal"
            :gastosPareja="dashboardStore.resumenMes.gastos_pareja"
            :gastosCompartido="dashboardStore.resumenMes.gastos_compartido"
            :totalAbonos="dashboardStore.resumenMes.total_abonos"
            :porcentajePersona2="dashboardStore.porcentajePersona2"
        />

        <!-- Toast -->
        <Toast
            :show="showToast"
            :message="toastMessage"
            :type="toastType"
            @close="showToast = false"
        />
        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { ChevronRightIcon } from '@heroicons/vue/24/outline';
import AlertaRecurrentes from '../Components/Dashboard/AlertaRecurrentes.vue';
import ResumenMes from '../Components/Dashboard/ResumenMes.vue';
import ResumenCategorias from '../Components/Dashboard/ResumenCategorias.vue';
import DashboardSkeleton from '../Components/Dashboard/DashboardSkeleton.vue';
import Toast from '../Components/UI/Toast.vue';
import { useDashboardStore } from '../Stores/dashboard';
import { useGastosRecurrentesStore } from '../Stores/gastosRecurrentes';
import { useConfigStore } from '../Stores/config';
import { useCurrency } from '../Composables/useCurrency';

const dashboardStore = useDashboardStore();
const gastosRecurrentesStore = useGastosRecurrentesStore();
const configStore = useConfigStore();
const { formatCurrency } = useCurrency();

const nombrePersona2 = computed(() => configStore.nombre_persona_2 || 'Usuario 2');

const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

onMounted(async () => {
    await Promise.all([
        dashboardStore.cargarDashboard(),
        configStore.cargarConfiguracion()
    ]);
});

const registrarRecurrentes = async () => {
    try {
        await gastosRecurrentesStore.registrarTodosPendientes();
        toastMessage.value = 'Gastos recurrentes registrados';
        toastType.value = 'success';
        showToast.value = true;
        await dashboardStore.cargarDashboard();
    } catch (error) {
        toastMessage.value = 'Error al registrar gastos recurrentes';
        toastType.value = 'error';
        showToast.value = true;
    }
};
</script>
