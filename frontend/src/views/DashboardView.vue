<script setup lang="ts">
import { 
    ClockIcon, 
    CheckCircleIcon, 
    DocumentTextIcon 
} from '@heroicons/vue/24/outline'

const stats = [
  { name: 'Всего заявок', stat: '12', icon: DocumentTextIcon, color: 'bg-blue-500' },
  { name: 'В обработке', stat: '4', icon: ClockIcon, color: 'bg-yellow-500' },
  { name: 'Выполнено', stat: '8', icon: CheckCircleIcon, color: 'bg-green-500' },
]
</script>

<template>
  <div>
    <!-- Welcome Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 shadow-xl mb-8">
        <div class="absolute inset-0 bg-[url('/img/grid.svg')] bg-center [mask-image:linear-gradient(180deg,white,rgba(255,255,255,0))]"></div>
        <div class="relative px-6 py-10 sm:px-10 sm:py-12">
            <div class="max-w-2xl">
                <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Добро пожаловать, <span class="text-indigo-400">Клиент</span>!
                </h1>
                <p class="mt-4 max-w-xl text-lg text-slate-300">
                    Управляйте своими заказами на арматуру и транспорт в едином интерфейсе. Создавайте новые заявки и отслеживайте их статус в реальном времени.
                </p>
                <div class="mt-8 flex gap-4">
                     <router-link to="/create-order" class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-slate-900 shadow-sm hover:bg-indigo-50 transition-colors">
                        Создать заявку
                     </router-link>
                     <router-link to="/orders" class="rounded-xl bg-white/10 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-white/20 backdrop-blur-sm transition-colors ring-1 ring-white/20">
                        Мои заявки
                     </router-link>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <h3 class="text-lg font-semibold leading-6 text-gray-900 mb-4">Обзор активности</h3>
    <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
      <div v-for="item in stats" :key="item.name" class="relative overflow-hidden rounded-2xl bg-white px-4 py-5 shadow-sm ring-1 ring-gray-200 sm:px-6 sm:py-6 transition-all hover:shadow-lg hover:-translate-y-1">
        <dt>
          <div :class="[item.color, 'absolute rounded-xl p-3 shadow-lg']">
            <component :is="item.icon" class="h-6 w-6 text-white" aria-hidden="true" />
          </div>
          <p class="ml-16 truncate text-sm font-medium text-gray-500">{{ item.name }}</p>
        </dt>
        <dd class="ml-16 flex items-baseline pb-1 sm:pb-2">
          <p class="text-2xl font-bold text-gray-900">{{ item.stat }}</p>
        </dd>
      </div>
    </dl>
  </div>
</template>