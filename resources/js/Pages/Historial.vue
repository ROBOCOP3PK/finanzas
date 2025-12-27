<template>
    <div class="p-4">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Historial</h1>

        <!-- Filtros -->
        <Card class="mb-4">
            <div class="grid grid-cols-2 gap-3">
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
import Card from '../Components/UI/Card.vue';
import Input from '../Components/UI/Input.vue';
import Select from '../Components/UI/Select.vue';
import Button from '../Components/UI/Button.vue';
import GastoItem from '../Components/Gastos/GastoItem.vue';
import { useGastosStore } from '../Stores/gastos';
import { useCategoriasStore } from '../Stores/categorias';
import { useConfigStore } from '../Stores/config';

const router = useRouter();
const gastosStore = useGastosStore();
const categoriasStore = useCategoriasStore();
const configStore = useConfigStore();

const filtros = ref({
    desde: '',
    hasta: '',
    tipo: '',
    categoria_id: ''
});

onMounted(async () => {
    await Promise.all([
        gastosStore.cargarGastos(),
        categoriasStore.cargarCategorias(true),
        configStore.cargarConfiguracion()
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
</script>
