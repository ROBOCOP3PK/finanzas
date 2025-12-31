<template>
    <div class="p-4 max-w-full overflow-hidden">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Gastos</h1>
            <router-link to="/gastos/nuevo">
                <Button size="sm">
                    <PlusIcon class="w-4 h-4 mr-1" />
                    Nuevo
                </Button>
            </router-link>
        </div>

        <GastosSkeleton v-if="gastosStore.loading" />

        <div v-else-if="gastosStore.gastos.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            No hay gastos registrados
        </div>

        <div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <GastoItem
                v-for="gasto in gastosStore.gastos"
                :key="gasto.id"
                :gasto="gasto"
                @click="editarGasto(gasto.id)"
                class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 px-4"
            />
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
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { PlusIcon } from '@heroicons/vue/24/outline';
import Button from '../../Components/UI/Button.vue';
import GastoItem from '../../Components/Gastos/GastoItem.vue';
import GastosSkeleton from '../../Components/Gastos/GastosSkeleton.vue';
import { useGastosStore } from '../../Stores/gastos';

const router = useRouter();
const gastosStore = useGastosStore();

onMounted(() => {
    gastosStore.cargarGastos();
});

const cargarPagina = (page) => {
    gastosStore.cargarGastos(page);
};

const editarGasto = (id) => {
    router.push(`/gastos/${id}/editar`);
};
</script>
