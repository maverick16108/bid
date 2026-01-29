<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AuthLayout from './layouts/AuthLayout.vue'
import AppLayout from './layouts/AppLayout.vue'
import AdminLayout from './layouts/AdminLayout.vue'

const route = useRoute()

const layout = computed(() => {
  if (route.meta.layout === 'AuthLayout') return AuthLayout
  if (route.meta.layout === 'AdminLayout') return AdminLayout
  return AppLayout
})
</script>

<template>
  <component :is="layout">
    <router-view />
  </component>
</template>