<template>
    <div class="p-4 max-w-full overflow-hidden">
        <Card>
            <GastoForm
                ref="gastoFormRef"
                :loading="loading"
                :errors="errors"
                :seccion-inicial="seccionInicial"
                submitText="Guardar Gasto"
                @submit="guardar"
                @referencia-copiada="mostrarReferenciCopiada"
            />
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
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import Card from '../../Components/UI/Card.vue';
import Toast from '../../Components/UI/Toast.vue';
import GastoForm from '../../Components/Gastos/GastoForm.vue';
import { useGastosStore } from '../../Stores/gastos';

const route = useRoute();
const gastosStore = useGastosStore();

const seccionInicial = computed(() => route.query.seccion || null);

const gastoFormRef = ref(null);
const loading = ref(false);
const errors = ref({});
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const mostrarReferenciCopiada = (referencia) => {
    toastMessage.value = `Referencia copiada: ${referencia}`;
    toastType.value = 'success';
    showToast.value = true;
};

const guardar = async (data) => {
    loading.value = true;
    errors.value = {};

    try {
        await gastosStore.crearGasto(data);
        toastMessage.value = 'Gasto guardado correctamente';
        toastType.value = 'success';
        showToast.value = true;

        // Limpiar formulario para agregar otro gasto
        gastoFormRef.value?.resetForm();
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            toastMessage.value = 'Error al guardar el gasto';
            toastType.value = 'error';
            showToast.value = true;
        }
    } finally {
        loading.value = false;
    }
};
</script>
