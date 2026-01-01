<template>
    <form @submit.prevent="submit" class="space-y-4">
        <!-- Carousel de Categorías / Servicios -->
        <div>
            <!-- Tabs del carousel -->
            <div class="flex border-b border-gray-200 dark:border-gray-700 mb-3">
                <button
                    type="button"
                    @click="tabActivo = 'categorias'"
                    :class="[
                        'flex-1 py-2 text-sm font-medium border-b-2 transition-colors',
                        tabActivo === 'categorias'
                            ? 'border-primary text-primary'
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
                    ]"
                >
                    Categorias
                </button>
                <button
                    v-if="serviciosActivos.length > 0"
                    type="button"
                    @click="tabActivo = 'servicios'"
                    :class="[
                        'flex-1 py-2 text-sm font-medium border-b-2 transition-colors relative',
                        tabActivo === 'servicios'
                            ? 'border-primary text-primary'
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
                    ]"
                >
                    Servicios
                    <span
                        v-if="serviciosPendientes.length > 0"
                        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
                    >
                        {{ serviciosPendientes.length }}
                    </span>
                </button>
            </div>

            <!-- Contenedor con swipe -->
            <div
                ref="swipeContainer"
                class="overflow-hidden touch-pan-y"
                @touchstart="onTouchStart"
                @touchmove="onTouchMove"
                @touchend="onTouchEnd"
            >
                <div
                    class="flex transition-transform duration-300 ease-out"
                    :style="{ transform: `translateX(${swipeOffset}px)` }"
                >
                    <!-- Panel Categorías -->
                    <div class="w-full flex-shrink-0">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Categoria <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="cat in categoriasActivas"
                                :key="cat.id"
                                type="button"
                                @click="seleccionarCategoria(cat)"
                                :class="[
                                    'flex flex-col items-center justify-center p-2 rounded-lg border-2 transition-all min-h-[60px]',
                                    form.categoria_id === cat.id && !servicioSeleccionado
                                        ? 'border-primary bg-primary/10 dark:bg-primary/20'
                                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800'
                                ]"
                            >
                                <div
                                    class="w-8 h-8 rounded-lg flex items-center justify-center mb-1"
                                    :style="{ backgroundColor: cat.color + '20' }"
                                >
                                    <i v-if="cat.icono" :class="cat.icono" :style="{ color: cat.color }"></i>
                                    <span v-else class="w-3 h-3 rounded" :style="{ backgroundColor: cat.color }"></span>
                                </div>
                                <span class="text-xs text-gray-700 dark:text-gray-300 text-center leading-tight truncate w-full">
                                    {{ cat.nombre }}
                                </span>
                            </button>
                        </div>
                        <p v-if="errors.categoria_id" class="mt-1 text-sm text-red-500">{{ errors.categoria_id }}</p>
                    </div>

                    <!-- Panel Servicios -->
                    <div v-if="serviciosActivos.length > 0" class="w-full flex-shrink-0">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Servicio
                        </label>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="serv in serviciosActivos"
                                :key="serv.id"
                                type="button"
                                @click="handleServiceClick(serv)"
                                :class="[
                                    'flex flex-col items-center justify-center p-2 rounded-lg border-2 transition-all min-h-[60px]',
                                    servicioSeleccionado?.id === serv.id
                                        ? 'border-primary bg-primary/10 dark:bg-primary/20'
                                        : serv.pagado
                                            ? 'border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800'
                                ]"
                            >
                                <div
                                    class="w-8 h-8 rounded-lg flex items-center justify-center mb-1"
                                    :style="{
                                        backgroundColor: serv.pagado ? (serv.color + '40') : (serv.color + '20'),
                                        opacity: serv.pagado && servicioSeleccionado?.id !== serv.id ? 0.6 : 1
                                    }"
                                >
                                    <i
                                        v-if="serv.icono"
                                        :class="serv.icono"
                                        :style="{ color: serv.pagado ? '#22c55e' : serv.color }"
                                    ></i>
                                    <i
                                        v-else
                                        class="pi pi-file"
                                        :style="{ color: serv.pagado ? '#22c55e' : serv.color }"
                                    ></i>
                                </div>
                                <span
                                    :class="[
                                        'text-xs text-center leading-tight truncate w-full',
                                        serv.pagado ? 'text-green-600 dark:text-green-400' : 'text-gray-700 dark:text-gray-300'
                                    ]"
                                >
                                    {{ serv.nombre }}
                                </span>
                                <i
                                    v-if="serv.pagado"
                                    class="pi pi-check-circle text-green-500 text-xs mt-0.5"
                                ></i>
                            </button>
                        </div>
                        <p v-if="serviciosPendientes.length === 0 && serviciosActivos.length > 0" class="mt-2 text-sm text-green-600 dark:text-green-400">
                            <i class="pi pi-check-circle mr-1"></i>
                            Todos los servicios estan pagados este mes
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Valor (obligatorio) -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Valor ({{ divisaInfo.codigo }}) <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                    {{ divisaInfo.simbolo }}
                </span>
                <input
                    ref="valorInput"
                    type="text"
                    :value="valorFormateado"
                    @input="onValorInput"
                    placeholder="0"
                    inputmode="decimal"
                    class="w-full pl-8 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                    :class="{ 'border-red-500': errors.valor }"
                />
                <button
                    v-if="valorFormateado"
                    type="button"
                    @click="limpiarValor"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                >
                    <XCircleIcon class="w-5 h-5" />
                </button>
            </div>
            <p v-if="errors.valor" class="mt-1 text-sm text-red-500">{{ errors.valor }}</p>
        </div>

        <!-- Acordeon de opciones adicionales -->
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <button
                type="button"
                @click="showOpciones = !showOpciones"
                class="w-full flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            >
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Mas opciones
                </span>
                <ChevronDownIcon
                    :class="[
                        'w-5 h-5 text-gray-500 transition-transform duration-200',
                        showOpciones ? 'rotate-180' : ''
                    ]"
                />
            </button>
            <div v-show="showOpciones" class="p-3 space-y-3 border-t border-gray-200 dark:border-gray-700">
                <!-- Tipo de gasto - solo mostrar si hay mas de un tipo disponible -->
                <div v-if="tiposGasto.length > 1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tipo de gasto
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="tipo in tiposGasto"
                            :key="tipo.value"
                            type="button"
                            @click="form.tipo = tipo.value"
                            :class="[
                                'py-2 px-2 rounded-lg font-medium text-sm transition-colors text-center',
                                form.tipo === tipo.value
                                    ? 'bg-primary text-white dark:bg-indigo-500'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                            ]"
                        >
                            {{ tipo.label }}
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Por defecto: Mio
                    </p>
                    <p v-if="errors.tipo" class="mt-1 text-sm text-red-500">{{ errors.tipo }}</p>
                </div>

                <!-- Medio de pago - solo mostrar si hay mas de 1 medio -->
                <div v-if="mediosPagoActivos.length > 1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Medio de Pago
                    </label>
                    <div class="grid grid-cols-4 gap-2">
                        <button
                            v-for="mp in mediosPagoActivos"
                            :key="mp.id"
                            type="button"
                            @click="form.medio_pago_id = form.medio_pago_id === mp.id ? '' : mp.id"
                            :class="[
                                'flex flex-col items-center justify-center p-2 rounded-lg border-2 transition-all min-h-[60px]',
                                form.medio_pago_id === mp.id
                                    ? 'border-primary bg-primary/10 dark:bg-primary/20'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800'
                            ]"
                        >
                            <div
                                class="w-8 h-8 rounded-lg flex items-center justify-center mb-1"
                                :style="{ backgroundColor: mp.color ? mp.color + '20' : '#6366f120' }"
                            >
                                <i
                                    v-if="mp.icono"
                                    :class="mp.icono"
                                    :style="{ color: mp.color || '#6366f1' }"
                                ></i>
                                <CreditCardIcon
                                    v-else
                                    class="w-4 h-4"
                                    :style="{ color: mp.color || '#6366f1' }"
                                />
                            </div>
                            <span class="text-xs text-gray-700 dark:text-gray-300 text-center leading-tight truncate w-full">
                                {{ mp.nombre }}
                            </span>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Toca de nuevo para deseleccionar
                    </p>
                    <p v-if="errors.medio_pago_id" class="mt-1 text-sm text-red-500">{{ errors.medio_pago_id }}</p>
                </div>

                <!-- Concepto (opcional) -->
                <div class="relative">
                    <Input
                        v-model="form.concepto"
                        label="Concepto"
                        placeholder="Ej: Almuerzo, Mercado..."
                        :error="errors.concepto"
                        @input="buscarConceptos"
                        @focus="showSugerencias = true"
                    />
                    <!-- Sugerencias de autocompletado -->
                    <div
                        v-if="showSugerencias && sugerencias.length > 0"
                        class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                    >
                        <button
                            v-for="sug in sugerencias"
                            :key="sug.id"
                            type="button"
                            @click="seleccionarConcepto(sug)"
                            class="w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                        >
                            <StarIcon v-if="sug.es_favorito" class="w-4 h-4 text-yellow-500" />
                            <span class="text-gray-900 dark:text-white">{{ sug.concepto }}</span>
                        </button>
                    </div>
                </div>

                <!-- Fecha -->
                <div class="w-full">
                    <Input
                        v-model="form.fecha"
                        type="date"
                        label="Fecha"
                        :error="errors.fecha"
                    />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Si no se indica, se usa la fecha de hoy
                    </p>
                </div>
            </div>
        </div>

        <Button type="submit" class="w-full" :loading="loading" :disabled="!isValid">
            {{ submitText }}
        </Button>
    </form>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { StarIcon } from '@heroicons/vue/24/solid';
import { ChevronDownIcon, XCircleIcon, CreditCardIcon } from '@heroicons/vue/24/outline';
import Input from '../UI/Input.vue';
import Button from '../UI/Button.vue';
import { useMediosPagoStore } from '../../Stores/mediosPago';
import { useCategoriasStore } from '../../Stores/categorias';
import { useConfigStore } from '../../Stores/config';
import { useConceptosFrecuentesStore } from '../../Stores/conceptosFrecuentes';
import { useServiciosStore } from '../../Stores/servicios';
import { useCurrency } from '../../Composables/useCurrency';

const props = defineProps({
    gasto: Object,
    loading: Boolean,
    errors: {
        type: Object,
        default: () => ({})
    },
    submitText: {
        type: String,
        default: 'Guardar'
    },
    seccionInicial: {
        type: String,
        default: null
    }
});

const emit = defineEmits(['submit', 'servicio-pagado', 'referencia-copiada']);

const mediosPagoStore = useMediosPagoStore();
const categoriasStore = useCategoriasStore();
const configStore = useConfigStore();
const conceptosStore = useConceptosFrecuentesStore();
const serviciosStore = useServiciosStore();
const { formatInputValue, parseFormattedValue, divisaInfo } = useCurrency();

const showSugerencias = ref(false);
const showOpciones = ref(false);
const tabActivo = ref('categorias');
const servicioSeleccionado = ref(null);
const valorInput = ref(null);
const sugerencias = computed(() => conceptosStore.sugerencias);

// Swipe para cambiar tabs
const swipeContainer = ref(null);
const touchStartX = ref(0);
const touchCurrentX = ref(0);
const isSwiping = ref(false);
const swipeThreshold = 50;

// Doble click para copiar referencia
const lastClickTime = ref(0);
const lastClickServiceId = ref(null);
const DOUBLE_CLICK_DELAY = 300;

const handleServiceClick = (servicio) => {
    const now = Date.now();

    // Verificar si es doble click en el mismo servicio
    if (lastClickServiceId.value === servicio.id && (now - lastClickTime.value) < DOUBLE_CLICK_DELAY) {
        // Doble click - copiar referencia si tiene
        if (servicio.referencia) {
            copiarReferencia(servicio);
        }
        lastClickTime.value = 0;
        lastClickServiceId.value = null;
    } else {
        // Primer click - seleccionar servicio
        lastClickTime.value = now;
        lastClickServiceId.value = servicio.id;
        seleccionarServicio(servicio);
    }
};

const copiarReferencia = async (servicio) => {
    try {
        await navigator.clipboard.writeText(servicio.referencia);

        if (navigator.vibrate) {
            navigator.vibrate(100);
        }

        emit('referencia-copiada', servicio.referencia);
    } catch (error) {
        console.error('Error al copiar referencia:', error);
    }
};

const swipeOffset = computed(() => {
    const containerWidth = swipeContainer.value?.offsetWidth || 0;
    const baseOffset = tabActivo.value === 'servicios' ? -containerWidth : 0;

    if (isSwiping.value) {
        const diff = touchCurrentX.value - touchStartX.value;
        // Limitar el swipe para no pasar de los límites
        if (tabActivo.value === 'categorias' && diff > 0) return 0;
        if (tabActivo.value === 'servicios' && diff < 0) return -containerWidth;
        return baseOffset + diff;
    }

    return baseOffset;
});

const onTouchStart = (e) => {
    if (serviciosActivos.value.length === 0) return;
    touchStartX.value = e.touches[0].clientX;
    touchCurrentX.value = e.touches[0].clientX;
    isSwiping.value = true;
};

const onTouchMove = (e) => {
    if (!isSwiping.value) return;
    touchCurrentX.value = e.touches[0].clientX;
};

const onTouchEnd = () => {
    if (!isSwiping.value) return;

    const diff = touchCurrentX.value - touchStartX.value;

    if (Math.abs(diff) > swipeThreshold) {
        if (diff < 0 && tabActivo.value === 'categorias' && serviciosActivos.value.length > 0) {
            tabActivo.value = 'servicios';
        } else if (diff > 0 && tabActivo.value === 'servicios') {
            tabActivo.value = 'categorias';
        }
    }

    isSwiping.value = false;
};

// Valor formateado para mostrar en el input
const valorFormateado = ref('');

// Actualizar valor formateado cuando cambia el valor numérico
const actualizarValorFormateado = (valorNumerico) => {
    if (valorNumerico) {
        valorFormateado.value = formatInputValue(valorNumerico);
    } else {
        valorFormateado.value = '';
    }
};

// Manejar input del usuario en el campo de valor
const onValorInput = (event) => {
    const inputValue = event.target.value;
    valorFormateado.value = formatInputValue(inputValue);
    form.value.valor = parseFormattedValue(inputValue);
};

// Limpiar el valor
const limpiarValor = () => {
    valorFormateado.value = '';
    form.value.valor = '';
};

const form = ref({
    fecha: '',
    medio_pago_id: '',
    categoria_id: '',
    concepto: '',
    valor: '',
    tipo: 'personal'
});

// Cargar datos iniciales
onMounted(async () => {
    if (mediosPagoStore.mediosPago.length === 0) {
        await mediosPagoStore.cargarMediosPago(true);
    }
    if (categoriasStore.categorias.length === 0) {
        await categoriasStore.cargarCategorias(true);
    }
    if (serviciosStore.servicios.length === 0) {
        await serviciosStore.cargarServicios();
    }

    // Si hay gasto para editar, cargar datos y mostrar opciones
    if (props.gasto) {
        form.value = { ...props.gasto };
        actualizarValorFormateado(props.gasto.valor);
        if (props.gasto.fecha || props.gasto.medio_pago_id || props.gasto.tipo !== 'personal' || props.gasto.concepto) {
            showOpciones.value = true;
        }
    }

    // Si hay exactamente 1 medio de pago, asignarlo automaticamente
    if (mediosPagoStore.activos.length === 1 && !form.value.medio_pago_id) {
        form.value.medio_pago_id = mediosPagoStore.activos[0].id;
    }

    // Si viene con seccion inicial de servicios, cambiar a esa pestaña
    if (props.seccionInicial === 'servicios' && serviciosStore.activos.length > 0) {
        tabActivo.value = 'servicios';
    }
});

const mediosPagoActivos = computed(() => mediosPagoStore.activos);

const categoriasActivas = computed(() => categoriasStore.activas);
const serviciosActivos = computed(() => serviciosStore.activos);
const serviciosPendientes = computed(() => serviciosStore.pendientes);

const tiposGasto = computed(() => configStore.tiposGasto);

const isValid = computed(() =>
    form.value.valor > 0 &&
    form.value.categoria_id
);

// Seleccionar categoría (limpia servicio seleccionado)
const seleccionarCategoria = (cat) => {
    form.value.categoria_id = cat.id;
    servicioSeleccionado.value = null;
    form.value.concepto = '';
};

// Seleccionar servicio (auto-rellena campos)
const seleccionarServicio = (servicio) => {
    servicioSeleccionado.value = servicio;

    // Auto-rellenar campos
    if (servicio.categoria_id) {
        form.value.categoria_id = servicio.categoria_id;
    }
    form.value.concepto = servicio.nombre;

    // Si tiene valor estimado, usarlo
    if (servicio.valor_estimado) {
        form.value.valor = servicio.valor_estimado;
        actualizarValorFormateado(servicio.valor_estimado);
    }

    // Enfocar el input de valor para que pueda modificar si necesita
    nextTick(() => {
        if (valorInput.value) {
            valorInput.value.focus();
            valorInput.value.select();
        }
    });
};

let debounceTimer;
const buscarConceptos = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        conceptosStore.buscar(form.value.concepto);
    }, 300);
};

const seleccionarConcepto = (concepto) => {
    form.value.concepto = concepto.concepto;
    if (concepto.medio_pago_id) {
        form.value.medio_pago_id = concepto.medio_pago_id;
    }
    if (concepto.tipo && concepto.tipo !== 'personal') {
        form.value.tipo = concepto.tipo;
    }
    showSugerencias.value = false;
    conceptosStore.limpiarSugerencias();
};

// Función para limpiar el formulario
const resetForm = () => {
    servicioSeleccionado.value = null;
    tabActivo.value = 'categorias';
    form.value = {
        fecha: '',
        medio_pago_id: '',
        categoria_id: '',
        concepto: '',
        valor: '',
        tipo: 'personal'
    };
    valorFormateado.value = '';
    showOpciones.value = false;
};

// Exponer la función reset para uso externo
defineExpose({ resetForm });

const submit = async () => {
    // Si es un servicio, marcarlo como pagado
    if (servicioSeleccionado.value) {
        try {
            await serviciosStore.marcarPagado(servicioSeleccionado.value.id, {
                fecha: form.value.fecha || new Date().toLocaleDateString('sv-SE'),
                valor: form.value.valor,
                medio_pago_id: form.value.medio_pago_id || null,
                tipo: form.value.tipo,
                crear_gasto: true
            });
            emit('servicio-pagado', servicioSeleccionado.value);
            resetForm();
        } catch (error) {
            console.error('Error marcando servicio como pagado:', error);
        }
    } else {
        // Flujo normal de gasto
        emit('submit', { ...form.value });
    }
};

// Cerrar sugerencias al hacer clic fuera
document.addEventListener('click', (e) => {
    if (!e.target.closest('.relative')) {
        showSugerencias.value = false;
    }
});
</script>
