import { defineStore } from 'pinia';
import api from '../axios';

export const useMediosPagoStore = defineStore('mediosPago', {
    state: () => ({
        mediosPago: [],
        loading: false,
        error: null
    }),

    getters: {
        activos: (state) => state.mediosPago.filter(mp => mp.activo),
        todos: (state) => state.mediosPago,
        porId: (state) => (id) => state.mediosPago.find(mp => mp.id === id)
    },

    actions: {
        async cargarMediosPago(soloActivos = false) {
            this.loading = true;
            this.error = null;
            try {
                const params = soloActivos ? { activos: true } : {};
                const response = await api.get('/medios-pago', { params });
                this.mediosPago = response.data.data;
            } catch (error) {
                this.error = 'Error cargando medios de pago';
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        async crearMedioPago(data) {
            const response = await api.post('/medios-pago', data);
            this.mediosPago.push(response.data.data);
            return response.data.data;
        },

        async actualizarMedioPago(id, data) {
            const response = await api.put(`/medios-pago/${id}`, data);
            const index = this.mediosPago.findIndex(mp => mp.id === id);
            if (index !== -1) {
                this.mediosPago[index] = response.data.data;
            }
            return response.data.data;
        },

        async eliminarMedioPago(id) {
            await api.delete(`/medios-pago/${id}`);
            this.mediosPago = this.mediosPago.filter(mp => mp.id !== id);
        },

        async reordenar(orden) {
            await api.put('/medios-pago/reordenar', { orden });
            await this.cargarMediosPago();
        }
    }
});
