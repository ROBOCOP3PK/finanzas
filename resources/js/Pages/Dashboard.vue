<template>
    <div class="p-4 space-y-4">
        <!-- Saldo Card -->
        <SaldoCard
            :saldo="dashboardStore.saldoPendiente"
            :nombrePersona="dashboardStore.configuracion.nombre_persona_1"
        />

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

        <!-- Resumen del Mes -->
        <ResumenMes
            :nombrePersona1="dashboardStore.configuracion.nombre_persona_1"
            :nombrePersona2="dashboardStore.configuracion.nombre_persona_2"
            :gastosPersona1="dashboardStore.resumenMes.gastos_persona_1"
            :gastosPersona2="dashboardStore.resumenMes.gastos_persona_2"
            :gastosCasa="dashboardStore.resumenMes.gastos_casa"
            :totalAbonos="dashboardStore.resumenMes.total_abonos"
        />

        <!-- Últimos Movimientos -->
        <UltimosMovimientos :movimientos="dashboardStore.ultimosMovimientos" />

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
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import SaldoCard from '../Components/Dashboard/SaldoCard.vue';
import AlertaRecurrentes from '../Components/Dashboard/AlertaRecurrentes.vue';
import PlantillasRapidas from '../Components/Dashboard/PlantillasRapidas.vue';
import ResumenMes from '../Components/Dashboard/ResumenMes.vue';
import UltimosMovimientos from '../Components/Dashboard/UltimosMovimientos.vue';
import Modal from '../Components/UI/Modal.vue';
import Input from '../Components/UI/Input.vue';
import Button from '../Components/UI/Button.vue';
import Toast from '../Components/UI/Toast.vue';
import { useDashboardStore } from '../Stores/dashboard';
import { usePlantillasStore } from '../Stores/plantillas';
import { useGastosRecurrentesStore } from '../Stores/gastosRecurrentes';
import { useConfigStore } from '../Stores/config';

const router = useRouter();
const dashboardStore = useDashboardStore();
const plantillasStore = usePlantillasStore();
const gastosRecurrentesStore = useGastosRecurrentesStore();
const configStore = useConfigStore();

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

        // Recargar dashboard
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
