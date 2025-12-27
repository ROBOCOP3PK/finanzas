import { defineStore } from 'pinia';
import api from '../axios';

export const useCategoriasStore = defineStore('categorias', {
    state: () => ({
        categorias: [],
        loading: false,
        error: null
    }),

    getters: {
        activas: (state) => state.categorias.filter(c => c.activo),
        todas: (state) => state.categorias,
        porId: (state) => (id) => state.categorias.find(c => c.id === id)
    },

    actions: {
        async cargarCategorias(soloActivas = false) {
            this.loading = true;
            this.error = null;
            try {
                const params = soloActivas ? { activas: true } : {};
                const response = await api.get('/categorias', { params });
                this.categorias = response.data.data;
            } catch (error) {
                this.error = 'Error cargando categorías';
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        async crearCategoria(data) {
            const response = await api.post('/categorias', data);
            this.categorias.push(response.data.data);
            return response.data.data;
        },

        async actualizarCategoria(id, data) {
            const response = await api.put(`/categorias/${id}`, data);
            const index = this.categorias.findIndex(c => c.id === id);
            if (index !== -1) {
                this.categorias[index] = response.data.data;
            }
            return response.data.data;
        },

        async eliminarCategoria(id) {
            await api.delete(`/categorias/${id}`);
            this.categorias = this.categorias.filter(c => c.id !== id);
        },

        async reordenar(orden) {
            await api.put('/categorias/reordenar', { orden });
            await this.cargarCategorias();
        }
    }
});
