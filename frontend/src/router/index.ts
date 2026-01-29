import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import OrdersListView from '../views/OrdersListView.vue'
import CreateOrderView from '../views/CreateOrderView.vue'
import CreateArmatureOrder from '../views/orders/CreateArmatureOrder.vue'
import CreateTransportOrder from '../views/orders/CreateTransportOrder.vue'

// Admin Views
import AdminLoginView from '../views/AdminLoginView.vue'
import AdminDashboardView from '../views/AdminDashboardView.vue'
import AdminModeratorsView from '../views/AdminModeratorsView.vue'
import AdminUsersView from '../views/AdminUsersView.vue'
import AdminBidsView from '../views/AdminBidsView.vue'
import AdminLayout from '../layouts/AdminLayout.vue'

import { useAdminStore } from '@/stores/admin'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      component: LoginView,
      meta: { layout: 'AuthLayout', title: 'Новосталь-М | Портал управления заявками' }
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: DashboardView,
      meta: { layout: 'AppLayout', requiresAuth: true, title: 'Заявки | Новосталь-М' }
    },
    {
      path: '/orders',
      name: 'orders',
      component: OrdersListView,
      meta: { layout: 'AppLayout', requiresAuth: true, title: 'Заявки | Новосталь-М' }
    },
    {
      path: '/create-order',
      name: 'create-order',
      component: CreateOrderView,
      meta: { layout: 'AppLayout', requiresAuth: true, title: 'Новая заявка | Новосталь-М' }
    },
    {
      path: '/create-order/armature',
      name: 'create-order-armature',
      component: CreateArmatureOrder,
      meta: { layout: 'AppLayout', requiresAuth: true, title: 'Заказ арматуры | Новосталь-М' }
    },
    {
      path: '/create-order/transport',
      name: 'create-order-transport',
      component: CreateTransportOrder,
      meta: { layout: 'AppLayout', requiresAuth: true, title: 'Заказ транспорта | Новосталь-М' }
    },
    
    // ADMIN ROUTES
    // ADMIN ROUTES
    {
      path: '/admin/login',
      name: 'admin-login',
      component: AdminLoginView,
      meta: { layout: 'AuthLayout', title: 'Вход для сотрудников | Новосталь-М' }
    },
    {
      path: '/admin/dashboard',
      name: 'admin-dashboard',
      component: AdminDashboardView,
      meta: { layout: 'AdminLayout', requiresAuth: true, title: 'Дашборд | Администратор' }
    },
    {
      path: '/admin/users',
      name: 'admin-users',
      component: AdminUsersView,
      meta: { layout: 'AdminLayout', requiresAuth: true, title: 'Пользователи | Администратор' }
    },
    {
      path: '/admin/users/create',
      name: 'admin-users-create',
      component: () => import('../views/AdminCreateUserView.vue'),
      meta: { layout: 'AdminLayout', requiresAuth: true, title: 'Добавить пользователя | Администратор' }
    },
    {
      path: '/admin/users/:id/edit',
      name: 'admin-users-edit',
      component: () => import('../views/AdminEditUserView.vue'),
      meta: { layout: 'AdminLayout', requiresAuth: true, title: 'Редактировать пользователя | Администратор' }
    },
    {
      path: '/admin/bids',
      name: 'admin-bids',
      component: AdminBidsView,
      meta: { layout: 'AdminLayout', requiresAuth: true, title: 'Заявки | Администратор' }
    },
    {
      path: '/admin/moderators',
      name: 'admin-moderators',
      component: AdminModeratorsView,
      meta: { layout: 'AdminLayout', requiresAuth: true, title: 'Модераторы | Администратор', role: 'master' }
    },
    {
      path: '/admin/moderators/create',
      name: 'admin-moderators-create',
      component: () => import('../views/AdminCreateModeratorView.vue'),
      meta: { layout: 'AdminLayout', requiresAuth: true, title: 'Добавить модератора | Администратор', role: 'master' }
    },
    {
      path: '/admin/moderators/:id/edit',
      name: 'admin-moderators-edit',
      component: () => import('../views/AdminEditModeratorView.vue'),
      meta: { layout: 'AdminLayout', requiresAuth: true, title: 'Редактировать модератора | Администратор', role: 'master' }
    },
    {
       path: '/admin',
       redirect: '/admin/dashboard'
    }
  ]
})

router.beforeEach(async (to, from, next) => {
  document.title = (to.meta.title as string) || 'Новосталь-М'
  
  // Admin Guard
  if (to.path.startsWith('/admin') && to.name !== 'admin-login') {
      const adminStore = useAdminStore()
      
      if (!adminStore.token) {
          return next('/admin/login')
      }

      // Ensure user loaded for role check
      if (!adminStore.user) {
          await adminStore.fetchMe()
      }

      if (to.meta.role === 'master' && !adminStore.isMaster) {
          return next('/admin/dashboard')
      }
  }
  
  next()
})

export default router
