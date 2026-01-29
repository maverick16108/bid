import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: { layout: 'AuthLayout', title: 'Новосталь-М | Портал управления заявками' }
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../views/DashboardView.vue'),
      meta: { layout: 'AppLayout', requiresAuth: true, title: 'Заявки | Новосталь-М' }
    },
    {
      path: '/orders',
      name: 'orders',
      component: () => import('../views/OrdersListView.vue'),
      meta: { layout: 'AppLayout', requiresAuth: true, title: 'Заявки | Новосталь-М' }
    },
    {
      path: '/create-order',
      name: 'create-order',
      component: () => import('../views/CreateOrderView.vue'),
      meta: { layout: 'AppLayout', requiresAuth: true, title: 'Новая заявка | Новосталь-М' }
    },
    {
      path: '/create-order/armature',
      name: 'create-order-armature',
      component: () => import('../views/orders/CreateArmatureOrder.vue'),
      meta: { layout: 'AppLayout', requiresAuth: true, title: 'Заказ арматуры | Новосталь-М' }
    },
    {
      path: '/create-order/transport',
      name: 'create-order-transport',
      component: () => import('../views/orders/CreateTransportOrder.vue'),
      meta: { layout: 'AppLayout', requiresAuth: true, title: 'Заказ транспорта | Новосталь-М' }
    },
    {
      path: '/admin/users',
      name: 'admin-users',
      component: () => import('../views/AdminUsersView.vue'),
      meta: { layout: 'AppLayout', requiresAuth: true, title: 'Панель администрирования | Новосталь-М' }
    },
  ]
})

router.beforeEach((to, _from, next) => {
  document.title = (to.meta.title as string) || 'Новосталь-М Заявки'
  next()
})

export default router

