<template>
    <form @submit.prevent="submit" class="space-y-4">
        <Input
            v-model="form.fecha"
            type="date"
            label="Fecha"
            required
            :error="errors.fecha"
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

const form = ref({
    fecha: new Date().toISOString().split('T')[0],
    valor: '',
    nota: ''
});

onMounted(() => {
    if (props.abono) {
        form.value = { ...props.abono };
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
