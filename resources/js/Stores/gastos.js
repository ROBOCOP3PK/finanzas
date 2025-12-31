import { defineStore } from 'pinia';
import api from '../axios';

export const useGastosStore = defineStore('gastos', {
    state: () => ({
        gastos: [],
        loading: false,
        loadingMore: false,
        error: null,
        meta: {
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0
        },
        filtros: {
            desde: null,
            hasta: null,
            tipos: null, // Array para selección múltiple
            categorias: null, // Array para selección múltiple
            medio_pago_id: null
        }
    }),

    getters: {
        hayMas: (state) => state.meta.current_page < state.meta.last_page
    },

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

        async cargarMas() {
            if (!this.hayMas || this.loadingMore) return;

            this.loadingMore = true;
            try {
                const params = {
                    page: this.meta.current_page + 1,
                    ...this.filtros
                };

                // Remove null values
                Object.keys(params).forEach(key => {
                    if (params[key] === null || params[key] === '') {
                        delete params[key];
                    }
                });

                const response = await api.get('/gastos', { params });
                this.gastos = [...this.gastos, ...response.data.data];
                this.meta = response.data.meta;
            } catch (error) {
                this.error = 'Error cargando más gastos';
                console.error(error);
            } finally {
                this.loadingMore = false;
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
                tipos: null,
                categorias: null,
                medio_pago_id: null
            };
        },

        async obtenerTodosGastos(filtros = {}) {
            const params = { ...filtros, per_page: 1000 };

            // Remove null/empty values
            Object.keys(params).forEach(key => {
                if (params[key] === null || params[key] === '') {
                    delete params[key];
                }
            });

            const response = await api.get('/gastos', { params });
            return response.data.data;
        },

        async exportarGastos(filtrosExport) {
            const params = new URLSearchParams();

            if (filtrosExport.exportar_todos) {
                params.append('exportar_todos', '1');
            } else {
                if (filtrosExport.desde) params.append('desde', filtrosExport.desde);
                if (filtrosExport.hasta) params.append('hasta', filtrosExport.hasta);
            }

            if (filtrosExport.tipo) params.append('tipo', filtrosExport.tipo);
            if (filtrosExport.categoria_id) params.append('categoria_id', filtrosExport.categoria_id);

            const response = await api.get('/gastos/exportar', {
                params,
                responseType: 'blob'
            });

            // Crear descarga
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;

            // Obtener nombre del archivo del header o usar uno por defecto
            const contentDisposition = response.headers['content-disposition'];
            let filename = 'gastos.csv';
            if (contentDisposition) {
                const match = contentDisposition.match(/filename="(.+)"/);
                if (match) filename = match[1];
            }

            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
        }
    }
});
