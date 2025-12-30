<template>
    <div class="p-4 max-w-full overflow-hidden">
        <div class="flex justify-end gap-2 mb-4">
            <Button variant="secondary" size="sm" @click="abrirModalCompartir">
                <ShareIcon class="w-4 h-4 mr-1" />
                Compartir
            </Button>
            <Button variant="secondary" size="sm" @click="abrirModalExportar">
                Exportar
            </Button>
        </div>

        <!-- Modal Exportar -->
        <div v-if="mostrarModalExportar" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <Card class="w-full max-w-md">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Exportar Gastos</h2>

                <div class="space-y-4">
                    <!-- Checkbox exportar todos -->
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="filtrosExport.exportar_todos"
                            class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary"
                        />
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            Exportar todos los registros (desde siempre)
                        </span>
                    </label>

                    <!-- Rango de fechas (deshabilitado si exportar_todos está activo) -->
                    <div :class="{ 'opacity-50 pointer-events-none': filtrosExport.exportar_todos }">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">O selecciona un rango de fechas:</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 min-w-0">
                            <Input
                                v-model="filtrosExport.desde"
                                type="date"
                                label="Desde"
                                :disabled="filtrosExport.exportar_todos"
                            />
                            <Input
                                v-model="filtrosExport.hasta"
                                type="date"
                                label="Hasta"
                                :disabled="filtrosExport.exportar_todos"
                            />
                        </div>
                    </div>

                    <!-- Filtros adicionales opcionales -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <Select
                            v-model="filtrosExport.tipo"
                            label="Tipo"
                            :options="tiposOptions"
                        />
                        <Select
                            v-model="filtrosExport.categoria_id"
                            label="Categoría"
                            :options="categoriasOptions"
                        />
                    </div>
                </div>

                <div class="flex gap-2 mt-6">
                    <Button variant="ghost" class="flex-1" @click="cerrarModalExportar">
                        Cancelar
                    </Button>
                    <Button
                        class="flex-1"
                        @click="exportar"
                        :disabled="exportando || (!filtrosExport.exportar_todos && !filtrosExport.desde && !filtrosExport.hasta)"
                    >
                        {{ exportando ? 'Exportando...' : 'Descargar CSV' }}
                    </Button>
                </div>
            </Card>
        </div>

        <!-- Modal Compartir -->
        <div v-if="mostrarModalCompartir" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <Card class="w-full max-w-md max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Compartir Gastos</h2>

                <div class="space-y-4">
                    <!-- Saldo pendiente -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ configStore.nombre_persona_2 }} te debe:
                            </span>
                            <span class="text-lg font-bold" :class="dashboardStore.deudaPersona2 > 0 ? 'text-red-500' : 'text-green-500'">
                                {{ formatCurrency(dashboardStore.deudaPersona2) }}
                            </span>
                        </div>
                        <label class="flex items-center gap-2 mt-2 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="incluirSaldoPendiente"
                                class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary"
                            />
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                Incluir saldo pendiente en el reporte
                            </span>
                        </label>
                    </div>

                    <!-- Opciones de rango -->
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="radio"
                                v-model="opcionCompartir"
                                value="actuales"
                                class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                            />
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Gastos mostrados en pantalla ({{ gastosStore.gastos.length }})
                            </span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="radio"
                                v-model="opcionCompartir"
                                value="filtrados"
                                class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                            />
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Todos los gastos con filtros actuales
                            </span>
                        </label>
                    </div>

                    <!-- Filtros de Tipo (selección múltiple) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tipo de gasto
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="toggleTipoCompartir('')"
                                :class="[
                                    'px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
                                    tiposCompartirSeleccionados.includes('')
                                        ? 'bg-primary text-white'
                                        : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                                ]"
                            >
                                Todos
                            </button>
                            <button
                                v-for="tipo in configStore.tiposGasto"
                                :key="tipo.value"
                                type="button"
                                @click="toggleTipoCompartir(tipo.value)"
                                :class="[
                                    'px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
                                    tiposCompartirSeleccionados.includes(tipo.value)
                                        ? 'bg-primary text-white'
                                        : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                                ]"
                            >
                                {{ tipo.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Filtros de Categoría (selección múltiple) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Categorías
                        </label>
                        <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto">
                            <button
                                type="button"
                                @click="toggleCategoriaCompartir('')"
                                :class="[
                                    'px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
                                    categoriasCompartirSeleccionadas.includes('')
                                        ? 'bg-primary text-white'
                                        : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                                ]"
                            >
                                Todas
                            </button>
                            <button
                                v-for="cat in categoriasStore.activas"
                                :key="cat.id"
                                type="button"
                                @click="toggleCategoriaCompartir(cat.id)"
                                :class="[
                                    'px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
                                    categoriasCompartirSeleccionadas.includes(cat.id)
                                        ? 'bg-primary text-white'
                                        : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                                ]"
                            >
                                {{ cat.nombre }}
                            </button>
                        </div>
                    </div>

                    <!-- Botones de compartir -->
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            @click="compartirWhatsApp"
                            :disabled="generandoCompartir"
                            class="flex flex-col items-center justify-center p-4 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors"
                        >
                            <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center mb-2">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">WhatsApp</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Texto formateado</span>
                        </button>
                        <button
                            @click="generarPDF"
                            :disabled="generandoCompartir"
                            class="flex flex-col items-center justify-center p-4 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                        >
                            <div class="w-12 h-12 rounded-full bg-red-500 flex items-center justify-center mb-2">
                                <DocumentTextIcon class="w-6 h-6 text-white" />
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">PDF</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Documento</span>
                        </button>
                    </div>

                    <p v-if="generandoCompartir" class="text-center text-sm text-gray-500 dark:text-gray-400">
                        Generando...
                    </p>
                </div>

                <div class="flex gap-2 mt-6">
                    <Button variant="ghost" class="w-full" @click="cerrarModalCompartir">
                        Cerrar
                    </Button>
                </div>
            </Card>
        </div>

        <!-- Filtros -->
        <Card class="mb-4">
            <div class="space-y-3 min-w-0">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 min-w-0">
                    <Input
                        v-model="filtros.desde"
                        type="date"
                        label="Desde"
                        @change="aplicarFiltros"
                    />
                    <Input
                        v-model="filtros.hasta"
                        type="date"
                        label="Hasta"
                        @change="aplicarFiltros"
                    />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 min-w-0">
                    <Select
                        v-model="filtros.tipo"
                        label="Tipo"
                        :options="tiposOptions"
                        @change="aplicarFiltros"
                    />
                    <Select
                        v-model="filtros.categoria_id"
                        label="Categoría"
                        :options="categoriasOptions"
                        @change="aplicarFiltros"
                    />
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <Button variant="ghost" size="sm" @click="limpiarFiltros">
                    Limpiar filtros
                </Button>
            </div>
        </Card>

        <!-- Lista de gastos -->
        <div v-if="gastosStore.loading" class="text-center py-8">
            <div class="animate-spin w-8 h-8 border-4 border-primary border-t-transparent rounded-full mx-auto"></div>
        </div>

        <div v-else-if="gastosStore.gastos.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            No hay gastos que coincidan con los filtros
        </div>

        <div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <div
                v-for="gasto in gastosStore.gastos"
                :key="gasto.id"
                @click="editarGasto(gasto.id)"
                class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 px-4"
            >
                <GastoItem :gasto="gasto" />
            </div>
        </div>

        <!-- Paginación -->
        <div v-if="gastosStore.meta.last_page > 1" class="flex justify-center gap-2 mt-4">
            <Button
                variant="secondary"
                size="sm"
                :disabled="gastosStore.meta.current_page === 1"
                @click="cargarPagina(gastosStore.meta.current_page - 1)"
            >
                Anterior
            </Button>
            <span class="py-2 px-3 text-sm text-gray-600 dark:text-gray-400">
                {{ gastosStore.meta.current_page }} / {{ gastosStore.meta.last_page }}
            </span>
            <Button
                variant="secondary"
                size="sm"
                :disabled="gastosStore.meta.current_page === gastosStore.meta.last_page"
                @click="cargarPagina(gastosStore.meta.current_page + 1)"
            >
                Siguiente
            </Button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { ShareIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';
import Card from '../Components/UI/Card.vue';
import Input from '../Components/UI/Input.vue';
import Select from '../Components/UI/Select.vue';
import Button from '../Components/UI/Button.vue';
import GastoItem from '../Components/Gastos/GastoItem.vue';
import { useGastosStore } from '../Stores/gastos';
import { useCategoriasStore } from '../Stores/categorias';
import { useConfigStore } from '../Stores/config';
import { useDashboardStore } from '../Stores/dashboard';
import { useCurrency } from '../Composables/useCurrency';
import jsPDF from 'jspdf';
import 'jspdf-autotable';

const router = useRouter();
const gastosStore = useGastosStore();
const categoriasStore = useCategoriasStore();
const configStore = useConfigStore();
const dashboardStore = useDashboardStore();
const { formatCurrency } = useCurrency();

const filtros = ref({
    desde: '',
    hasta: '',
    tipo: '',
    categoria_id: ''
});

// Estado para modal de exportación
const mostrarModalExportar = ref(false);
const exportando = ref(false);
const filtrosExport = ref({
    desde: '',
    hasta: '',
    tipo: '',
    categoria_id: '',
    exportar_todos: false
});

onMounted(async () => {
    await Promise.all([
        gastosStore.cargarGastos(),
        categoriasStore.cargarCategorias(true),
        configStore.cargarConfiguracion(),
        dashboardStore.cargarDashboard()
    ]);
});

const tiposOptions = computed(() => [
    { value: '', label: 'Todos' },
    ...configStore.tiposGasto
]);

const categoriasOptions = computed(() => [
    { value: '', label: 'Todas' },
    ...categoriasStore.activas.map(c => ({ value: c.id, label: c.nombre }))
]);

const aplicarFiltros = () => {
    gastosStore.setFiltros(filtros.value);
    gastosStore.cargarGastos(1);
};

const limpiarFiltros = () => {
    filtros.value = { desde: '', hasta: '', tipo: '', categoria_id: '' };
    gastosStore.limpiarFiltros();
    gastosStore.cargarGastos(1);
};

const cargarPagina = (page) => {
    gastosStore.cargarGastos(page);
};

const editarGasto = (id) => {
    router.push(`/gastos/${id}/editar`);
};

// Funciones de exportación
const abrirModalExportar = () => {
    // Inicializar con los filtros actuales de la vista
    filtrosExport.value = {
        desde: filtros.value.desde || '',
        hasta: filtros.value.hasta || '',
        tipo: filtros.value.tipo || '',
        categoria_id: filtros.value.categoria_id || '',
        exportar_todos: false
    };
    mostrarModalExportar.value = true;
};

const cerrarModalExportar = () => {
    mostrarModalExportar.value = false;
};

const exportar = async () => {
    exportando.value = true;
    try {
        await gastosStore.exportarGastos(filtrosExport.value);
        cerrarModalExportar();
    } catch (error) {
        console.error('Error exportando:', error);
        alert('Error al exportar los gastos');
    } finally {
        exportando.value = false;
    }
};

// Funciones de compartir
const mostrarModalCompartir = ref(false);
const opcionCompartir = ref('actuales');
const generandoCompartir = ref(false);
const incluirSaldoPendiente = ref(true);
const tiposCompartirSeleccionados = ref(['']);
const categoriasCompartirSeleccionadas = ref(['']);

const abrirModalCompartir = () => {
    opcionCompartir.value = 'actuales';
    incluirSaldoPendiente.value = true;
    tiposCompartirSeleccionados.value = [''];
    categoriasCompartirSeleccionadas.value = [''];
    mostrarModalCompartir.value = true;
};

const cerrarModalCompartir = () => {
    mostrarModalCompartir.value = false;
};

// Toggle para selección múltiple de tipos
const toggleTipoCompartir = (tipo) => {
    if (tipo === '') {
        // Si selecciona "Todos", deselecciona los demás
        tiposCompartirSeleccionados.value = [''];
    } else {
        // Si selecciona un tipo específico
        const index = tiposCompartirSeleccionados.value.indexOf(tipo);
        // Quitar "Todos" si está seleccionado
        const todosIndex = tiposCompartirSeleccionados.value.indexOf('');
        if (todosIndex > -1) {
            tiposCompartirSeleccionados.value.splice(todosIndex, 1);
        }

        if (index > -1) {
            // Ya está seleccionado, quitarlo
            tiposCompartirSeleccionados.value.splice(index, 1);
            // Si no queda ninguno, seleccionar "Todos"
            if (tiposCompartirSeleccionados.value.length === 0) {
                tiposCompartirSeleccionados.value = [''];
            }
        } else {
            // Agregarlo
            tiposCompartirSeleccionados.value.push(tipo);
        }
    }
};

// Toggle para selección múltiple de categorías
const toggleCategoriaCompartir = (catId) => {
    if (catId === '') {
        // Si selecciona "Todas", deselecciona las demás
        categoriasCompartirSeleccionadas.value = [''];
    } else {
        // Si selecciona una categoría específica
        const index = categoriasCompartirSeleccionadas.value.indexOf(catId);
        // Quitar "Todas" si está seleccionada
        const todasIndex = categoriasCompartirSeleccionadas.value.indexOf('');
        if (todasIndex > -1) {
            categoriasCompartirSeleccionadas.value.splice(todasIndex, 1);
        }

        if (index > -1) {
            // Ya está seleccionada, quitarla
            categoriasCompartirSeleccionadas.value.splice(index, 1);
            // Si no queda ninguna, seleccionar "Todas"
            if (categoriasCompartirSeleccionadas.value.length === 0) {
                categoriasCompartirSeleccionadas.value = [''];
            }
        } else {
            // Agregarla
            categoriasCompartirSeleccionadas.value.push(catId);
        }
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-CO', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const formatDateShort = (date) => {
    return new Date(date).toLocaleDateString('es-CO', {
        day: '2-digit',
        month: 'short'
    });
};

const obtenerGastosParaCompartir = async () => {
    let gastos;
    if (opcionCompartir.value === 'actuales') {
        gastos = gastosStore.gastos;
    } else {
        // Cargar todos los gastos con los filtros actuales
        gastos = await gastosStore.obtenerTodosGastos(filtros.value);
    }

    // Filtrar por tipos seleccionados
    if (!tiposCompartirSeleccionados.value.includes('')) {
        gastos = gastos.filter(g => tiposCompartirSeleccionados.value.includes(g.tipo));
    }

    // Filtrar por categorías seleccionadas
    if (!categoriasCompartirSeleccionadas.value.includes('')) {
        gastos = gastos.filter(g => categoriasCompartirSeleccionadas.value.includes(g.categoria_id));
    }

    return gastos;
};

const generarTextoWhatsApp = (gastos) => {
    if (gastos.length === 0) return 'No hay gastos para compartir.';

    // Calcular totales
    const total = gastos.reduce((sum, g) => sum + parseFloat(g.valor), 0);
    const porTipo = {};
    gastos.forEach(g => {
        const tipoLabel = configStore.getNombreTipo(g.tipo);
        porTipo[tipoLabel] = (porTipo[tipoLabel] || 0) + parseFloat(g.valor);
    });

    // Determinar rango de fechas
    const fechas = gastos.map(g => new Date(g.fecha)).sort((a, b) => a - b);
    const fechaInicio = formatDate(fechas[0]);
    const fechaFin = formatDate(fechas[fechas.length - 1]);

    let texto = `📊 *RESUMEN DE GASTOS*\n`;
    texto += `📅 ${fechaInicio} - ${fechaFin}\n`;
    texto += `━━━━━━━━━━━━━━━━━━\n\n`;

    // Saldo pendiente
    if (incluirSaldoPendiente.value) {
        const deuda = dashboardStore.deudaPersona2;
        const nombrePareja = configStore.nombre_persona_2;
        texto += `💵 *SALDO PENDIENTE:*\n`;
        if (deuda > 0) {
            texto += `${nombrePareja} te debe: ${formatCurrency(deuda)}\n\n`;
        } else if (deuda < 0) {
            texto += `Le debes a ${nombrePareja}: ${formatCurrency(Math.abs(deuda))}\n\n`;
        } else {
            texto += `Están a paz y salvo ✅\n\n`;
        }
    }

    // Resumen por tipo
    texto += `💰 *TOTALES POR TIPO:*\n`;
    Object.entries(porTipo).forEach(([tipo, monto]) => {
        texto += `  • ${tipo}: ${formatCurrency(monto)}\n`;
    });
    texto += `\n📌 *TOTAL GENERAL: ${formatCurrency(total)}*\n`;
    texto += `━━━━━━━━━━━━━━━━━━\n\n`;

    // Detalle de gastos (agrupados por fecha)
    texto += `📋 *DETALLE DE GASTOS:*\n\n`;

    const gastosPorFecha = {};
    gastos.forEach(g => {
        const fecha = g.fecha;
        if (!gastosPorFecha[fecha]) {
            gastosPorFecha[fecha] = [];
        }
        gastosPorFecha[fecha].push(g);
    });

    Object.entries(gastosPorFecha)
        .sort(([a], [b]) => new Date(b) - new Date(a))
        .forEach(([fecha, gastosDelDia]) => {
            texto += `📅 *${formatDate(fecha)}*\n`;
            gastosDelDia.forEach(g => {
                const categoria = g.categoria?.nombre || 'Sin categoría';
                const concepto = g.concepto || categoria;
                const tipoLabel = configStore.getNombreTipo(g.tipo);
                texto += `  ${concepto}\n`;
                texto += `  ${formatCurrency(g.valor)} (${tipoLabel})\n`;
                if (g.medio_pago?.nombre) {
                    texto += `  💳 ${g.medio_pago.nombre}\n`;
                }
                texto += `\n`;
            });
        });

    texto += `━━━━━━━━━━━━━━━━━━\n`;
    texto += `_${gastos.length} movimiento(s)_`;

    return texto;
};

const compartirWhatsApp = async () => {
    generandoCompartir.value = true;
    try {
        const gastos = await obtenerGastosParaCompartir();
        const texto = generarTextoWhatsApp(gastos);

        // Usar Web Share API si está disponible, sino abrir WhatsApp Web
        if (navigator.share) {
            await navigator.share({
                text: texto
            });
        } else {
            // Fallback a WhatsApp Web
            const url = `https://wa.me/?text=${encodeURIComponent(texto)}`;
            window.open(url, '_blank');
        }

        cerrarModalCompartir();
    } catch (error) {
        console.error('Error compartiendo:', error);
        if (error.name !== 'AbortError') {
            alert('Error al compartir los gastos');
        }
    } finally {
        generandoCompartir.value = false;
    }
};

const generarPDF = async () => {
    generandoCompartir.value = true;
    try {
        const gastos = await obtenerGastosParaCompartir();

        if (gastos.length === 0) {
            alert('No hay gastos para generar el PDF');
            return;
        }

        // Calcular totales
        const total = gastos.reduce((sum, g) => sum + parseFloat(g.valor), 0);
        const porTipo = {};
        gastos.forEach(g => {
            const tipoLabel = configStore.getNombreTipo(g.tipo);
            porTipo[tipoLabel] = (porTipo[tipoLabel] || 0) + parseFloat(g.valor);
        });

        // Determinar rango de fechas
        const fechas = gastos.map(g => new Date(g.fecha)).sort((a, b) => a - b);
        const fechaInicio = formatDate(fechas[0]);
        const fechaFin = formatDate(fechas[fechas.length - 1]);

        // Crear PDF
        const doc = new jsPDF();
        const pageWidth = doc.internal.pageSize.getWidth();
        let yPos = 20;

        // Título
        doc.setFontSize(20);
        doc.setTextColor(79, 70, 229); // Primary color
        doc.text('Resumen de Gastos', pageWidth / 2, yPos, { align: 'center' });
        yPos += 10;

        // Periodo
        doc.setFontSize(11);
        doc.setTextColor(100, 100, 100);
        doc.text(`Periodo: ${fechaInicio} - ${fechaFin}`, pageWidth / 2, yPos, { align: 'center' });
        yPos += 15;

        // Saldo pendiente
        if (incluirSaldoPendiente.value) {
            const deuda = dashboardStore.deudaPersona2;
            const nombrePareja = configStore.nombre_persona_2;
            let saldoTexto = '';

            if (deuda > 0) {
                doc.setFillColor(239, 68, 68); // Rojo
                saldoTexto = `${nombrePareja} te debe: ${formatCurrency(deuda)}`;
            } else if (deuda < 0) {
                doc.setFillColor(245, 158, 11); // Amarillo
                saldoTexto = `Le debes a ${nombrePareja}: ${formatCurrency(Math.abs(deuda))}`;
            } else {
                doc.setFillColor(34, 197, 94); // Verde
                saldoTexto = 'Estan a paz y salvo';
            }

            doc.roundedRect(14, yPos - 5, pageWidth - 28, 18, 3, 3, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(12);
            doc.text('Saldo Pendiente', 20, yPos + 3);
            doc.setFontSize(11);
            doc.text(saldoTexto, pageWidth - 20, yPos + 3, { align: 'right' });
            yPos += 20;
        }

        // Totales por tipo
        doc.setTextColor(79, 70, 229);
        doc.setFontSize(14);
        doc.text('Totales por Tipo', 14, yPos);
        yPos += 8;

        doc.setFontSize(10);
        doc.setTextColor(60, 60, 60);
        Object.entries(porTipo).forEach(([tipo, monto]) => {
            doc.text(tipo, 20, yPos);
            doc.text(formatCurrency(monto), pageWidth - 20, yPos, { align: 'right' });
            yPos += 6;
        });

        // Total general
        yPos += 5;
        doc.setFillColor(79, 70, 229);
        doc.roundedRect(14, yPos - 5, pageWidth - 28, 12, 2, 2, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(12);
        doc.text('Total General:', 20, yPos + 2);
        doc.text(formatCurrency(total), pageWidth - 20, yPos + 2, { align: 'right' });
        yPos += 18;

        // Tabla de gastos
        doc.setTextColor(79, 70, 229);
        doc.setFontSize(14);
        doc.text('Detalle de Gastos', 14, yPos);
        yPos += 5;

        const tableData = gastos.map(g => [
            formatDateShort(g.fecha),
            g.concepto || '-',
            g.categoria?.nombre || '-',
            configStore.getNombreTipo(g.tipo),
            g.medio_pago?.nombre || '-',
            formatCurrency(g.valor)
        ]);

        doc.autoTable({
            startY: yPos,
            head: [['Fecha', 'Concepto', 'Categoria', 'Tipo', 'Medio Pago', 'Valor']],
            body: tableData,
            theme: 'striped',
            headStyles: {
                fillColor: [79, 70, 229],
                textColor: 255,
                fontSize: 9
            },
            bodyStyles: {
                fontSize: 8
            },
            columnStyles: {
                0: { cellWidth: 22 },
                5: { halign: 'right', fontStyle: 'bold' }
            },
            margin: { left: 14, right: 14 }
        });

        // Footer
        const finalY = doc.lastAutoTable.finalY + 10;
        doc.setFontSize(8);
        doc.setTextColor(150, 150, 150);
        doc.text(
            `Generado el ${new Date().toLocaleDateString('es-CO', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })}`,
            pageWidth / 2,
            finalY,
            { align: 'center' }
        );

        // Generar el PDF como Blob
        const pdfBlob = doc.output('blob');
        const fileName = `gastos_${fechaInicio.replace(/\s/g, '-')}_${fechaFin.replace(/\s/g, '-')}.pdf`;

        // Intentar usar Web Share API si está disponible y soporta archivos
        if (navigator.canShare && navigator.canShare({ files: [new File([pdfBlob], fileName, { type: 'application/pdf' })] })) {
            const file = new File([pdfBlob], fileName, { type: 'application/pdf' });
            try {
                await navigator.share({
                    files: [file],
                    title: 'Resumen de Gastos',
                    text: 'Aqui esta el resumen de gastos'
                });
                cerrarModalCompartir();
                return;
            } catch (shareError) {
                if (shareError.name === 'AbortError') {
                    // Usuario canceló, no hacer nada
                    return;
                }
                // Si falla el share, continuar con la descarga
            }
        }

        // Fallback: descargar el PDF
        const url = URL.createObjectURL(pdfBlob);
        const link = document.createElement('a');
        link.href = url;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        cerrarModalCompartir();
    } catch (error) {
        console.error('Error generando PDF:', error);
        alert('Error al generar el PDF');
    } finally {
        generandoCompartir.value = false;
    }
};
</script>
