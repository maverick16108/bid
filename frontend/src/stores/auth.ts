import { defineStore } from 'pinia';
import api from '@/services/api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null as any,
        token: localStorage.getItem('token') || null,
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
    },
    actions: {
        async login(phone: string) {
            try {
                const response = await api.post('/login', { phone });
                const { access_token, user } = response.data;
                
                this.token = access_token;
                this.user = user;
                localStorage.setItem('token', access_token);
                
                return true;
            } catch (error) {
                console.error('Login failed', error);
                throw error;
            }
        },
        async logout() {
            try {
                await api.post('/logout');
            } finally {
                this.token = null;
                this.user = null;
                localStorage.removeItem('token');
            }
        },
        async fetchUser() {
            if (!this.token) return;
            try {
                const response = await api.get('/me');
                this.user = response.data;
            } catch (error) {
                this.logout();
            }
        }
    },
});
