<template>
    <div class="p-4 max-w-full overflow-hidden">
        <Card>
            <GastoForm
                ref="gastoFormRef"
                :loading="loading"
                :errors="errors"
                submitText="Guardar Gasto"
                @submit="guardar"
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
import { ref } from 'vue';
import Card from '../../Components/UI/Card.vue';
import Toast from '../../Components/UI/Toast.vue';
import GastoForm from '../../Components/Gastos/GastoForm.vue';
import { useGastosStore } from '../../Stores/gastos';

const gastosStore = useGastosStore();

const gastoFormRef = ref(null);
const loading = ref(false);
const errors = ref({});
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

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
