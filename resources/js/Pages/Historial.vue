<template>
    <div class="p-4 max-w-full overflow-hidden">
        <div class="flex justify-between items-center mb-4">
            <!-- Botón Filtrar -->
            <button
                @click="mostrarFiltros = !mostrarFiltros"
                class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                :class="[
                    hayFiltrosActivos
                        ? 'bg-primary text-white'
                        : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                ]"
            >
                <FunnelIcon class="w-4 h-4" />
                <span>Filtrar</span>
                <span v-if="hayFiltrosActivos" class="bg-white/20 px-1.5 py-0.5 rounded-full text-xs">
                    {{ cantidadFiltrosActivos }}
                </span>
                <ChevronDownIcon
                    class="w-4 h-4 transition-transform"
                    :class="mostrarFiltros ? 'rotate-180' : ''"
                />
            </button>

            <Button variant="secondary" size="sm" @click="abrirModalCompartir">
                <ShareIcon class="w-4 h-4 mr-1" />
                Compartir
            </Button>
        </div>

        <!-- Modal Compartir -->
        <div v-if="mostrarModalCompartir" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <Card class="w-full max-w-md max-h-[90vh] overflow-y-auto relative">
                <!-- Boton cerrar arriba derecha -->
                <button
                    @click="cerrarModalCompartir"
                    class="absolute top-3 right-3 p-1 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                >
                    <XMarkIcon class="w-5 h-5" />
                </button>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pr-8">Compartir Gastos</h2>

                <div class="space-y-4">
                    <!-- Saldo pendiente - solo si hay usuario 2 -->
                    <div v-if="configStore.tieneUsuario2" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
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

                    <!-- Rango de fechas opcional -->
                    <div class="space-y-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Rango de fechas (opcional)
                        </p>
                        <div class="grid grid-cols-2 gap-3">
                            <Input
                                v-model="fechaCompartirDesde"
                                type="date"
                                label="Desde"
                            />
                            <Input
                                v-model="fechaCompartirHasta"
                                type="date"
                                label="Hasta"
                            />
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Si no seleccionas fechas, se incluyen todos los registros
                        </p>
                    </div>

                    <!-- Filtros de Tipo (selección múltiple) - solo si hay mas de un tipo -->
                    <div v-if="configStore.tiposGasto.length > 1">
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

                    <!-- Filtros de Categoría (colapsable) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Categorías
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="categoriasCompartirSeleccionadas = ['']; mostrarCategoriasCompartir = false"
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
                                v-if="!mostrarCategoriasCompartir && categoriasCompartirSeleccionadas.includes('')"
                                type="button"
                                @click="mostrarCategoriasCompartir = true"
                                class="px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                            >
                                Elegir categorías...
                            </button>
                        </div>
                        <!-- Categorías expandidas -->
                        <div v-if="mostrarCategoriasCompartir || !categoriasCompartirSeleccionadas.includes('')" class="flex flex-wrap gap-2 mt-2 max-h-32 overflow-y-auto">
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
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            @click="compartirWhatsApp"
                            :disabled="generandoCompartir"
                            class="flex flex-col items-center justify-center p-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors"
                        >
                            <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center mb-1">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">WhatsApp</span>
                        </button>
                        <button
                            @click="generarPDF"
                            :disabled="generandoCompartir"
                            class="flex flex-col items-center justify-center p-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                        >
                            <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center mb-1">
                                <DocumentTextIcon class="w-5 h-5 text-white" />
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">PDF</span>
                        </button>
                        <button
                            @click="exportarCSV"
                            :disabled="generandoCompartir"
                            class="flex flex-col items-center justify-center p-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                        >
                            <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center mb-1">
                                <TableCellsIcon class="w-5 h-5 text-white" />
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">CSV</span>
                        </button>
                    </div>

                    <p v-if="generandoCompartir" class="text-center text-sm text-gray-500 dark:text-gray-400">
                        Generando...
                    </p>
                </div>

            </Card>
        </div>

        <!-- Filtros Colapsables -->
        <div v-if="mostrarFiltros" class="mb-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 space-y-4">
                <!-- Rango de fechas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Rango de fechas
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <Input
                            v-model="filtros.desde"
                            type="date"
                            label="Desde"
                        />
                        <Input
                            v-model="filtros.hasta"
                            type="date"
                            label="Hasta"
                        />
                    </div>
                </div>

                <!-- Tipo de gasto - solo si hay mas de un tipo -->
                <div v-if="configStore.tiposGasto.length > 1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tipo de gasto
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            @click="seleccionarTodosTipos"
                            :class="[
                                'px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
                                filtros.tipos.length === 0
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
                            @click="toggleTipoFiltro(tipo.value)"
                            :class="[
                                'px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
                                filtros.tipos.includes(tipo.value)
                                    ? 'bg-primary text-white'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                            ]"
                        >
                            {{ tipo.label }}
                        </button>
                    </div>
                </div>

                <!-- Categoría (colapsable) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Categoria
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            @click="seleccionarTodasCategorias"
                            :class="[
                                'px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
                                filtros.categorias.length === 0
                                    ? 'bg-primary text-white'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                            ]"
                        >
                            Todas
                        </button>
                        <button
                            v-if="!mostrarCategoriasHistorial && filtros.categorias.length === 0"
                            type="button"
                            @click="mostrarCategoriasHistorial = true"
                            class="px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                        >
                            Elegir categoria...
                        </button>
                    </div>
                    <!-- Categorías expandidas -->
                    <div v-if="mostrarCategoriasHistorial || filtros.categorias.length > 0" class="flex flex-wrap gap-2 mt-2 max-h-32 overflow-y-auto">
                        <button
                            v-for="cat in categoriasStore.activas"
                            :key="cat.id"
                            type="button"
                            @click="toggleCategoriaFiltro(cat.id)"
                            :class="[
                                'px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
                                filtros.categorias.includes(cat.id)
                                    ? 'bg-primary text-white'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                            ]"
                        >
                            {{ cat.nombre }}
                        </button>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex gap-2 pt-2">
                    <Button variant="primary" size="sm" class="flex-1" @click="aplicarFiltros">
                        Aplicar
                    </Button>
                    <Button variant="ghost" size="sm" @click="limpiarFiltros">
                        Limpiar
                    </Button>
                </div>
            </div>
        </div>

        <!-- Resumen de totales cuando hay filtros activos -->
        <div v-if="resumenFiltrado && !gastosStore.loading" class="mb-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Resumen del filtro</h3>
            <div class="space-y-2">
                <!-- Total General -->
                <div class="flex justify-between items-center">
                    <span class="text-gray-700 dark:text-gray-300">Total General</span>
                    <span class="text-lg font-bold text-primary">{{ formatCurrency(resumenFiltrado.totalGeneral) }}</span>
                </div>

                <!-- Totales por persona - solo si hay usuario 2 -->
                <template v-if="configStore.tieneUsuario2">
                    <!-- Total Persona 1 -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                        <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ configStore.nombre_persona_1 || 'Yo' }} (100%)</span>
                            <span>{{ formatCurrency(resumenFiltrado.gastosPersonales) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400">
                            <span>Compartido ({{ resumenFiltrado.porcentajeP1 }}%)</span>
                            <span>{{ formatCurrency(resumenFiltrado.porcionCompartidaP1) }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Total {{ configStore.nombre_persona_1 || 'Yo' }}</span>
                            <span class="text-lg font-bold text-green-600">{{ formatCurrency(resumenFiltrado.totalPersona1) }}</span>
                        </div>
                    </div>

                    <!-- Total Persona 2 -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                        <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ configStore.nombre_persona_2 }} (100%)</span>
                            <span>{{ formatCurrency(resumenFiltrado.gastosPareja) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400">
                            <span>Compartido ({{ resumenFiltrado.porcentajeP2 }}%)</span>
                            <span>{{ formatCurrency(resumenFiltrado.porcionCompartidaP2) }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Total {{ configStore.nombre_persona_2 }}</span>
                            <span class="text-lg font-bold text-red-500">{{ formatCurrency(resumenFiltrado.totalPersona2) }}</span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Lista de gastos y abonos -->
        <HistorialSkeleton v-if="gastosStore.loading || abonosStore.loading" />

        <div v-else-if="historialCombinado.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            No hay registros que coincidan con los filtros
        </div>

        <div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <div
                v-for="item in historialCombinado"
                :key="item._tipo + '-' + item.id"
                @click="item._tipo === 'gasto' ? editarGasto(item.id) : irAAbonos()"
                class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 px-4"
            >
                <GastoItem v-if="item._tipo === 'gasto'" :gasto="item" />
                <AbonoItem v-else :abono="item" />
            </div>
        </div>

        <!-- Cargar más -->
        <div v-if="gastosStore.hayMas" class="flex justify-center mt-4">
            <Button
                variant="secondary"
                size="sm"
                :loading="gastosStore.loadingMore"
                @click="gastosStore.cargarMas()"
            >
                Cargar más
            </Button>
        </div>

        <!-- Info de registros -->
        <p v-if="historialCombinado.length > 0" class="text-center text-xs text-gray-400 dark:text-gray-500 mt-2">
            Mostrando {{ historialCombinado.length }} registros
        </p>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { ShareIcon, DocumentTextIcon, TableCellsIcon, FunnelIcon, ChevronDownIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import Card from '../Components/UI/Card.vue';
import Input from '../Components/UI/Input.vue';
import Button from '../Components/UI/Button.vue';
import GastoItem from '../Components/Gastos/GastoItem.vue';
import AbonoItem from '../Components/Abonos/AbonoItem.vue';
import HistorialSkeleton from '../Components/Historial/HistorialSkeleton.vue';
import { useGastosStore } from '../Stores/gastos';
import { useCategoriasStore } from '../Stores/categorias';
import { useConfigStore } from '../Stores/config';
import { useDashboardStore } from '../Stores/dashboard';
import { useAbonosStore } from '../Stores/abonos';
import { useCurrency } from '../Composables/useCurrency';
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable';

const router = useRouter();
const gastosStore = useGastosStore();
const categoriasStore = useCategoriasStore();
const configStore = useConfigStore();
const dashboardStore = useDashboardStore();
const abonosStore = useAbonosStore();
const { formatCurrency } = useCurrency();

const filtros = ref({
    desde: '',
    hasta: '',
    tipos: [], // Array para selección múltiple
    categorias: [] // Array para selección múltiple
});

const mostrarFiltros = ref(false);
const mostrarCategoriasHistorial = ref(false);

const hayFiltrosActivos = computed(() => {
    return filtros.value.desde || filtros.value.hasta || filtros.value.tipos.length > 0 || filtros.value.categorias.length > 0;
});

const cantidadFiltrosActivos = computed(() => {
    let count = 0;
    if (filtros.value.desde || filtros.value.hasta) count++;
    if (filtros.value.tipos.length > 0) count++;
    if (filtros.value.categorias.length > 0) count++;
    return count;
});

// Resumen de totales cuando hay filtros activos
const resumenFiltrado = computed(() => {
    // Solo calcular si hay filtros y gastos
    if (!hayFiltrosActivos.value || gastosStore.gastos.length === 0) {
        return null;
    }

    const gastos = gastosStore.gastos;
    const porcentajeP1 = parseFloat(configStore.porcentaje_persona_1);
    const porcentajeP2 = parseFloat(configStore.porcentaje_persona_2);

    // Total general
    const totalGeneral = gastos.reduce((sum, g) => sum + parseFloat(g.valor), 0);

    // Gastos por tipo
    const gastosPersonales = gastos
        .filter(g => g.tipo === 'personal')
        .reduce((sum, g) => sum + parseFloat(g.valor), 0);

    const gastosPareja = gastos
        .filter(g => g.tipo === 'pareja')
        .reduce((sum, g) => sum + parseFloat(g.valor), 0);

    const gastosCompartidos = gastos
        .filter(g => g.tipo === 'compartido')
        .reduce((sum, g) => sum + parseFloat(g.valor), 0);

    // Porciones de compartidos
    const porcionCompartidaP1 = gastosCompartidos * (porcentajeP1 / 100);
    const porcionCompartidaP2 = gastosCompartidos * (porcentajeP2 / 100);

    // Totales por persona
    const totalPersona1 = gastosPersonales + porcionCompartidaP1;
    const totalPersona2 = gastosPareja + porcionCompartidaP2;

    return {
        totalGeneral,
        totalPersona1,
        totalPersona2,
        gastosPersonales,
        gastosPareja,
        porcionCompartidaP1,
        porcionCompartidaP2,
        porcentajeP1,
        porcentajeP2
    };
});

// Toggle para selección múltiple de tipos en filtros
const toggleTipoFiltro = (tipo) => {
    const index = filtros.value.tipos.indexOf(tipo);
    if (index > -1) {
        // Ya está seleccionado, quitarlo
        filtros.value.tipos.splice(index, 1);
    } else {
        // Agregarlo
        filtros.value.tipos.push(tipo);
    }
};

// Toggle para selección múltiple de categorías en filtros
const toggleCategoriaFiltro = (catId) => {
    const index = filtros.value.categorias.indexOf(catId);
    if (index > -1) {
        // Ya está seleccionada, quitarla
        filtros.value.categorias.splice(index, 1);
    } else {
        // Agregarla
        filtros.value.categorias.push(catId);
    }
};

// Seleccionar/deseleccionar todos los tipos
const seleccionarTodosTipos = () => {
    filtros.value.tipos = [];
};

// Seleccionar/deseleccionar todas las categorías
const seleccionarTodasCategorias = () => {
    filtros.value.categorias = [];
    mostrarCategoriasHistorial.value = false;
};

onMounted(async () => {
    await Promise.all([
        gastosStore.cargarGastos(),
        categoriasStore.cargarCategorias(true),
        configStore.cargarConfiguracion(),
        dashboardStore.cargarDashboard(),
        abonosStore.cargarAbonos()
    ]);
});

// Combinar gastos y abonos ordenados por fecha
const historialCombinado = computed(() => {
    const gastos = gastosStore.gastos.map(g => ({
        ...g,
        _tipo: 'gasto'
    }));

    // Solo incluir abonos si hay usuario 2 configurado
    const abonos = configStore.tieneUsuario2
        ? abonosStore.abonos.map(a => ({
            id: a.id,
            fecha: a.fecha,
            concepto: a.nota || 'Abono',
            valor: a.valor,
            _tipo: 'abono'
        }))
        : [];

    return [...gastos, ...abonos].sort((a, b) => {
        // Primero ordenar por fecha descendente
        const fechaCompare = new Date(b.fecha) - new Date(a.fecha);
        if (fechaCompare !== 0) return fechaCompare;

        // Si las fechas son iguales, ordenar por created_at descendente
        const createdA = a.created_at ? new Date(a.created_at) : new Date(0);
        const createdB = b.created_at ? new Date(b.created_at) : new Date(0);
        return createdB - createdA;
    });
});

const aplicarFiltros = () => {
    // Convertir arrays a formato que el store/backend espera
    const filtrosParaEnviar = {
        desde: filtros.value.desde,
        hasta: filtros.value.hasta,
        tipos: filtros.value.tipos.length > 0 ? filtros.value.tipos : null,
        categorias: filtros.value.categorias.length > 0 ? filtros.value.categorias : null
    };
    gastosStore.setFiltros(filtrosParaEnviar);
    gastosStore.cargarGastos(1);
    mostrarFiltros.value = false;
};

const limpiarFiltros = () => {
    filtros.value = { desde: '', hasta: '', tipos: [], categorias: [] };
    mostrarCategoriasHistorial.value = false;
    gastosStore.limpiarFiltros();
    gastosStore.cargarGastos(1);
    mostrarFiltros.value = false;
};

const editarGasto = (id) => {
    router.push(`/gastos/${id}/editar`);
};

const irAAbonos = () => {
    router.push('/abonos');
};

// Funciones de compartir
const mostrarModalCompartir = ref(false);
const generandoCompartir = ref(false);
const incluirSaldoPendiente = ref(true);
const tiposCompartirSeleccionados = ref(['']);
const categoriasCompartirSeleccionadas = ref(['']);
const mostrarCategoriasCompartir = ref(false);
const fechaCompartirDesde = ref('');
const fechaCompartirHasta = ref('');

const abrirModalCompartir = () => {
    incluirSaldoPendiente.value = true;
    tiposCompartirSeleccionados.value = [''];
    categoriasCompartirSeleccionadas.value = [''];
    mostrarCategoriasCompartir.value = false;
    fechaCompartirDesde.value = '';
    fechaCompartirHasta.value = '';
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
    // Construir filtros para la consulta
    const filtrosCompartir = {};
    if (fechaCompartirDesde.value) {
        filtrosCompartir.desde = fechaCompartirDesde.value;
    }
    if (fechaCompartirHasta.value) {
        filtrosCompartir.hasta = fechaCompartirHasta.value;
    }

    // Cargar todos los gastos con los filtros de fecha
    let gastos = await gastosStore.obtenerTodosGastos(filtrosCompartir);

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
                saldoTexto = `${nombrePareja} te debe: ${formatCurrency(deuda)}`;
            } else if (deuda < 0) {
                saldoTexto = `Le debes a ${nombrePareja}: ${formatCurrency(Math.abs(deuda))}`;
            } else {
                saldoTexto = 'Estan a paz y salvo';
            }

            doc.setFillColor(67, 56, 202); // Morado oscuro
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
        yPos += 15;

        // Totales por persona - Solo si hay usuario 2 configurado
        if (configStore.tieneUsuario2) {
            const nombrePersona1 = configStore.nombre_persona_1 || 'Yo';
            const nombrePersona2 = configStore.nombre_persona_2;
            const porcentajePersona1 = parseFloat(configStore.porcentaje_persona_1);
            const porcentajePersona2 = parseFloat(configStore.porcentaje_persona_2);

            // Calcular gastos por tipo
            const gastosPersonales = gastos
                .filter(g => g.tipo === 'personal')
                .reduce((sum, g) => sum + parseFloat(g.valor), 0);

            const gastosPareja = gastos
                .filter(g => g.tipo === 'pareja')
                .reduce((sum, g) => sum + parseFloat(g.valor), 0);

            const gastosCompartidos = gastos
                .filter(g => g.tipo === 'compartido')
                .reduce((sum, g) => sum + parseFloat(g.valor), 0);

            const porcionCompartidaP1 = gastosCompartidos * (porcentajePersona1 / 100);
            const porcionCompartidaP2 = gastosCompartidos * (porcentajePersona2 / 100);

            const totalPersona1 = gastosPersonales + porcionCompartidaP1;
            const totalPersona2 = gastosPareja + porcionCompartidaP2;

            // --- Total Persona 1 ---
            doc.setFontSize(10);
            doc.setTextColor(60, 60, 60);
            doc.text(`${nombrePersona1} (100%):`, 20, yPos);
            doc.text(formatCurrency(gastosPersonales), pageWidth - 20, yPos, { align: 'right' });
            yPos += 6;

            doc.text(`Compartido (${porcentajePersona1}%):`, 20, yPos);
            doc.text(formatCurrency(porcionCompartidaP1), pageWidth - 20, yPos, { align: 'right' });
            yPos += 8;

            // Total persona 1 con fondo verde
            doc.setFillColor(34, 197, 94); // Verde
            doc.roundedRect(14, yPos - 5, pageWidth - 28, 12, 2, 2, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(12);
            doc.text(`Total ${nombrePersona1}:`, 20, yPos + 2);
            doc.text(formatCurrency(totalPersona1), pageWidth - 20, yPos + 2, { align: 'right' });
            yPos += 18;

            // --- Total Persona 2 ---
            doc.setFontSize(10);
            doc.setTextColor(60, 60, 60);
            doc.text(`${nombrePersona2} (100%):`, 20, yPos);
            doc.text(formatCurrency(gastosPareja), pageWidth - 20, yPos, { align: 'right' });
            yPos += 6;

            doc.text(`Compartido (${porcentajePersona2}%):`, 20, yPos);
            doc.text(formatCurrency(porcionCompartidaP2), pageWidth - 20, yPos, { align: 'right' });
            yPos += 8;

            // Total persona 2 con fondo morado oscuro
            doc.setFillColor(67, 56, 202); // Morado oscuro
            doc.roundedRect(14, yPos - 5, pageWidth - 28, 12, 2, 2, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(12);
            doc.text(`Total ${nombrePersona2}:`, 20, yPos + 2);
            doc.text(formatCurrency(totalPersona2), pageWidth - 20, yPos + 2, { align: 'right' });
            yPos += 15;
        }

        yPos += 3;

        // Tabla de gastos
        doc.setTextColor(79, 70, 229);
        doc.setFontSize(14);
        doc.text('Detalle de Gastos', 14, yPos);
        yPos += 5;

        // Configurar columnas según si hay usuario 2
        let tableHeaders;
        let tableData;
        let columnStyles;

        if (configStore.tieneUsuario2) {
            const nombreP1 = configStore.nombre_persona_1 || 'Yo';
            const nombreP2 = configStore.nombre_persona_2;
            const porcP1 = parseFloat(configStore.porcentaje_persona_1);
            const porcP2 = parseFloat(configStore.porcentaje_persona_2);

            tableHeaders = [['Fecha', 'Concepto', 'Categoria', 'Tipo', 'Medio Pago', nombreP1, nombreP2, 'Total']];

            tableData = gastos.map(g => {
                const valor = parseFloat(g.valor);
                let valorP1 = 0;
                let valorP2 = 0;
                let tipoLabel = '';

                if (g.tipo === 'personal') {
                    valorP1 = valor;
                    valorP2 = 0;
                    tipoLabel = nombreP1;
                } else if (g.tipo === 'pareja') {
                    valorP1 = 0;
                    valorP2 = valor;
                    tipoLabel = nombreP2;
                } else if (g.tipo === 'compartido') {
                    valorP1 = valor * (porcP1 / 100);
                    valorP2 = valor * (porcP2 / 100);
                    tipoLabel = `${porcP1}/${porcP2}`;
                }

                return [
                    formatDateShort(g.fecha),
                    g.concepto || '-',
                    g.categoria?.nombre || '-',
                    tipoLabel,
                    g.medio_pago?.nombre || '-',
                    formatCurrency(valorP1),
                    formatCurrency(valorP2),
                    formatCurrency(valor)
                ];
            });

            columnStyles = {
                0: { cellWidth: 18 },
                3: { cellWidth: 18 },
                5: { halign: 'right' },
                6: { halign: 'right' },
                7: { halign: 'right', fontStyle: 'bold' }
            };
        } else {
            tableHeaders = [['Fecha', 'Concepto', 'Categoria', 'Tipo', 'Medio Pago', 'Valor']];

            tableData = gastos.map(g => [
                formatDateShort(g.fecha),
                g.concepto || '-',
                g.categoria?.nombre || '-',
                configStore.getNombreTipo(g.tipo),
                g.medio_pago?.nombre || '-',
                formatCurrency(g.valor)
            ]);

            columnStyles = {
                0: { cellWidth: 22 },
                5: { halign: 'right', fontStyle: 'bold' }
            };
        }

        autoTable(doc, {
            startY: yPos,
            head: tableHeaders,
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
            columnStyles: columnStyles,
            margin: { left: 14, right: 14 }
        });

        // Footer
        const finalY = (doc).lastAutoTable.finalY + 10;
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

const exportarCSV = async () => {
    generandoCompartir.value = true;
    try {
        const gastos = await obtenerGastosParaCompartir();

        if (gastos.length === 0) {
            alert('No hay gastos para exportar');
            return;
        }

        // Crear contenido CSV
        let headers;
        let rows;

        if (configStore.tieneUsuario2) {
            const nombreP1 = configStore.nombre_persona_1 || 'Yo';
            const nombreP2 = configStore.nombre_persona_2;
            const porcP1 = parseFloat(configStore.porcentaje_persona_1);
            const porcP2 = parseFloat(configStore.porcentaje_persona_2);

            headers = ['Fecha', 'Concepto', 'Categoria', 'Tipo', 'Medio de Pago', nombreP1, nombreP2, 'Total'];

            rows = gastos.map(g => {
                const valor = parseFloat(g.valor);
                let valorP1 = 0;
                let valorP2 = 0;
                let tipoLabel = '';

                if (g.tipo === 'personal') {
                    valorP1 = valor;
                    valorP2 = 0;
                    tipoLabel = nombreP1;
                } else if (g.tipo === 'pareja') {
                    valorP1 = 0;
                    valorP2 = valor;
                    tipoLabel = nombreP2;
                } else if (g.tipo === 'compartido') {
                    valorP1 = valor * (porcP1 / 100);
                    valorP2 = valor * (porcP2 / 100);
                    tipoLabel = `${porcP1}/${porcP2}`;
                }

                return [
                    g.fecha,
                    `"${(g.concepto || '').replace(/"/g, '""')}"`,
                    `"${(g.categoria?.nombre || '').replace(/"/g, '""')}"`,
                    tipoLabel,
                    `"${(g.medio_pago?.nombre || '').replace(/"/g, '""')}"`,
                    valorP1,
                    valorP2,
                    valor
                ];
            });
        } else {
            headers = ['Fecha', 'Concepto', 'Categoria', 'Tipo', 'Medio de Pago', 'Valor'];
            rows = gastos.map(g => [
                g.fecha,
                `"${(g.concepto || '').replace(/"/g, '""')}"`,
                `"${(g.categoria?.nombre || '').replace(/"/g, '""')}"`,
                configStore.getNombreTipo(g.tipo),
                `"${(g.medio_pago?.nombre || '').replace(/"/g, '""')}"`,
                g.valor
            ]);
        }

        const csvContent = [
            headers.join(','),
            ...rows.map(row => row.join(','))
        ].join('\n');

        // Determinar nombre del archivo
        const fechas = gastos.map(g => new Date(g.fecha)).sort((a, b) => a - b);
        const fechaInicio = fechas[0].toLocaleDateString('sv-SE');
        const fechaFin = fechas[fechas.length - 1].toLocaleDateString('sv-SE');
        const fileName = `gastos_${fechaInicio}_${fechaFin}.csv`;

        // Crear blob
        const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });

        // Intentar usar Web Share API si está disponible y soporta archivos
        if (navigator.canShare && navigator.canShare({ files: [new File([blob], fileName, { type: 'text/csv' })] })) {
            const file = new File([blob], fileName, { type: 'text/csv' });
            try {
                await navigator.share({
                    files: [file],
                    title: 'Gastos CSV',
                    text: 'Aqui esta el archivo de gastos'
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

        // Fallback: descargar el archivo
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        cerrarModalCompartir();
    } catch (error) {
        console.error('Error exportando CSV:', error);
        alert('Error al exportar los gastos');
    } finally {
        generandoCompartir.value = false;
    }
};
</script>
