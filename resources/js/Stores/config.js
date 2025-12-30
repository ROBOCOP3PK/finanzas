import { defineStore } from 'pinia';
import axios from '../axios';

export const useConfigStore = defineStore('config', {
    state: () => ({
        loaded: false,
        divisa: 'COP',
        formato_divisa: 'punto',
        nombre_persona_1: 'Yo',
        nombre_persona_2: 'Pareja',
        porcentaje_persona_1: 50,
        porcentaje_persona_2: 50
    }),

    getters: {
        tiposGasto: (state) => [
            { value: 'personal', label: 'Mío' },
            { value: 'pareja', label: state.nombre_persona_2 },
            { value: 'compartido', label: `${state.porcentaje_persona_1}/${state.porcentaje_persona_2}` }
        ],

        getNombreTipo: (state) => (tipo) => {
            const tipos = {
                'personal': 'Personal',
                'pareja': state.nombre_persona_2,
                'compartido': `Compartido (${state.porcentaje_persona_1}/${state.porcentaje_persona_2})`
            };
            return tipos[tipo] || tipo;
        },

        formatoDivisa: (state) => state.formato_divisa,

        divisasDisponibles: () => [
            { value: 'COP', label: 'COP - Peso Colombiano' },
            { value: 'USD', label: 'USD - Dolar Estadounidense' },
            { value: 'EUR', label: 'EUR - Euro' },
            { value: 'MXN', label: 'MXN - Peso Mexicano' }
        ],

        formatosDivisaDisponibles: () => [
            { value: 'punto', label: 'Punto (1.234.567)' },
            { value: 'coma', label: 'Coma (1,234,567)' }
        ]
    },

    actions: {
        async cargarConfiguracion() {
            try {
                const { data } = await axios.get('/configuracion');
                if (data.success && data.data) {
                    this.divisa = data.data.divisa || 'COP';
                    this.formato_divisa = data.data.formato_divisa || 'punto';
                    this.nombre_persona_1 = data.data.nombre_persona_1 || 'Yo';
                    this.nombre_persona_2 = data.data.nombre_persona_2 || 'Pareja';
                    this.porcentaje_persona_1 = parseFloat(data.data.porcentaje_persona_1) || 50;
                    this.porcentaje_persona_2 = parseFloat(data.data.porcentaje_persona_2) || 50;
                }
                this.loaded = true;
            } catch (error) {
                console.error('Error cargando configuracion:', error);
                this.loaded = true;
            }
        },

        async actualizarDivisa(divisa) {
            try {
                const { data } = await axios.put('/configuracion', { divisa });
                if (data.success) {
                    this.divisa = divisa;
                }
                return data;
            } catch (error) {
                throw error;
            }
        },

        async actualizarFormatoDivisa(formato_divisa) {
            try {
                const { data } = await axios.put('/configuracion', { formato_divisa });
                if (data.success) {
                    this.formato_divisa = formato_divisa;
                }
                return data;
            } catch (error) {
                throw error;
            }
        },

        async actualizarGastosCompartidos(config) {
            try {
                const { data } = await axios.put('/configuracion', {
                    nombre_persona_1: config.nombre_persona_1,
                    nombre_persona_2: config.nombre_persona_2,
                    porcentaje_persona_1: config.porcentaje_persona_1,
                    porcentaje_persona_2: config.porcentaje_persona_2
                });
                if (data.success) {
                    this.nombre_persona_1 = config.nombre_persona_1;
                    this.nombre_persona_2 = config.nombre_persona_2;
                    this.porcentaje_persona_1 = config.porcentaje_persona_1;
                    this.porcentaje_persona_2 = config.porcentaje_persona_2;
                }
                return data;
            } catch (error) {
                throw error;
            }
        }
    }
});
