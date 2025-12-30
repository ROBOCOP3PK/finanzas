<template>
    <form @submit.prevent="submit" class="space-y-4">
        <!-- Categoria -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Categoria
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
                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
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

        <!-- Valor -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Valor <span class="text-red-500">*</span>
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
                    class="w-full pl-8 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                />
            </div>
        </div>

        <!-- Concepto -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Concepto <span class="text-red-500">*</span>
            </label>
            <input
                v-model="form.concepto"
                type="text"
                placeholder="Ej: Almuerzo, Mercado..."
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
            />
        </div>

        <!-- Tipo de gasto -->
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
                        'py-2 px-2 rounded-lg font-medium text-sm transition-colors text-center',
                        form.tipo === tipo.value
                            ? 'bg-primary text-white dark:bg-indigo-500'
                            : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                    ]"
                >
                    {{ tipo.label }}
                </button>
            </div>
        </div>

        <!-- Medio de pago -->
        <div>
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
                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                    ]"
                >
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-1 bg-gray-100 dark:bg-gray-700">
                        <i v-if="mp.icono" :class="mp.icono" class="text-gray-600 dark:text-gray-400"></i>
                        <CreditCardIcon v-else class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                    </div>
                    <span class="text-xs text-gray-700 dark:text-gray-300 text-center leading-tight truncate w-full">
                        {{ mp.nombre }}
                    </span>
                </button>
            </div>
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
import { ref, computed } from 'vue';
import { CreditCardIcon } from '@heroicons/vue/24/outline';
import { useCurrency } from '../../Composables/useCurrency';
import { useConfigStore } from '../../Stores/config';

const props = defineProps({
    categorias: {
        type: Array,
        default: () => []
    },
    mediosPago: {
        type: Array,
        default: () => []
    },
    loading: Boolean
});

const emit = defineEmits(['submit']);

const configStore = useConfigStore();
const { formatInputValue, parseFormattedValue, divisaInfo } = useCurrency();

const form = ref({
    fecha: '',
    medio_pago_id: null,
    categoria_id: null,
    concepto: '',
    valor: 0,
    tipo: 'personal'
});

const valorFormateado = ref('');

const tiposGasto = computed(() => configStore.tiposGasto);

const isValid = computed(() => {
    return form.value.concepto.trim() && form.value.valor > 0;
});

const onValorInput = (event) => {
    const inputValue = event.target.value;
    valorFormateado.value = formatInputValue(inputValue);
    form.value.valor = parseFormattedValue(inputValue);
};

const submit = () => {
    if (isValid.value) {
        emit('submit', { ...form.value });
        // Reset form
        form.value = {
            fecha: '',
            medio_pago_id: null,
            categoria_id: null,
            concepto: '',
            valor: 0,
            tipo: 'personal'
        };
        valorFormateado.value = '';
    }
};
</script>
