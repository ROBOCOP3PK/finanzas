import { defineStore } from 'pinia';
import api from '../axios';

export const useConceptosFrecuentesStore = defineStore('conceptosFrecuentes', {
    state: () => ({
        conceptos: [],
        sugerencias: [],
        loading: false
    }),

    getters: {
        favoritos: (state) => state.conceptos.filter(c => c.es_favorito)
    },

    actions: {
        async cargarConceptos(soloFavoritos = false, limite = 10) {
            this.loading = true;
            try {
                const params = { limite };
                if (soloFavoritos) params.favoritos = true;

                const response = await api.get('/conceptos-frecuentes', { params });
                this.conceptos = response.data.data;
            } catch (error) {
                console.error('Error cargando conceptos:', error);
            } finally {
                this.loading = false;
            }
        },

        async buscar(texto) {
            if (!texto || texto.length < 1) {
                this.sugerencias = [];
                return;
            }

            try {
                const response = await api.get('/conceptos-frecuentes/buscar', {
                    params: { q: texto }
                });
                this.sugerencias = response.data.data;
            } catch (error) {
                console.error('Error buscando conceptos:', error);
                this.sugerencias = [];
            }
        },

        async toggleFavorito(id, esFavorito) {
            const response = await api.put(`/conceptos-frecuentes/${id}/favorito`, {
                es_favorito: esFavorito
            });

            const index = this.conceptos.findIndex(c => c.id === id);
            if (index !== -1) {
                this.conceptos[index] = response.data.data;
            }

            return response.data.data;
        },

        async eliminar(id) {
            await api.delete(`/conceptos-frecuentes/${id}`);
            this.conceptos = this.conceptos.filter(c => c.id !== id);
        },

        limpiarSugerencias() {
            this.sugerencias = [];
        }
    }
});
