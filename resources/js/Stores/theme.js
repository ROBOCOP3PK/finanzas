import { defineStore } from 'pinia';
import api from '../axios';

export const useThemeStore = defineStore('theme', {
    state: () => ({
        tema: 'system' // 'light' | 'dark' | 'system'
    }),

    getters: {
        temaActivo: (state) => {
            if (state.tema === 'system') {
                return window.matchMedia('(prefers-color-scheme: dark)').matches
                    ? 'dark'
                    : 'light';
            }
            return state.tema;
        }
    },

    actions: {
        setTema(tema) {
            this.tema = tema;
            localStorage.setItem('tema', tema);
            this.aplicarTema();

            // Guardar en servidor
            api.put('/configuracion', { tema }).catch(console.error);
        },

        aplicarTema() {
            const html = document.documentElement;
            if (this.temaActivo === 'dark') {
                html.classList.add('dark');
            } else {
                html.classList.remove('dark');
            }
        },

        inicializar() {
            // Primero intentar desde localStorage
            const temaLocal = localStorage.getItem('tema');
            if (temaLocal) {
                this.tema = temaLocal;
            }

            this.aplicarTema();

            // Escuchar cambios en preferencia del sistema
            window.matchMedia('(prefers-color-scheme: dark)')
                .addEventListener('change', () => {
                    if (this.tema === 'system') {
                        this.aplicarTema();
                    }
                });
        }
    }
});
