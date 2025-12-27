import { defineStore } from 'pinia';
import api from '../axios';

export const useConfigStore = defineStore('config', {
    state: () => ({
        nombre_persona_1: 'Persona 1',
        nombre_persona_2: 'Persona 2',
        porcentaje_persona_1: 50,
        porcentaje_persona_2: 50,
        tema: 'system',
        loaded: false,
        loading: false
    }),

    getters: {
        tiposGasto: (state) => [
            { value: 'persona_1', label: state.nombre_persona_1 },
            { value: 'persona_2', label: state.nombre_persona_2 },
            { value: 'casa', label: 'Casa' }
        ],

        getNombreTipo: (state) => (tipo) => {
            const tipos = {
                'persona_1': state.nombre_persona_1,
                'persona_2': state.nombre_persona_2,
                'casa': 'Casa'
            };
            return tipos[tipo] || tipo;
        }
    },

    actions: {
        async cargarConfiguracion() {
            if (this.loading) return;

            this.loading = true;
            try {
                const response = await api.get('/configuracion');
                const data = response.data.data;

                this.nombre_persona_1 = data.nombre_persona_1 || 'Persona 1';
                this.nombre_persona_2 = data.nombre_persona_2 || 'Persona 2';
                this.porcentaje_persona_1 = parseInt(data.porcentaje_persona_1) || 50;
                this.porcentaje_persona_2 = parseInt(data.porcentaje_persona_2) || 50;
                this.tema = data.tema || 'system';
                this.loaded = true;
            } catch (error) {
                console.error('Error cargando configuración:', error);
            } finally {
                this.loading = false;
            }
        },

        async guardarConfiguracion(data) {
            try {
                const response = await api.put('/configuracion', data);
                const newData = response.data.data;

                if (newData.nombre_persona_1) this.nombre_persona_1 = newData.nombre_persona_1;
                if (newData.nombre_persona_2) this.nombre_persona_2 = newData.nombre_persona_2;
                if (newData.porcentaje_persona_1) this.porcentaje_persona_1 = parseInt(newData.porcentaje_persona_1);
                if (newData.porcentaje_persona_2) this.porcentaje_persona_2 = parseInt(newData.porcentaje_persona_2);
                if (newData.tema) this.tema = newData.tema;

                return response.data;
            } catch (error) {
                console.error('Error guardando configuración:', error);
                throw error;
            }
        }
    }
});
