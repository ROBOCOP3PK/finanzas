import { defineStore } from 'pinia';

export const useConfigStore = defineStore('config', {
    state: () => ({
        loaded: true
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
        }
    }
});
