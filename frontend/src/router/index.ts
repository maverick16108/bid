import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: { layout: 'AuthLayout' }
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../views/DashboardView.vue'),
      meta: { layout: 'AppLayout', requiresAuth: true }
    },
    {
      path: '/orders',
      name: 'orders',
      component: () => import('../views/OrdersListView.vue'),
      meta: { layout: 'AppLayout', requiresAuth: true }
    },
    {
      path: '/create-order',
      name: 'create-order',
      component: () => import('../views/CreateOrderView.vue'),
      meta: { layout: 'AppLayout', requiresAuth: true }
    },
    {
      path: '/create-order/armature',
      name: 'create-order-armature',
      component: () => import('../views/orders/CreateArmatureOrder.vue'),
      meta: { layout: 'AppLayout', requiresAuth: true }
    },
    {
      path: '/create-order/transport',
      name: 'create-order-transport',
      component: () => import('../views/orders/CreateTransportOrder.vue'),
      meta: { layout: 'AppLayout', requiresAuth: true }
    },
  ]
})

export default router
