import { defineStore } from 'pinia';
import api from '../axios';

export const useGastosStore = defineStore('gastos', {
    state: () => ({
        gastos: [],
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
            hasta: null,
            tipo: null,
            medio_pago_id: null,
            categoria_id: null
        }
    }),

    actions: {
        async cargarGastos(page = 1) {
            this.loading = true;
            this.error = null;
            try {
                const params = {
                    page,
                    ...this.filtros
                };

                // Remove null values
                Object.keys(params).forEach(key => {
                    if (params[key] === null || params[key] === '') {
                        delete params[key];
                    }
                });

                const response = await api.get('/gastos', { params });
                this.gastos = response.data.data;
                this.meta = response.data.meta;
            } catch (error) {
                this.error = 'Error cargando gastos';
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        async crearGasto(data) {
            const response = await api.post('/gastos', data);
            return response.data.data;
        },

        async obtenerGasto(id) {
            const response = await api.get(`/gastos/${id}`);
            return response.data.data;
        },

        async actualizarGasto(id, data) {
            const response = await api.put(`/gastos/${id}`, data);
            const index = this.gastos.findIndex(g => g.id === id);
            if (index !== -1) {
                this.gastos[index] = response.data.data;
            }
            return response.data.data;
        },

        async eliminarGasto(id) {
            await api.delete(`/gastos/${id}`);
            this.gastos = this.gastos.filter(g => g.id !== id);
        },

        setFiltros(filtros) {
            this.filtros = { ...this.filtros, ...filtros };
        },

        limpiarFiltros() {
            this.filtros = {
                desde: null,
                hasta: null,
                tipo: null,
                medio_pago_id: null,
                categoria_id: null
            };
        }
    }
});
