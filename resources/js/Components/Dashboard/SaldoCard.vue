<template>
    <Card class="bg-gradient-to-br from-primary to-primary-dark dark:from-indigo-600 dark:to-indigo-800 text-white">
        <div class="text-center">
            <p class="text-sm opacity-90 mb-1">Saldo Pendiente</p>
            <p class="text-xs opacity-75 mb-2">{{ nombrePersona }}</p>
            <p class="text-3xl font-bold">{{ saldoFormateado }}</p>
        </div>
    </Card>
</template>

<script setup>
import { computed } from 'vue';
import Card from '../UI/Card.vue';

const props = defineProps({
    saldo: {
        type: Number,
        required: true
    },
    nombrePersona: {
        type: String,
        default: 'Persona 1'
    }
});

const saldoFormateado = computed(() => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(props.saldo);
});
</script>
