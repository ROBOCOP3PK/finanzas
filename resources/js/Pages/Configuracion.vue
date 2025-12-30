<template>
    <div class="p-4 space-y-3 max-w-full overflow-hidden">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Configuracion</h1>

        <!-- Seccion: Apariencia -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <button
                @click="toggleSeccion('apariencia')"
                class="w-full flex items-center justify-between p-4 text-left"
            >
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                        <PaintBrushIcon class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">Apariencia</span>
                </div>
                <ChevronDownIcon
                    :class="[
                        'w-5 h-5 text-gray-500 transition-transform duration-200',
                        seccionesAbiertas.apariencia ? 'rotate-180' : ''
                    ]"
                />
            </button>
            <div v-show="seccionesAbiertas.apariencia" class="px-4 pb-4 space-y-4 border-t border-gray-100 dark:border-gray-700">
                <!-- Tema -->
                <div class="pt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tema</label>
                    <div class="flex gap-2">
                        <button
                            v-for="tema in temas"
                            :key="tema.value"
                            @click="cambiarTema(tema.value)"
                            :class="[
                                'flex-1 py-2 px-3 rounded-lg font-medium text-sm transition-colors',
                                themeStore.tema === tema.value
                                    ? 'bg-primary text-white dark:bg-indigo-500'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                            ]"
                        >
                            {{ tema.label }}
                        </button>
                    </div>
                </div>
                <!-- Divisa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Divisa</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <button
                            v-for="div in configStore.divisasDisponibles"
                            :key="div.value"
                            @click="cambiarDivisa(div.value)"
                            :class="[
                                'py-2 px-3 rounded-lg font-medium text-sm transition-colors',
                                configStore.divisa === div.value
                                    ? 'bg-primary text-white dark:bg-indigo-500'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                            ]"
                        >
                            {{ div.value }}
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Selecciona la moneda para mostrar los valores
                    </p>
                </div>
                <!-- Formato de Divisa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Formato de divisa</label>
                    <div class="flex gap-2">
                        <button
                            v-for="formato in configStore.formatosDivisaDisponibles"
                            :key="formato.value"
                            @click="cambiarFormatoDivisa(formato.value)"
                            :class="[
                                'flex-1 py-2 px-3 rounded-lg font-medium text-sm transition-colors',
                                configStore.formato_divisa === formato.value
                                    ? 'bg-primary text-white dark:bg-indigo-500'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                            ]"
                        >
                            {{ formato.label }}
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Selecciona el separador de miles para los valores
                    </p>
                </div>
            </div>
        </div>

        <!-- Seccion: Categorias -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <button
                @click="toggleSeccion('categorias')"
                class="w-full flex items-center justify-between p-4 text-left"
            >
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center">
                        <TagIcon class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">Categorias</span>
                </div>
                <ChevronDownIcon
                    :class="[
                        'w-5 h-5 text-gray-500 transition-transform duration-200',
                        seccionesAbiertas.categorias ? 'rotate-180' : ''
                    ]"
                />
            </button>
            <div v-show="seccionesAbiertas.categorias" class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700">
                <div class="pt-3 flex justify-end">
                    <Button size="sm" @click="abrirModalCategoria()">
                        <PlusIcon class="w-4 h-4 mr-1" />
                        Nueva
                    </Button>
                </div>
                <div class="mt-3 space-y-2">
                    <div
                        v-for="cat in categoriasStore.categorias"
                        :key="cat.id"
                        class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg flex items-center justify-center"
                                :style="{ backgroundColor: cat.color + '20' }"
                            >
                                <i v-if="cat.icono" :class="cat.icono" :style="{ color: cat.color }"></i>
                                <span v-else class="w-3 h-3 rounded" :style="{ backgroundColor: cat.color }"></span>
                            </div>
                            <span :class="['text-gray-900 dark:text-white', !cat.activo && 'opacity-50']">
                                {{ cat.nombre }}
                            </span>
                            <span v-if="!cat.activo" class="text-xs text-gray-400">(inactiva)</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="abrirModalCategoria(cat)"
                            >
                                <PencilIcon class="w-4 h-4" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="confirmarEliminarCategoria(cat)"
                                class="text-red-500 hover:text-red-600"
                            >
                                <TrashIcon class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                    <p v-if="categoriasStore.categorias.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-4">
                        No hay categorias configuradas
                    </p>
                </div>
            </div>
        </div>

        <!-- Seccion: Medios de Pago -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <button
                @click="toggleSeccion('mediosPago')"
                class="w-full flex items-center justify-between p-4 text-left"
            >
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900 flex items-center justify-center">
                        <CreditCardIcon class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">Medios de Pago</span>
                </div>
                <ChevronDownIcon
                    :class="[
                        'w-5 h-5 text-gray-500 transition-transform duration-200',
                        seccionesAbiertas.mediosPago ? 'rotate-180' : ''
                    ]"
                />
            </button>
            <div v-show="seccionesAbiertas.mediosPago" class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700">
                <div class="pt-3 flex justify-end">
                    <Button size="sm" @click="abrirModalMedioPago()">
                        <PlusIcon class="w-4 h-4 mr-1" />
                        Nuevo
                    </Button>
                </div>
                <div class="mt-3 space-y-2">
                    <div
                        v-for="mp in mediosPagoStore.mediosPago"
                        :key="mp.id"
                        class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0"
                    >
                        <div class="flex items-center gap-3">
                            <span :class="['w-2 h-2 rounded-full', mp.activo ? 'bg-green-500' : 'bg-gray-400']"></span>
                            <span :class="['text-gray-900 dark:text-white', !mp.activo && 'opacity-50']">
                                {{ mp.nombre }}
                            </span>
                            <span v-if="!mp.activo" class="text-xs text-gray-400">(inactivo)</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="abrirModalMedioPago(mp)"
                            >
                                <PencilIcon class="w-4 h-4" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="confirmarEliminarMedioPago(mp)"
                                class="text-red-500 hover:text-red-600"
                            >
                                <TrashIcon class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                    <p v-if="mediosPagoStore.mediosPago.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-4">
                        No hay medios de pago configurados
                    </p>
                </div>
            </div>
        </div>

        <!-- Seccion: Cuenta -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <button
                @click="toggleSeccion('cuenta')"
                class="w-full flex items-center justify-between p-4 text-left"
            >
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                        <UserIcon class="w-4 h-4 text-red-600 dark:text-red-400" />
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">Cuenta</span>
                </div>
                <ChevronDownIcon
                    :class="[
                        'w-5 h-5 text-gray-500 transition-transform duration-200',
                        seccionesAbiertas.cuenta ? 'rotate-180' : ''
                    ]"
                />
            </button>
            <div v-show="seccionesAbiertas.cuenta" class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700">
                <div class="mt-4 space-y-4">
                    <!-- Restablecer datos -->
                    <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                        <h4 class="font-medium text-red-800 dark:text-red-300">Restablecer datos</h4>
                        <p class="text-sm text-red-600 dark:text-red-400 mt-1">
                            Elimina todos tus gastos, abonos, categorias, medios de pago y plantillas.
                            Se restauraran las categorias y medios de pago por defecto.
                        </p>
                        <Button
                            variant="danger"
                            size="sm"
                            class="mt-3"
                            @click="showModalRestablecer = true"
                        >
                            <ArrowPathIcon class="w-4 h-4 mr-1" />
                            Restablecer todo
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Categoria -->
        <Modal :show="showModalCategoria" :title="categoriaEditando ? 'Editar Categoria' : 'Nueva Categoria'" @close="cerrarModalCategoria">
            <div class="space-y-4">
                <Input
                    v-model="formCategoria.nombre"
                    label="Nombre"
                    placeholder="Ej: Comida, Transporte..."
                    :error="erroresCategoria.nombre"
                />
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Icono
                    </label>
                    <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto p-2 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <button
                            v-for="icono in iconosDisponibles"
                            :key="icono"
                            type="button"
                            @click="formCategoria.icono = icono"
                            :class="[
                                'w-9 h-9 flex items-center justify-center rounded-lg border-2 transition-all',
                                formCategoria.icono === icono
                                    ? 'border-primary bg-primary/10 dark:bg-primary/20'
                                    : 'border-transparent hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        >
                            <i :class="['text-lg', icono]" :style="{ color: formCategoria.color }"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Color
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="color in coloresDisponibles"
                            :key="color"
                            type="button"
                            @click="formCategoria.color = color"
                            :class="[
                                'w-8 h-8 rounded-full border-2 transition-all',
                                formCategoria.color === color ? 'border-gray-900 dark:border-white scale-110' : 'border-transparent'
                            ]"
                            :style="{ backgroundColor: color }"
                        ></button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        id="categoriaActiva"
                        v-model="formCategoria.activo"
                        class="rounded border-gray-300 text-primary focus:ring-primary"
                    />
                    <label for="categoriaActiva" class="text-sm text-gray-700 dark:text-gray-300">
                        Categoria activa
                    </label>
                </div>
            </div>
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="secondary" @click="cerrarModalCategoria">Cancelar</Button>
                    <Button @click="guardarCategoria" :loading="guardandoCategoria">
                        {{ categoriaEditando ? 'Actualizar' : 'Crear' }}
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Modal Confirmar Eliminar Categoria -->
        <Modal :show="showModalEliminar" title="Eliminar Categoria" @close="showModalEliminar = false">
            <p class="text-gray-600 dark:text-gray-400">
                Estas seguro de eliminar la categoria <strong>{{ categoriaEliminar?.nombre }}</strong>?
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                Solo se puede eliminar si no tiene gastos asociados.
            </p>
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="secondary" @click="showModalEliminar = false">Cancelar</Button>
                    <Button variant="danger" @click="eliminarCategoria" :loading="eliminandoCategoria">
                        Eliminar
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Modal Medio de Pago -->
        <Modal :show="showModalMedioPago" :title="medioPagoEditando ? 'Editar Medio de Pago' : 'Nuevo Medio de Pago'" @close="cerrarModalMedioPago">
            <div class="space-y-4">
                <Input
                    v-model="formMedioPago.nombre"
                    label="Nombre"
                    placeholder="Ej: Efectivo, Tarjeta Debito..."
                    :error="erroresMedioPago.nombre"
                />
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        id="medioPagoActivo"
                        v-model="formMedioPago.activo"
                        class="rounded border-gray-300 text-primary focus:ring-primary"
                    />
                    <label for="medioPagoActivo" class="text-sm text-gray-700 dark:text-gray-300">
                        Medio de pago activo
                    </label>
                </div>
            </div>
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="secondary" @click="cerrarModalMedioPago">Cancelar</Button>
                    <Button @click="guardarMedioPago" :loading="guardandoMedioPago">
                        {{ medioPagoEditando ? 'Actualizar' : 'Crear' }}
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Modal Confirmar Eliminar Medio de Pago -->
        <Modal :show="showModalEliminarMedioPago" title="Eliminar Medio de Pago" @close="showModalEliminarMedioPago = false">
            <p class="text-gray-600 dark:text-gray-400">
                Estas seguro de eliminar el medio de pago <strong>{{ medioPagoEliminar?.nombre }}</strong>?
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                Solo se puede eliminar si no tiene gastos asociados.
            </p>
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="secondary" @click="showModalEliminarMedioPago = false">Cancelar</Button>
                    <Button variant="danger" @click="eliminarMedioPago" :loading="eliminandoMedioPago">
                        Eliminar
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Modal Confirmar Restablecer Datos -->
        <Modal :show="showModalRestablecer" title="Restablecer todos los datos" @close="showModalRestablecer = false">
            <div class="space-y-3">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full">
                    <ExclamationTriangleIcon class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <p class="text-center text-gray-600 dark:text-gray-400">
                    Esta accion eliminara <strong>permanentemente</strong> todos tus datos:
                </p>
                <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-1 list-disc list-inside">
                    <li>Todos los gastos registrados</li>
                    <li>Todos los abonos</li>
                    <li>Todas las categorias personalizadas</li>
                    <li>Todos los medios de pago personalizados</li>
                    <li>Todas las plantillas</li>
                    <li>Todos los gastos recurrentes</li>
                </ul>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                    Se restauraran las categorias y medios de pago por defecto.
                </p>
                <p class="text-sm font-medium text-red-600 dark:text-red-400 text-center">
                    Esta accion no se puede deshacer.
                </p>
            </div>
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="secondary" @click="showModalRestablecer = false">Cancelar</Button>
                    <Button variant="danger" @click="restablecerDatos" :loading="restableciendo">
                        Si, restablecer todo
                    </Button>
                </div>
            </template>
        </Modal>

        <Toast
            :show="showToast"
            :message="toastMessage"
            :type="toastType"
            @close="showToast = false"
        />
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import {
    PlusIcon,
    PencilIcon,
    TrashIcon,
    ChevronDownIcon,
    PaintBrushIcon,
    TagIcon,
    CreditCardIcon,
    UserIcon,
    ArrowPathIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';
import Card from '../Components/UI/Card.vue';
import Input from '../Components/UI/Input.vue';
import Button from '../Components/UI/Button.vue';
import Modal from '../Components/UI/Modal.vue';
import Toast from '../Components/UI/Toast.vue';
import { useThemeStore } from '../Stores/theme';
import { useMediosPagoStore } from '../Stores/mediosPago';
import { useCategoriasStore } from '../Stores/categorias';
import { useConfigStore } from '../Stores/config';
import axios from 'axios';

const themeStore = useThemeStore();
const mediosPagoStore = useMediosPagoStore();
const categoriasStore = useCategoriasStore();
const configStore = useConfigStore();

// Secciones del acordeon
const seccionesAbiertas = reactive({
    apariencia: false,
    categorias: false,
    mediosPago: false,
    cuenta: false
});

const toggleSeccion = (seccion) => {
    seccionesAbiertas[seccion] = !seccionesAbiertas[seccion];
};

const temas = [
    { value: 'light', label: 'Claro' },
    { value: 'dark', label: 'Oscuro' },
    { value: 'system', label: 'Sistema' }
];

const coloresDisponibles = [
    '#EF4444', // red
    '#F97316', // orange
    '#F59E0B', // amber
    '#EAB308', // yellow
    '#84CC16', // lime
    '#22C55E', // green
    '#10B981', // emerald
    '#14B8A6', // teal
    '#06B6D4', // cyan
    '#0EA5E9', // sky
    '#3B82F6', // blue
    '#6366F1', // indigo
    '#8B5CF6', // violet
    '#A855F7', // purple
    '#D946EF', // fuchsia
    '#EC4899', // pink
];

// Iconos de PrimeIcons para categorias de gastos
const iconosDisponibles = [
    'pi pi-shopping-cart',
    'pi pi-shopping-bag',
    'pi pi-car',
    'pi pi-home',
    'pi pi-bolt',
    'pi pi-phone',
    'pi pi-wifi',
    'pi pi-heart',
    'pi pi-gift',
    'pi pi-ticket',
    'pi pi-wallet',
    'pi pi-credit-card',
    'pi pi-money-bill',
    'pi pi-building',
    'pi pi-briefcase',
    'pi pi-book',
    'pi pi-graduation-cap',
    'pi pi-users',
    'pi pi-user',
    'pi pi-wrench',
    'pi pi-cog',
    'pi pi-desktop',
    'pi pi-mobile',
    'pi pi-tablet',
    'pi pi-camera',
    'pi pi-video',
    'pi pi-headphones',
    'pi pi-microphone',
    'pi pi-sun',
    'pi pi-cloud',
    'pi pi-star',
    'pi pi-flag',
    'pi pi-map-marker',
    'pi pi-globe',
    'pi pi-compass',
    'pi pi-send',
    'pi pi-inbox',
    'pi pi-tag',
    'pi pi-tags',
    'pi pi-percentage',
    'pi pi-chart-bar',
    'pi pi-chart-line',
    'pi pi-clock',
    'pi pi-calendar',
    'pi pi-trophy',
    'pi pi-sparkles',
    'pi pi-face-smile',
    'pi pi-palette',
    'pi pi-scissors',
    'pi pi-hammer',
];

// Toast
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const mostrarToast = (mensaje, tipo = 'success') => {
    toastMessage.value = mensaje;
    toastType.value = tipo;
    showToast.value = true;
};

// Categoria Modal
const showModalCategoria = ref(false);
const categoriaEditando = ref(null);
const guardandoCategoria = ref(false);
const erroresCategoria = reactive({});

const formCategoria = reactive({
    nombre: '',
    icono: 'pi pi-tag',
    color: '#3B82F6',
    activo: true
});

const abrirModalCategoria = (categoria = null) => {
    categoriaEditando.value = categoria;
    if (categoria) {
        formCategoria.nombre = categoria.nombre;
        formCategoria.icono = categoria.icono || 'pi pi-tag';
        formCategoria.color = categoria.color;
        formCategoria.activo = categoria.activo;
    } else {
        formCategoria.nombre = '';
        formCategoria.icono = 'pi pi-tag';
        formCategoria.color = '#3B82F6';
        formCategoria.activo = true;
    }
    Object.keys(erroresCategoria).forEach(key => delete erroresCategoria[key]);
    showModalCategoria.value = true;
};

const cerrarModalCategoria = () => {
    showModalCategoria.value = false;
    categoriaEditando.value = null;
};

const guardarCategoria = async () => {
    Object.keys(erroresCategoria).forEach(key => delete erroresCategoria[key]);

    if (!formCategoria.nombre.trim()) {
        erroresCategoria.nombre = 'El nombre es requerido';
        return;
    }

    guardandoCategoria.value = true;
    try {
        if (categoriaEditando.value) {
            await categoriasStore.actualizarCategoria(categoriaEditando.value.id, {
                nombre: formCategoria.nombre,
                icono: formCategoria.icono,
                color: formCategoria.color,
                activo: formCategoria.activo
            });
            mostrarToast('Categoria actualizada');
        } else {
            await categoriasStore.crearCategoria({
                nombre: formCategoria.nombre,
                icono: formCategoria.icono,
                color: formCategoria.color,
                activo: formCategoria.activo
            });
            mostrarToast('Categoria creada');
        }
        cerrarModalCategoria();
    } catch (error) {
        const mensaje = error.response?.data?.message || 'Error al guardar';
        mostrarToast(mensaje, 'error');
    } finally {
        guardandoCategoria.value = false;
    }
};

// Eliminar Categoria
const showModalEliminar = ref(false);
const categoriaEliminar = ref(null);
const eliminandoCategoria = ref(false);

const confirmarEliminarCategoria = (categoria) => {
    categoriaEliminar.value = categoria;
    showModalEliminar.value = true;
};

const eliminarCategoria = async () => {
    if (!categoriaEliminar.value) return;

    eliminandoCategoria.value = true;
    try {
        await categoriasStore.eliminarCategoria(categoriaEliminar.value.id);
        mostrarToast('Categoria eliminada');
        showModalEliminar.value = false;
    } catch (error) {
        const mensaje = error.response?.data?.message || 'Error al eliminar';
        mostrarToast(mensaje, 'error');
    } finally {
        eliminandoCategoria.value = false;
    }
};

// Medios de Pago
const showModalMedioPago = ref(false);
const medioPagoEditando = ref(null);
const guardandoMedioPago = ref(false);
const erroresMedioPago = reactive({});

const formMedioPago = reactive({
    nombre: '',
    activo: true
});

const abrirModalMedioPago = (medioPago = null) => {
    medioPagoEditando.value = medioPago;
    if (medioPago) {
        formMedioPago.nombre = medioPago.nombre;
        formMedioPago.activo = medioPago.activo;
    } else {
        formMedioPago.nombre = '';
        formMedioPago.activo = true;
    }
    Object.keys(erroresMedioPago).forEach(key => delete erroresMedioPago[key]);
    showModalMedioPago.value = true;
};

const cerrarModalMedioPago = () => {
    showModalMedioPago.value = false;
    medioPagoEditando.value = null;
};

const guardarMedioPago = async () => {
    Object.keys(erroresMedioPago).forEach(key => delete erroresMedioPago[key]);

    if (!formMedioPago.nombre.trim()) {
        erroresMedioPago.nombre = 'El nombre es requerido';
        return;
    }

    guardandoMedioPago.value = true;
    try {
        if (medioPagoEditando.value) {
            await mediosPagoStore.actualizarMedioPago(medioPagoEditando.value.id, {
                nombre: formMedioPago.nombre,
                activo: formMedioPago.activo
            });
            mostrarToast('Medio de pago actualizado');
        } else {
            await mediosPagoStore.crearMedioPago({
                nombre: formMedioPago.nombre,
                activo: formMedioPago.activo
            });
            mostrarToast('Medio de pago creado');
        }
        cerrarModalMedioPago();
    } catch (error) {
        const mensaje = error.response?.data?.message || 'Error al guardar';
        mostrarToast(mensaje, 'error');
    } finally {
        guardandoMedioPago.value = false;
    }
};

// Eliminar Medio de Pago
const showModalEliminarMedioPago = ref(false);
const medioPagoEliminar = ref(null);
const eliminandoMedioPago = ref(false);

const confirmarEliminarMedioPago = (medioPago) => {
    medioPagoEliminar.value = medioPago;
    showModalEliminarMedioPago.value = true;
};

const eliminarMedioPago = async () => {
    if (!medioPagoEliminar.value) return;

    eliminandoMedioPago.value = true;
    try {
        await mediosPagoStore.eliminarMedioPago(medioPagoEliminar.value.id);
        mostrarToast('Medio de pago eliminado');
        showModalEliminarMedioPago.value = false;
    } catch (error) {
        const mensaje = error.response?.data?.message || 'Error al eliminar';
        mostrarToast(mensaje, 'error');
    } finally {
        eliminandoMedioPago.value = false;
    }
};

// Theme
const cambiarTema = (tema) => {
    themeStore.setTema(tema);
};

// Divisa
const cambiarDivisa = async (divisa) => {
    try {
        await configStore.actualizarDivisa(divisa);
        mostrarToast('Divisa actualizada');
    } catch (error) {
        mostrarToast('Error al actualizar divisa', 'error');
    }
};

// Formato de Divisa
const cambiarFormatoDivisa = async (formato) => {
    try {
        await configStore.actualizarFormatoDivisa(formato);
        mostrarToast('Formato de divisa actualizado');
    } catch (error) {
        mostrarToast('Error al actualizar formato', 'error');
    }
};

// Restablecer datos
const showModalRestablecer = ref(false);
const restableciendo = ref(false);

const restablecerDatos = async () => {
    restableciendo.value = true;
    try {
        await axios.post('/api/auth/reset-user-data');

        // Recargar los stores con los nuevos datos por defecto
        await Promise.all([
            mediosPagoStore.cargarMediosPago(),
            categoriasStore.cargarCategorias()
        ]);

        showModalRestablecer.value = false;
        mostrarToast('Todos los datos han sido restablecidos');
    } catch (error) {
        const mensaje = error.response?.data?.message || 'Error al restablecer datos';
        mostrarToast(mensaje, 'error');
    } finally {
        restableciendo.value = false;
    }
};

// Cargar datos
onMounted(async () => {
    await Promise.all([
        mediosPagoStore.cargarMediosPago(),
        categoriasStore.cargarCategorias()
    ]);
});
</script>
