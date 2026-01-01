import { defineStore } from 'pinia';
import api from '../axios';

export const useGastosRecurrentesStore = defineStore('gastosRecurrentes', {
    state: () => ({
        gastosRecurrentes: [],
        pendientes: [],
        loading: false,
        error: null
    }),

    getters: {
        activos: (state) => state.gastosRecurrentes.filter(gr => gr.activo),
        cantidadPendientes: (state) => state.pendientes.length
    },

    actions: {
        async cargarGastosRecurrentes(soloActivos = false) {
            this.loading = true;
            try {
                const params = soloActivos ? { activos: true } : {};
                const response = await api.get('/gastos-recurrentes', { params });
                this.gastosRecurrentes = response.data.data;
            } catch (error) {
                this.error = 'Error cargando gastos recurrentes';
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        async cargarPendientes() {
            try {
                const response = await api.get('/gastos-recurrentes/pendientes');
                this.pendientes = response.data.data;
            } catch (error) {
                console.error('Error cargando pendientes:', error);
            }
        },

        async crearGastoRecurrente(data) {
            const response = await api.post('/gastos-recurrentes', data);
            this.gastosRecurrentes.push(response.data.data);
            return response.data.data;
        },

        async actualizarGastoRecurrente(id, data) {
            const response = await api.put(`/gastos-recurrentes/${id}`, data);
            const index = this.gastosRecurrentes.findIndex(gr => gr.id === id);
            if (index !== -1) {
                this.gastosRecurrentes[index] = response.data.data;
            }
            return response.data.data;
        },

        async eliminarGastoRecurrente(id) {
            await api.delete(`/gastos-recurrentes/${id}`);
            this.gastosRecurrentes = this.gastosRecurrentes.filter(gr => gr.id !== id);
            this.pendientes = this.pendientes.filter(gr => gr.id !== id);
        },

        async registrar(id) {
            const response = await api.post(`/gastos-recurrentes/${id}/registrar`);
            this.pendientes = this.pendientes.filter(gr => gr.id !== id);

            // Actualizar ultimo_registro en la lista principal
            const index = this.gastosRecurrentes.findIndex(gr => gr.id === id);
            if (index !== -1) {
                this.gastosRecurrentes[index].ultimo_registro = new Date().toLocaleDateString('sv-SE');
            }

            return response.data.data;
        },

        async registrarTodosPendientes() {
            const response = await api.post('/gastos-recurrentes/registrar-pendientes');
            this.pendientes = [];
            await this.cargarGastosRecurrentes();
            return response.data.data;
        }
    }
});
