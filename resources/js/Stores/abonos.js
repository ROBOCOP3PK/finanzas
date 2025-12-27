import { defineStore } from 'pinia';
import api from '../axios';

export const useAbonosStore = defineStore('abonos', {
    state: () => ({
        abonos: [],
        loading: false,
        error: null,
        meta: {
            current_page: 1,
            last_page: 1,
            per_page: 20,
            total: 0
        },
        filtros: {
            desde: null,
            hasta: null
        }
    }),

    actions: {
        async cargarAbonos(page = 1) {
            this.loading = true;
            this.error = null;
            try {
                const params = {
                    page,
                    ...this.filtros
                };

                Object.keys(params).forEach(key => {
                    if (params[key] === null || params[key] === '') {
                        delete params[key];
                    }
                });

                const response = await api.get('/abonos', { params });
                this.abonos = response.data.data;
                this.meta = response.data.meta;
            } catch (error) {
                this.error = 'Error cargando abonos';
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        async crearAbono(data) {
            const response = await api.post('/abonos', data);
            return response.data.data;
        },

        async obtenerAbono(id) {
            const response = await api.get(`/abonos/${id}`);
            return response.data.data;
        },

        async actualizarAbono(id, data) {
            const response = await api.put(`/abonos/${id}`, data);
            const index = this.abonos.findIndex(a => a.id === id);
            if (index !== -1) {
                this.abonos[index] = response.data.data;
            }
            return response.data.data;
        },

        async eliminarAbono(id) {
            await api.delete(`/abonos/${id}`);
            this.abonos = this.abonos.filter(a => a.id !== id);
        },

        setFiltros(filtros) {
            this.filtros = { ...this.filtros, ...filtros };
        }
    }
});
