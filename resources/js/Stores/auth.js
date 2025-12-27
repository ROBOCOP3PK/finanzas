import { defineStore } from 'pinia';
import axios from 'axios';

const TOKEN_KEY = 'finanzas_auth_token';
const USER_KEY = 'finanzas_auth_user';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem(USER_KEY)) || null,
        token: localStorage.getItem(TOKEN_KEY) || null,
        loading: false,
        error: null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token && !!state.user,
        userName: (state) => state.user?.name || '',
    },

    actions: {
        // Configurar token en axios
        setAxiosToken(token) {
            if (token) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            } else {
                delete axios.defaults.headers.common['Authorization'];
            }
        },

        // Inicializar auth desde localStorage
        init() {
            if (this.token) {
                this.setAxiosToken(this.token);
            }
        },

        // Login
        async login(email, password) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post('/api/auth/login', {
                    email,
                    password,
                    device_name: navigator.userAgent,
                });

                const { user, token } = response.data;

                // Guardar en state
                this.user = user;
                this.token = token;

                // Persistir en localStorage
                localStorage.setItem(TOKEN_KEY, token);
                localStorage.setItem(USER_KEY, JSON.stringify(user));

                // Configurar axios
                this.setAxiosToken(token);

                return { success: true };
            } catch (error) {
                this.error = error.response?.data?.message ||
                             error.response?.data?.errors?.email?.[0] ||
                             'Error al iniciar sesión';
                return { success: false, error: this.error };
            } finally {
                this.loading = false;
            }
        },

        // Registro
        async register(name, email, password, passwordConfirmation) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post('/api/auth/register', {
                    name,
                    email,
                    password,
                    password_confirmation: passwordConfirmation,
                    device_name: navigator.userAgent,
                });

                const { user, token } = response.data;

                this.user = user;
                this.token = token;

                localStorage.setItem(TOKEN_KEY, token);
                localStorage.setItem(USER_KEY, JSON.stringify(user));

                this.setAxiosToken(token);

                return { success: true };
            } catch (error) {
                const errors = error.response?.data?.errors;
                if (errors) {
                    this.error = Object.values(errors).flat()[0];
                } else {
                    this.error = error.response?.data?.message || 'Error al registrar';
                }
                return { success: false, error: this.error };
            } finally {
                this.loading = false;
            }
        },

        // Logout
        async logout() {
            try {
                if (this.token) {
                    await axios.post('/api/auth/logout');
                }
            } catch (error) {
                // Ignorar errores de logout (token inválido, etc.)
                console.warn('Error en logout:', error);
            } finally {
                this.clearAuth();
            }
        },

        // Limpiar autenticación
        clearAuth() {
            this.user = null;
            this.token = null;
            this.error = null;

            localStorage.removeItem(TOKEN_KEY);
            localStorage.removeItem(USER_KEY);

            this.setAxiosToken(null);
        },

        // Verificar token (obtener usuario actual)
        async checkAuth() {
            if (!this.token) {
                return false;
            }

            this.setAxiosToken(this.token);

            try {
                const response = await axios.get('/api/auth/me');
                this.user = response.data.user;
                localStorage.setItem(USER_KEY, JSON.stringify(this.user));
                return true;
            } catch (error) {
                // Token inválido o expirado
                this.clearAuth();
                return false;
            }
        },
    },
});
