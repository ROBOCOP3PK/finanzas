import { defineStore } from 'pinia';
import axios from '../axios';

export const useConfigStore = defineStore('config', {
    state: () => ({
        loaded: false,
        divisa: 'COP',
        formato_divisa: 'punto'
    }),

    getters: {
        tiposGasto: () => [
            { value: 'personal', label: 'Personal (mío)' },
            { value: 'pareja', label: 'Pareja (100%)' },
            { value: 'compartido', label: 'Compartido' }
        ],

        getNombreTipo: () => (tipo) => {
            const tipos = {
                'personal': 'Personal',
                'pareja': 'Pareja',
                'compartido': 'Compartido'
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
                const { data } = await axios.get('/api/configuracion');
                if (data.success && data.data) {
                    this.divisa = data.data.divisa || 'COP';
                    this.formato_divisa = data.data.formato_divisa || 'punto';
                }
                this.loaded = true;
            } catch (error) {
                console.error('Error cargando configuracion:', error);
                this.loaded = true;
            }
        },

        async actualizarDivisa(divisa) {
            try {
                const { data } = await axios.put('/api/configuracion', { divisa });
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
                const { data } = await axios.put('/api/configuracion', { formato_divisa });
                if (data.success) {
                    this.formato_divisa = formato_divisa;
                }
                return data;
            } catch (error) {
                throw error;
            }
        }
    }
});
