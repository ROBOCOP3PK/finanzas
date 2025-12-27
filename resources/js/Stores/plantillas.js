import { defineStore } from 'pinia';
import api from '../axios';

export const usePlantillasStore = defineStore('plantillas', {
    state: () => ({
        plantillas: [],
        rapidas: [],
        loading: false,
        error: null
    }),

    getters: {
        activas: (state) => state.plantillas.filter(p => p.activo),
        porId: (state) => (id) => state.plantillas.find(p => p.id === id)
    },

    actions: {
        async cargarPlantillas(soloActivas = false) {
            this.loading = true;
            try {
                const params = soloActivas ? { activas: true } : {};
                const response = await api.get('/plantillas', { params });
                this.plantillas = response.data.data;
            } catch (error) {
                this.error = 'Error cargando plantillas';
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        async cargarRapidas() {
            try {
                const response = await api.get('/plantillas/rapidas');
                this.rapidas = response.data.data;
            } catch (error) {
                console.error('Error cargando plantillas rápidas:', error);
            }
        },

        async crearPlantilla(data) {
            const response = await api.post('/plantillas', data);
            this.plantillas.push(response.data.data);
            return response.data.data;
        },

        async actualizarPlantilla(id, data) {
            const response = await api.put(`/plantillas/${id}`, data);
            const index = this.plantillas.findIndex(p => p.id === id);
            if (index !== -1) {
                this.plantillas[index] = response.data.data;
            }
            return response.data.data;
        },

        async eliminarPlantilla(id) {
            await api.delete(`/plantillas/${id}`);
            this.plantillas = this.plantillas.filter(p => p.id !== id);
            this.rapidas = this.rapidas.filter(p => p.id !== id);
        },

        async usarPlantilla(id, fecha, valor = null) {
            const data = { fecha };
            if (valor !== null) data.valor = valor;

            const response = await api.post(`/plantillas/${id}/usar`, data);
            return response.data.data;
        },

        async reordenar(orden) {
            await api.put('/plantillas/reordenar', { orden });
            await this.cargarPlantillas();
        }
    }
});
