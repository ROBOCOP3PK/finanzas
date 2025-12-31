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

        <!-- Plantillas Rápidas -->
        <PlantillasRapidas
            :plantillas="plantillasStore.rapidas"
            @usar="abrirModalPlantilla"
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

        <!-- Modal para usar plantilla -->
        <Modal :show="showPlantillaModal" title="Registrar Gasto" @close="showPlantillaModal = false">
            <div class="space-y-4">
                <p class="text-gray-600 dark:text-gray-400">
                    {{ plantillaSeleccionada?.nombre }}
                </p>
                <Input
                    v-model="plantillaFecha"
                    type="date"
                    label="Fecha"
                />
                <Input
                    v-model="plantillaValor"
                    type="number"
                    label="Valor"
                    :placeholder="plantillaSeleccionada?.valor?.toString() || '0'"
                />
            </div>
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="secondary" @click="showPlantillaModal = false">Cancelar</Button>
                    <Button @click="usarPlantilla" :loading="usandoPlantilla">Guardar</Button>
                </div>
            </template>
        </Modal>

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
import PlantillasRapidas from '../Components/Dashboard/PlantillasRapidas.vue';
import ResumenMes from '../Components/Dashboard/ResumenMes.vue';
import ResumenCategorias from '../Components/Dashboard/ResumenCategorias.vue';
import DashboardSkeleton from '../Components/Dashboard/DashboardSkeleton.vue';
import Modal from '../Components/UI/Modal.vue';
import Input from '../Components/UI/Input.vue';
import Button from '../Components/UI/Button.vue';
import Toast from '../Components/UI/Toast.vue';
import { useDashboardStore } from '../Stores/dashboard';
import { usePlantillasStore } from '../Stores/plantillas';
import { useGastosRecurrentesStore } from '../Stores/gastosRecurrentes';
import { useConfigStore } from '../Stores/config';
import { useCurrency } from '../Composables/useCurrency';

const dashboardStore = useDashboardStore();
const plantillasStore = usePlantillasStore();
const gastosRecurrentesStore = useGastosRecurrentesStore();
const configStore = useConfigStore();
const { formatCurrency } = useCurrency();

const nombrePersona2 = computed(() => configStore.nombre_persona_2 || 'Pareja');

const showPlantillaModal = ref(false);
const plantillaSeleccionada = ref(null);
const plantillaFecha = ref(new Date().toISOString().split('T')[0]);
const plantillaValor = ref('');
const usandoPlantilla = ref(false);

const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

onMounted(async () => {
    await Promise.all([
        dashboardStore.cargarDashboard(),
        plantillasStore.cargarRapidas(),
        configStore.cargarConfiguracion()
    ]);
});

const abrirModalPlantilla = (plantilla) => {
    plantillaSeleccionada.value = plantilla;
    plantillaFecha.value = new Date().toISOString().split('T')[0];
    plantillaValor.value = plantilla.valor || '';
    showPlantillaModal.value = true;
};

const usarPlantilla = async () => {
    if (!plantillaSeleccionada.value) return;

    usandoPlantilla.value = true;
    try {
        await plantillasStore.usarPlantilla(
            plantillaSeleccionada.value.id,
            plantillaFecha.value,
            plantillaValor.value || null
        );

        showPlantillaModal.value = false;
        toastMessage.value = 'Gasto registrado correctamente';
        toastType.value = 'success';
        showToast.value = true;

        await dashboardStore.cargarDashboard();
    } catch (error) {
        toastMessage.value = 'Error al registrar el gasto';
        toastType.value = 'error';
        showToast.value = true;
    } finally {
        usandoPlantilla.value = false;
    }
};

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
