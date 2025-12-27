<template>
    <div class="p-4 space-y-4">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Configuración</h1>

        <!-- Tema -->
        <Card title="Apariencia">
            <div class="flex gap-2">
                <button
                    v-for="tema in temas"
                    :key="tema.value"
                    @click="cambiarTema(tema.value)"
                    :class="[
                        'flex-1 py-3 px-4 rounded-lg font-medium text-sm transition-colors',
                        themeStore.tema === tema.value
                            ? 'bg-primary text-white dark:bg-indigo-500'
                            : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                    ]"
                >
                    {{ tema.label }}
                </button>
            </div>
        </Card>

        <!-- Personas y Porcentajes -->
        <Card title="Personas y Porcentajes">
            <div class="space-y-4">
                <Input
                    v-model="config.nombre_persona_1"
                    label="Nombre Persona 1"
                />
                <Input
                    v-model="config.nombre_persona_2"
                    label="Nombre Persona 2"
                />
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Porcentaje gastos de casa
                    </label>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <input
                                type="range"
                                v-model="config.porcentaje_persona_1"
                                min="0"
                                max="100"
                                class="w-full"
                            />
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 w-24 text-right">
                            {{ config.porcentaje_persona_1 }}% / {{ 100 - config.porcentaje_persona_1 }}%
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ config.nombre_persona_1 }}: {{ config.porcentaje_persona_1 }}% |
                        {{ config.nombre_persona_2 }}: {{ 100 - config.porcentaje_persona_1 }}%
                    </p>
                </div>
                <Button @click="guardarConfig" :loading="guardando" class="w-full">
                    Guardar Cambios
                </Button>
            </div>
        </Card>

        <!-- Medios de Pago -->
        <Card title="Medios de Pago">
            <div class="space-y-2">
                <div
                    v-for="mp in mediosPagoStore.mediosPago"
                    :key="mp.id"
                    class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0"
                >
                    <div class="flex items-center gap-3">
                        <span :class="['w-2 h-2 rounded-full', mp.activo ? 'bg-green-500' : 'bg-gray-400']"></span>
                        <span class="text-gray-900 dark:text-white">{{ mp.nombre }}</span>
                    </div>
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="toggleMedioPago(mp)"
                    >
                        {{ mp.activo ? 'Desactivar' : 'Activar' }}
                    </Button>
                </div>
            </div>
        </Card>

        <!-- Categorías -->
        <Card title="Categorías">
            <div class="space-y-2">
                <div
                    v-for="cat in categoriasStore.categorias"
                    :key="cat.id"
                    class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="w-4 h-4 rounded"
                            :style="{ backgroundColor: cat.color }"
                        ></span>
                        <span class="text-gray-900 dark:text-white">{{ cat.nombre }}</span>
                    </div>
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="toggleCategoria(cat)"
                    >
                        {{ cat.activo ? 'Desactivar' : 'Activar' }}
                    </Button>
                </div>
            </div>
        </Card>

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
import Card from '../Components/UI/Card.vue';
import Input from '../Components/UI/Input.vue';
import Button from '../Components/UI/Button.vue';
import Toast from '../Components/UI/Toast.vue';
import { useThemeStore } from '../Stores/theme';
import { useConfigStore } from '../Stores/config';
import { useMediosPagoStore } from '../Stores/mediosPago';
import { useCategoriasStore } from '../Stores/categorias';

const themeStore = useThemeStore();
const configStore = useConfigStore();
const mediosPagoStore = useMediosPagoStore();
const categoriasStore = useCategoriasStore();

const temas = [
    { value: 'light', label: 'Claro' },
    { value: 'dark', label: 'Oscuro' },
    { value: 'system', label: 'Sistema' }
];

const config = reactive({
    nombre_persona_1: '',
    nombre_persona_2: '',
    porcentaje_persona_1: 50
});

const guardando = ref(false);
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

onMounted(async () => {
    await Promise.all([
        configStore.cargarConfiguracion(),
        mediosPagoStore.cargarMediosPago(),
        categoriasStore.cargarCategorias()
    ]);

    config.nombre_persona_1 = configStore.nombre_persona_1;
    config.nombre_persona_2 = configStore.nombre_persona_2;
    config.porcentaje_persona_1 = configStore.porcentaje_persona_1;
});

const cambiarTema = (tema) => {
    themeStore.setTema(tema);
};

const guardarConfig = async () => {
    guardando.value = true;
    try {
        await configStore.guardarConfiguracion({
            nombre_persona_1: config.nombre_persona_1,
            nombre_persona_2: config.nombre_persona_2,
            porcentaje_persona_1: parseInt(config.porcentaje_persona_1),
            porcentaje_persona_2: 100 - parseInt(config.porcentaje_persona_1)
        });
        toastMessage.value = 'Configuración guardada';
        toastType.value = 'success';
        showToast.value = true;
    } catch (error) {
        toastMessage.value = error.response?.data?.message || 'Error al guardar';
        toastType.value = 'error';
        showToast.value = true;
    } finally {
        guardando.value = false;
    }
};

const toggleMedioPago = async (mp) => {
    try {
        await mediosPagoStore.actualizarMedioPago(mp.id, { ...mp, activo: !mp.activo });
    } catch (error) {
        toastMessage.value = 'Error al actualizar';
        toastType.value = 'error';
        showToast.value = true;
    }
};

const toggleCategoria = async (cat) => {
    try {
        await categoriasStore.actualizarCategoria(cat.id, { ...cat, activo: !cat.activo });
    } catch (error) {
        toastMessage.value = 'Error al actualizar';
        toastType.value = 'error';
        showToast.value = true;
    }
};
</script>
