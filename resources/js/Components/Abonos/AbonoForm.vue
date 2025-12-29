<template>
    <form @submit.prevent="submit" class="space-y-4">
        <Input
            v-model="form.fecha"
            type="date"
            label="Fecha"
            required
            :error="errors.fecha"
        />

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
                    class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                    :class="{ 'border-red-500': errors.valor }"
                />
            </div>
            <p v-if="errors.valor" class="mt-1 text-sm text-red-500">{{ errors.valor }}</p>
        </div>

        <Input
            v-model="form.nota"
            label="Nota (opcional)"
            placeholder="Ej: Transferencia bancaria..."
            :error="errors.nota"
        />

        <Button type="submit" class="w-full" :loading="loading" :disabled="!isValid">
            {{ submitText }}
        </Button>
    </form>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Input from '../UI/Input.vue';
import Button from '../UI/Button.vue';
import { useCurrency } from '../../Composables/useCurrency';

const props = defineProps({
    abono: Object,
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
const { formatInputValue, parseFormattedValue, divisaInfo } = useCurrency();

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

const form = ref({
    fecha: new Date().toISOString().split('T')[0],
    valor: '',
    nota: ''
});

onMounted(() => {
    if (props.abono) {
        form.value = { ...props.abono };
        actualizarValorFormateado(props.abono.valor);
    }
});

const isValid = computed(() =>
    form.value.fecha &&
    form.value.valor > 0
);

const submit = () => {
    emit('submit', { ...form.value });
};
</script>
