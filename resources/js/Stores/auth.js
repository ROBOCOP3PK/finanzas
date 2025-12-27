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
        // Estado para verificacion de email
        verificationEmail: null,
        isEmailVerified: false,
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

                this.user = user;
                this.token = token;

                localStorage.setItem(TOKEN_KEY, token);
                localStorage.setItem(USER_KEY, JSON.stringify(user));

                this.setAxiosToken(token);

                return { success: true };
            } catch (error) {
                this.error = error.response?.data?.message ||
                             error.response?.data?.errors?.email?.[0] ||
                             'Error al iniciar sesion';
                return { success: false, error: this.error };
            } finally {
                this.loading = false;
            }
        },

        // Paso 1: Enviar codigo de verificacion
        async sendVerificationCode(email) {
            this.loading = true;
            this.error = null;

            try {
                await axios.post('/api/auth/send-code', { email });

                this.verificationEmail = email;
                this.isEmailVerified = false;

                return { success: true };
            } catch (error) {
                this.error = error.response?.data?.message ||
                             error.response?.data?.errors?.email?.[0] ||
                             'Error al enviar el codigo';
                return { success: false, error: this.error };
            } finally {
                this.loading = false;
            }
        },

        // Paso 2: Verificar codigo
        async verifyCode(email, code) {
            this.loading = true;
            this.error = null;

            try {
                await axios.post('/api/auth/verify-code', { email, code });

                this.isEmailVerified = true;

                return { success: true };
            } catch (error) {
                this.error = error.response?.data?.message ||
                             error.response?.data?.errors?.code?.[0] ||
                             'Codigo invalido';
                return { success: false, error: this.error };
            } finally {
                this.loading = false;
            }
        },

        // Reenviar codigo
        async resendCode(email) {
            this.loading = true;
            this.error = null;

            try {
                await axios.post('/api/auth/resend-code', { email });
                return { success: true };
            } catch (error) {
                this.error = error.response?.data?.message ||
                             'Error al reenviar el codigo';
                return { success: false, error: this.error };
            } finally {
                this.loading = false;
            }
        },

        // Paso 3: Completar registro
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

                // Limpiar estado de verificacion
                this.verificationEmail = null;
                this.isEmailVerified = false;

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
                console.warn('Error en logout:', error);
            } finally {
                this.clearAuth();
            }
        },

        // Limpiar autenticacion
        clearAuth() {
            this.user = null;
            this.token = null;
            this.error = null;
            this.verificationEmail = null;
            this.isEmailVerified = false;

            localStorage.removeItem(TOKEN_KEY);
            localStorage.removeItem(USER_KEY);

            this.setAxiosToken(null);
        },

        // Verificar token
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
                this.clearAuth();
                return false;
            }
        },

        // Limpiar error
        clearError() {
            this.error = null;
        },

        // Reset estado de verificacion (para volver atras)
        resetVerification() {
            this.verificationEmail = null;
            this.isEmailVerified = false;
            this.error = null;
        },
    },
});
