<template>
    <form @submit.prevent="submit" class="space-y-4">
        <Input
            v-model="form.fecha"
            type="date"
            label="Fecha"
            required
            :error="errors.fecha"
        />

        <Select
            v-model="form.medio_pago_id"
            label="Medio de Pago"
            :options="mediosPagoOptions"
            placeholder="Selecciona un medio de pago"
            required
            :error="errors.medio_pago_id"
        />

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

        <Select
            v-model="form.categoria_id"
            label="Categoría (opcional)"
            :options="categoriasOptions"
            placeholder="Selecciona una categoría"
            :error="errors.categoria_id"
        />

        <Input
            v-model="form.valor"
            type="number"
            label="Valor"
            placeholder="0"
            :min="1"
            :step="100"
            required
            :error="errors.valor"
        />

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                ¿De quién es este gasto?
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
            <p v-if="errors.tipo" class="mt-1 text-sm text-red-500">{{ errors.tipo }}</p>
        </div>

        <Button type="submit" class="w-full" :loading="loading" :disabled="!isValid">
            {{ submitText }}
        </Button>
    </form>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { StarIcon } from '@heroicons/vue/24/solid';
import Input from '../UI/Input.vue';
import Select from '../UI/Select.vue';
import Button from '../UI/Button.vue';
import { useMediosPagoStore } from '../../Stores/mediosPago';
import { useCategoriasStore } from '../../Stores/categorias';
import { useConfigStore } from '../../Stores/config';
import { useConceptosFrecuentesStore } from '../../Stores/conceptosFrecuentes';

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

const showSugerencias = ref(false);
const sugerencias = computed(() => conceptosStore.sugerencias);

const form = ref({
    fecha: new Date().toISOString().split('T')[0],
    medio_pago_id: '',
    categoria_id: '',
    concepto: '',
    valor: '',
    tipo: ''
});

// Cargar datos iniciales
onMounted(async () => {
    if (mediosPagoStore.mediosPago.length === 0) {
        await mediosPagoStore.cargarMediosPago(true);
    }
    if (categoriasStore.categorias.length === 0) {
        await categoriasStore.cargarCategorias(true);
    }

    // Si hay gasto para editar, cargar datos
    if (props.gasto) {
        form.value = { ...props.gasto };
    }
});

const mediosPagoOptions = computed(() =>
    mediosPagoStore.activos.map(mp => ({ value: mp.id, label: mp.nombre }))
);

const categoriasOptions = computed(() => [
    { value: '', label: 'Sin categoría' },
    ...categoriasStore.activas.map(c => ({ value: c.id, label: c.nombre }))
]);

const tiposGasto = computed(() => configStore.tiposGasto);

const isValid = computed(() =>
    form.value.fecha &&
    form.value.medio_pago_id &&
    form.value.concepto &&
    form.value.valor > 0 &&
    form.value.tipo
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
    }
    if (concepto.tipo) {
        form.value.tipo = concepto.tipo;
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
