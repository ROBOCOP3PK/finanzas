<template>
    <form @submit.prevent="submit" class="space-y-4">
        <!-- Campos obligatorios -->
        <div class="relative">
            <Input
                v-model="form.concepto"
                label="Concepto"
                placeholder="Ej: Almuerzo, Mercado..."
                required
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

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Valor ({{ divisaInfo.codigo }})
            </label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                    {{ divisaInfo.simbolo }}
                </span>
                <input
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

        <!-- Selector de Categoria con iconos -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Categoria <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-4 gap-2">
                <button
                    v-for="cat in categoriasActivas"
                    :key="cat.id"
                    type="button"
                    @click="form.categoria_id = cat.id"
                    :class="[
                        'flex flex-col items-center justify-center p-2 rounded-lg border-2 transition-all min-h-[60px]',
                        form.categoria_id === cat.id
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
            <div v-show="showOpciones" class="p-4 space-y-4 border-t border-gray-200 dark:border-gray-700">
                <Input
                    v-model="form.fecha"
                    type="date"
                    label="Fecha"
                    :error="errors.fecha"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 -mt-2">
                    Si no se indica, se usa la fecha de hoy
                </p>

                <Select
                    v-model="form.medio_pago_id"
                    label="Medio de Pago"
                    :options="mediosPagoOptions"
                    placeholder="Selecciona un medio de pago"
                    :error="errors.medio_pago_id"
                />

                <div>
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
                                'py-3 px-4 rounded-lg font-medium text-sm transition-colors',
                                form.tipo === tipo.value
                                    ? 'bg-primary text-white dark:bg-indigo-500'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                            ]"
                        >
                            {{ tipo.label }}
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Por defecto: Personal
                    </p>
                    <p v-if="errors.tipo" class="mt-1 text-sm text-red-500">{{ errors.tipo }}</p>
                </div>
            </div>
        </div>

        <Button type="submit" class="w-full" :loading="loading" :disabled="!isValid">
            {{ submitText }}
        </Button>
    </form>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { StarIcon } from '@heroicons/vue/24/solid';
import { ChevronDownIcon, XCircleIcon } from '@heroicons/vue/24/outline';
import Input from '../UI/Input.vue';
import Select from '../UI/Select.vue';
import Button from '../UI/Button.vue';
import { useMediosPagoStore } from '../../Stores/mediosPago';
import { useCategoriasStore } from '../../Stores/categorias';
import { useConfigStore } from '../../Stores/config';
import { useConceptosFrecuentesStore } from '../../Stores/conceptosFrecuentes';
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
    }
});

const emit = defineEmits(['submit']);

const mediosPagoStore = useMediosPagoStore();
const categoriasStore = useCategoriasStore();
const configStore = useConfigStore();
const conceptosStore = useConceptosFrecuentesStore();
const { formatInputValue, parseFormattedValue, divisaInfo } = useCurrency();

const showSugerencias = ref(false);
const showOpciones = ref(false);
const sugerencias = computed(() => conceptosStore.sugerencias);

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
    // Formatear el input mientras el usuario escribe (preserva decimales)
    valorFormateado.value = formatInputValue(inputValue);
    // Parsear el valor numérico para el formulario
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

    // Si hay gasto para editar, cargar datos y mostrar opciones
    if (props.gasto) {
        form.value = { ...props.gasto };
        actualizarValorFormateado(props.gasto.valor);
        // Si tiene datos opcionales, mostrar el acordeon
        if (props.gasto.fecha || props.gasto.medio_pago_id || props.gasto.tipo !== 'personal') {
            showOpciones.value = true;
        }
    }
});

const mediosPagoOptions = computed(() => [
    { value: '', label: 'Sin medio de pago' },
    ...mediosPagoStore.activos.map(mp => ({ value: mp.id, label: mp.nombre }))
]);

const categoriasActivas = computed(() => categoriasStore.activas);

const tiposGasto = computed(() => configStore.tiposGasto);

const isValid = computed(() =>
    form.value.concepto &&
    form.value.valor > 0 &&
    form.value.categoria_id
);

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
        showOpciones.value = true;
    }
    if (concepto.tipo && concepto.tipo !== 'personal') {
        form.value.tipo = concepto.tipo;
        showOpciones.value = true;
    }
    showSugerencias.value = false;
    conceptosStore.limpiarSugerencias();
};

const submit = () => {
    emit('submit', { ...form.value });
};

// Cerrar sugerencias al hacer clic fuera
document.addEventListener('click', (e) => {
    if (!e.target.closest('.relative')) {
        showSugerencias.value = false;
    }
});
</script>
