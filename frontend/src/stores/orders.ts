import { defineStore } from 'pinia';
import api from '@/services/api';

export const useOrdersStore = defineStore('orders', {
    state: () => ({
        orders: [] as any[],
        loading: false,
    }),
    actions: {
        async fetchOrders() {
            this.loading = true;
            try {
                const response = await api.get('/orders');
                this.orders = response.data;
            } finally {
                this.loading = false;
            }
        },
        async createOrder(orderData: { type: string, details: any, comment?: string }) {
            this.loading = true;
            try {
                const response = await api.post('/orders', orderData);
                this.orders.unshift(response.data);
                return response.data;
            } finally {
                this.loading = false;
            }
        }
    },
});
