<template>
    <form @submit.prevent="submit" class="space-y-4">
        <!-- Categoria (obligatoria) -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Categoria <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-4 gap-2">
                <button
                    v-for="cat in categorias"
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
                    v-model="valorFormateado"
                    @input="onValorInput"
                    type="text"
                    inputmode="decimal"
                    placeholder="0"
                    class="w-full pl-8 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
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
                <div v-if="props.tiposGasto.length > 1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tipo de gasto
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="tipo in props.tiposGasto"
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
                </div>

                <!-- Medio de pago - solo mostrar si hay mas de 1 medio -->
                <div v-if="mediosPago.length > 1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Medio de Pago
                    </label>
                    <div class="grid grid-cols-4 gap-2">
                        <button
                            v-for="mp in mediosPago"
                            :key="mp.id"
                            type="button"
                            @click="form.medio_pago_id = form.medio_pago_id === mp.id ? null : mp.id"
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
                </div>

                <!-- Concepto (opcional) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Concepto
                    </label>
                    <input
                        v-model="form.concepto"
                        type="text"
                        placeholder="Ej: Almuerzo, Mercado..."
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                    />
                </div>

                <!-- Fecha -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Fecha
                    </label>
                    <input
                        v-model="form.fecha"
                        type="date"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                    />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Si no se indica, se usa la fecha de hoy
                    </p>
                </div>
            </div>
        </div>

        <!-- Boton submit -->
        <button
            type="submit"
            :disabled="!isValid || loading"
            class="w-full py-3 bg-primary text-white rounded-lg font-medium hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
        >
            <span v-if="loading" class="animate-spin w-5 h-5 border-2 border-white border-t-transparent rounded-full"></span>
            <span>Enviar Solicitud</span>
        </button>

        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
            La solicitud sera enviada al propietario para su aprobacion
        </p>
    </form>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { CreditCardIcon, ChevronDownIcon, XCircleIcon } from '@heroicons/vue/24/outline';
import { useCurrency } from '../../Composables/useCurrency';

const props = defineProps({
    categorias: {
        type: Array,
        default: () => []
    },
    mediosPago: {
        type: Array,
        default: () => []
    },
    tiposGasto: {
        type: Array,
        default: () => [{ value: 'personal', label: 'Mio' }]
    },
    loading: Boolean
});

const emit = defineEmits(['submit']);

const { formatInputValue, parseFormattedValue, divisaInfo } = useCurrency();

const showOpciones = ref(false);

const form = ref({
    fecha: '',
    medio_pago_id: null,
    categoria_id: null,
    concepto: '',
    valor: 0,
    tipo: 'personal'
});

const valorFormateado = ref('');

// Validacion: categoria y valor son obligatorios
const isValid = computed(() => {
    return form.value.categoria_id && form.value.valor > 0;
});

onMounted(() => {
    // Si hay exactamente 1 medio de pago, asignarlo automaticamente
    if (props.mediosPago.length === 1 && !form.value.medio_pago_id) {
        form.value.medio_pago_id = props.mediosPago[0].id;
    }
});

const onValorInput = (event) => {
    const inputValue = event.target.value;
    valorFormateado.value = formatInputValue(inputValue);
    form.value.valor = parseFormattedValue(inputValue);
};

const limpiarValor = () => {
    valorFormateado.value = '';
    form.value.valor = 0;
};

const submit = () => {
    if (isValid.value) {
        emit('submit', { ...form.value });
        // Reset form
        form.value = {
            fecha: '',
            medio_pago_id: props.mediosPago.length === 1 ? props.mediosPago[0].id : null,
            categoria_id: null,
            concepto: '',
            valor: 0,
            tipo: 'personal'
        };
        valorFormateado.value = '';
        showOpciones.value = false;
    }
};
</script>
