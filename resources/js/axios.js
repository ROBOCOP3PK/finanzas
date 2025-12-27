import axios from 'axios';

const TOKEN_KEY = 'finanzas_auth_token';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
});

// Request interceptor - añadir token a cada petición
api.interceptors.request.use(
    config => {
        const token = localStorage.getItem(TOKEN_KEY);
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    error => Promise.reject(error)
);

// Response interceptor - manejar errores y 401
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            // Token inválido o expirado - limpiar y redirigir a login
            localStorage.removeItem(TOKEN_KEY);
            localStorage.removeItem('finanzas_auth_user');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

export default api;
