<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <SharedDataNav
            :ownerName="sharedDashboard.ownerName"
            :activeTab="activeTab"
            @back="goBack"
            @tab-change="activeTab = $event"
            @share="mostrarModalCompartir = true"
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
                                <span class="text-gray-600 dark:text-gray-400">{{ configStore.getNombreTipo('personal') }}</span>
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
                                <span class="text-gray-600 dark:text-gray-400">{{ configStore.getNombreTipo('compartido') }}</span>
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
                                        {{ configStore.getNombreTipo(gasto.tipo) }}
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

        <!-- Modal Compartir -->
        <div
            v-if="mostrarModalCompartir"
            class="fixed inset-0 z-50 flex items-end justify-center bg-black/50"
            @click.self="mostrarModalCompartir = false"
        >
            <div class="bg-white dark:bg-gray-800 rounded-t-2xl w-full max-w-lg p-4 pb-8 safe-area-bottom animate-slide-up">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Compartir</h3>
                    <button
                        @click="mostrarModalCompartir = false"
                        class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        <XMarkIcon class="w-5 h-5 text-gray-500" />
                    </button>
                </div>

                <!-- Opciones de compartir -->
                <div class="grid grid-cols-3 gap-3">
                    <button
                        @click="compartirWhatsApp"
                        :disabled="generandoCompartir"
                        class="flex flex-col items-center justify-center p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors disabled:opacity-50"
                    >
                        <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center mb-2">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">WhatsApp</span>
                    </button>
                    <button
                        @click="generarPDF"
                        :disabled="generandoCompartir"
                        class="flex flex-col items-center justify-center p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors disabled:opacity-50"
                    >
                        <div class="w-12 h-12 rounded-full bg-red-500 flex items-center justify-center mb-2">
                            <DocumentTextIcon class="w-6 h-6 text-white" />
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">PDF</span>
                    </button>
                    <button
                        @click="exportarCSV"
                        :disabled="generandoCompartir"
                        class="flex flex-col items-center justify-center p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors disabled:opacity-50"
                    >
                        <div class="w-12 h-12 rounded-full bg-blue-500 flex items-center justify-center mb-2">
                            <TableCellsIcon class="w-6 h-6 text-white" />
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">CSV</span>
                    </button>
                </div>

                <p v-if="generandoCompartir" class="text-center text-sm text-gray-500 dark:text-gray-400 mt-3">
                    Generando...
                </p>
            </div>
        </div>

        <Toast :show="showToast" :message="toastMessage" :type="toastType" @close="showToast = false" />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ArrowUpIcon, XMarkIcon, DocumentTextIcon, TableCellsIcon } from '@heroicons/vue/24/outline';
import SharedDataNav from '../../Components/Shared/SharedDataNav.vue';
import SharedGastoForm from '../../Components/Shared/SharedGastoForm.vue';
import Toast from '../../Components/UI/Toast.vue';
import { useDataShareStore } from '../../Stores/dataShare';
import { useSharedDashboardStore } from '../../Stores/sharedDashboard';
import { useConfigStore } from '../../Stores/config';
import { useCurrency } from '../../Composables/useCurrency';
import jsPDF from 'jspdf';
import 'jspdf-autotable';

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

// Compartir
const mostrarModalCompartir = ref(false);
const generandoCompartir = ref(false);

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

// Funciones de compartir
const formatearMoneda = (valor) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(valor);
};

const generarTextoWhatsApp = () => {
    const gastos = sharedDashboard.gastos;
    if (gastos.length === 0) return 'No hay gastos para compartir.';

    const total = gastos.reduce((sum, g) => sum + parseFloat(g.valor), 0);
    const ownerName = sharedDashboard.ownerName || 'Usuario';

    let texto = `*Gastos de ${ownerName}*\n`;
    texto += `_${gastos.length} registros_\n\n`;

    gastos.forEach(g => {
        const fecha = new Date(g.fecha).toLocaleDateString('es-CO', { day: '2-digit', month: 'short' });
        texto += `${fecha} | ${g.concepto} | ${formatearMoneda(g.valor)}\n`;
    });

    texto += `\n*Total: ${formatearMoneda(total)}*`;

    return texto;
};

const compartirWhatsApp = async () => {
    generandoCompartir.value = true;
    try {
        const texto = generarTextoWhatsApp();
        const url = `https://wa.me/?text=${encodeURIComponent(texto)}`;
        window.open(url, '_blank');
        mostrarModalCompartir.value = false;
    } catch (error) {
        console.error('Error compartiendo:', error);
        toastMessage.value = 'Error al compartir';
        toastType.value = 'error';
        showToast.value = true;
    } finally {
        generandoCompartir.value = false;
    }
};

const generarPDF = async () => {
    generandoCompartir.value = true;
    try {
        const gastos = sharedDashboard.gastos;
        const ownerName = sharedDashboard.ownerName || 'Usuario';

        const doc = new jsPDF();

        // Titulo
        doc.setFontSize(16);
        doc.text(`Gastos de ${ownerName}`, 14, 20);

        // Fecha de generacion
        doc.setFontSize(10);
        doc.setTextColor(100);
        doc.text(`Generado: ${new Date().toLocaleDateString('es-CO')}`, 14, 28);

        // Tabla
        const tableData = gastos.map(g => [
            new Date(g.fecha).toLocaleDateString('es-CO'),
            g.concepto,
            g.categoria?.nombre || '-',
            configStore.getNombreTipo(g.tipo),
            formatearMoneda(g.valor)
        ]);

        doc.autoTable({
            startY: 35,
            head: [['Fecha', 'Concepto', 'Categoria', 'Tipo', 'Valor']],
            body: tableData,
            theme: 'striped',
            headStyles: { fillColor: [99, 102, 241] },
            styles: { fontSize: 9 }
        });

        // Total
        const total = gastos.reduce((sum, g) => sum + parseFloat(g.valor), 0);
        const finalY = doc.lastAutoTable.finalY + 10;
        doc.setFontSize(12);
        doc.setTextColor(0);
        doc.text(`Total: ${formatearMoneda(total)}`, 14, finalY);

        doc.save(`gastos_${ownerName.toLowerCase().replace(/\s+/g, '_')}.pdf`);
        mostrarModalCompartir.value = false;
    } catch (error) {
        console.error('Error generando PDF:', error);
        toastMessage.value = 'Error al generar PDF';
        toastType.value = 'error';
        showToast.value = true;
    } finally {
        generandoCompartir.value = false;
    }
};

const exportarCSV = async () => {
    generandoCompartir.value = true;
    try {
        const gastos = sharedDashboard.gastos;
        const ownerName = sharedDashboard.ownerName || 'Usuario';

        // Header
        let csv = 'Fecha,Concepto,Categoria,Tipo,Valor\n';

        // Datos
        gastos.forEach(g => {
            const fecha = new Date(g.fecha).toLocaleDateString('es-CO');
            const concepto = `"${(g.concepto || '').replace(/"/g, '""')}"`;
            const categoria = g.categoria?.nombre || '-';
            const tipo = configStore.getNombreTipo(g.tipo);
            const valor = g.valor;
            csv += `${fecha},${concepto},${categoria},${tipo},${valor}\n`;
        });

        // Descargar
        const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `gastos_${ownerName.toLowerCase().replace(/\s+/g, '_')}.csv`;
        link.click();
        URL.revokeObjectURL(url);

        mostrarModalCompartir.value = false;
    } catch (error) {
        console.error('Error exportando CSV:', error);
        toastMessage.value = 'Error al exportar CSV';
        toastType.value = 'error';
        showToast.value = true;
    } finally {
        generandoCompartir.value = false;
    }
};
</script>
