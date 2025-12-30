<template>
    <div class="h-screen h-[-webkit-fill-available] flex flex-col overflow-hidden">
        <!-- Floating Actions -->
        <div v-if="serviciosStore.tieneAlertas" class="fixed top-0 right-0 z-50 safe-top safe-right">
            <div class="flex items-center gap-3 p-4">
                <!-- Alerta de servicios pendientes -->
                <button
                    @click="mostrarModalAlertas"
                    class="relative p-2.5 rounded-xl text-amber-600 hover:text-amber-700 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm shadow-sm transition-colors touch-target flex items-center justify-center animate-pulse"
                    title="Servicios pendientes"
                >
                    <BellAlertIcon class="w-5 h-5" />
                    <span
                        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-medium"
                    >
                        {{ serviciosStore.serviciosPendientesCount }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto overflow-x-hidden overscroll-contain">
            <div class="max-w-full main-content-padding top-content-padding">
                <slot />
            </div>
        </main>

        <!-- Bottom Navigation -->
        <BottomNav />

        <!-- Modal Alertas de Servicios -->
        <Modal :show="showModalAlertas" title="Servicios Pendientes" @close="cerrarModalAlertas">
            <div class="space-y-3">
                <!-- Info de dias restantes -->
                <div v-if="serviciosStore.alertas" class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 mb-4">
                    <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400">
                        <ExclamationTriangleIcon class="w-5 h-5" />
                        <span class="text-sm font-medium">
                            {{ serviciosStore.alertas.dias_restantes }} dias para el restablecimiento
                        </span>
                    </div>
                    <p class="text-xs text-amber-600 dark:text-amber-500 mt-1">
                        Fecha: {{ formatearFecha(serviciosStore.alertas.fecha_restablecimiento) }}
                    </p>
                </div>

                <!-- Lista de servicios pendientes -->
                <div v-if="serviciosStore.pendientesDetalle.length > 0" class="space-y-2">
                    <div
                        v-for="servicio in serviciosStore.pendientesDetalle"
                        :key="servicio.id"
                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg flex items-center justify-center"
                                :style="{ backgroundColor: servicio.color + '20' }"
                            >
                                <i :class="servicio.icono || 'pi pi-file'" :style="{ color: servicio.color }"></i>
                            </div>
                            <div>
                                <span class="text-gray-900 dark:text-white font-medium">{{ servicio.nombre }}</span>
                                <p v-if="servicio.valor_estimado" class="text-xs text-gray-500 dark:text-gray-400">
                                    ~{{ formatearValor(servicio.valor_estimado) }}
                                </p>
                            </div>
                        </div>
                        <button
                            @click="irAPagarServicio(servicio)"
                            class="px-3 py-1.5 bg-primary text-white text-sm rounded-lg hover:bg-primary/90 transition-colors"
                        >
                            Pagar
                        </button>
                    </div>
                </div>

                <p v-else class="text-center text-gray-500 dark:text-gray-400 py-4">
                    No hay servicios pendientes
                </p>
            </div>
            <template #footer>
                <div class="flex justify-end">
                    <button
                        @click="cerrarModalAlertas"
                        class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
                    >
                        Cerrar
                    </button>
                </div>
            </template>
        </Modal>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { BellAlertIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import BottomNav from './BottomNav.vue';
import Modal from '../UI/Modal.vue';
import { useServiciosStore } from '../../Stores/servicios';
import { useConfigStore } from '../../Stores/config';

const router = useRouter();
const serviciosStore = useServiciosStore();
const configStore = useConfigStore();

const showModalAlertas = ref(false);

const mostrarModalAlertas = async () => {
    await serviciosStore.cargarPendientesDetalle();
    showModalAlertas.value = true;
};

const cerrarModalAlertas = () => {
    showModalAlertas.value = false;
    serviciosStore.limpiarAlertas();
};

const irAPagarServicio = (servicio) => {
    showModalAlertas.value = false;
    router.push({
        path: '/gastos/nuevo',
        query: { servicio_id: servicio.id }
    });
};

const formatearFecha = (fecha) => {
    if (!fecha) return '';
    const date = new Date(fecha + 'T00:00:00');
    return date.toLocaleDateString('es-CO', { day: 'numeric', month: 'long' });
};

const formatearValor = (valor) => {
    const divisa = configStore.configuracion?.divisa || 'COP';
    const formato = configStore.configuracion?.formato_divisa || 'punto';

    let valorFormateado;
    if (formato === 'coma') {
        valorFormateado = valor.toLocaleString('de-DE', { minimumFractionDigits: 0 });
    } else {
        valorFormateado = valor.toLocaleString('en-US', { minimumFractionDigits: 0 });
    }

    const simbolos = { COP: '$', USD: '$', EUR: '€', MXN: '$' };
    return `${simbolos[divisa] || '$'}${valorFormateado}`;
};

onMounted(async () => {
    await serviciosStore.cargarAlertas();
});
</script>
