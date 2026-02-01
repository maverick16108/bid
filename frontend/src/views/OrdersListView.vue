<script setup lang="ts">
import { onMounted } from 'vue'
import { useOrdersStore } from '@/stores/orders'
import { 
    CubeIcon, 
    TruckIcon, 
    CalendarIcon, 
    MapPinIcon,
    InformationCircleIcon
} from '@heroicons/vue/24/outline'

const ordersStore = useOrdersStore()

onMounted(() => {
    ordersStore.fetchOrders()
})

const getStatusClass = (status: string) => {
    switch (status) {
        case 'new': return 'bg-blue-50 text-blue-700 ring-blue-600/20'
        case 'in_progress': return 'bg-yellow-50 text-yellow-700 ring-yellow-600/20'
        case 'completed': return 'bg-green-50 text-green-700 ring-green-600/20'
        case 'rejected': return 'bg-red-50 text-red-700 ring-red-600/20'
        default: return 'bg-gray-50 text-gray-700 ring-gray-600/20'
    }
}

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'new': return 'Отправлена'
        case 'in_progress': return 'В обработке'
        case 'completed': return 'Выполнена'
        case 'rejected': return 'Отклонена'
        default: return status
    }
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>

<template>
  <div class="max-w-6xl mx-auto">
    <div class="md:flex md:items-center md:justify-between mb-8">
      <div class="min-w-0 flex-1">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
          Мои заявки
        </h2>
        <p class="mt-2 text-sm text-gray-500">История и текущий статус ваших заказов</p>
      </div>
      <div class="mt-4 flex md:ml-4 md:mt-0">
        <router-link to="/create-order" class="inline-flex items-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all transform hover:-translate-y-0.5">
          Создать заявку
        </router-link>
      </div>
    </div>

    <div v-if="ordersStore.loading && ordersStore.orders.length === 0" class="flex justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>

    <div v-else-if="ordersStore.orders.length === 0" class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-gray-200">
        <InformationCircleIcon class="mx-auto h-12 w-12 text-gray-300" />
        <h3 class="mt-2 text-sm font-semibold text-gray-900">Заявок пока нет</h3>
        <p class="mt-1 text-sm text-gray-500">Самое время создать вашу первую заявку.</p>
        <div class="mt-6">
            <router-link to="/create-order" class="text-indigo-600 font-bold hover:text-indigo-500">
                Перейти к созданию &rarr;
            </router-link>
        </div>
    </div>

    <div v-else class="space-y-4">
        <div v-for="order in ordersStore.orders" :key="order.id" class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div :class="[order.type === 'armature' ? 'bg-indigo-100 text-indigo-600' : 'bg-emerald-100 text-emerald-600', 'p-3 rounded-xl']">
                            <CubeIcon v-if="order.type === 'armature'" class="h-6 w-6" />
                            <TruckIcon v-else class="h-6 w-6" />
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-900">
                                {{ order.type === 'armature' ? 'Заказ арматуры' : 'Заказ транспорта' }}
                            </h4>
                            <p class="text-xs text-gray-500">№ {{ order.id }} от {{ formatDate(order.created_at) }}</p>
                        </div>
                    </div>
                    <div>
                        <span :class="[getStatusClass(order.status), 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset']">
                            {{ getStatusLabel(order.status) }}
                        </span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <template v-if="order.type === 'armature'">
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="font-semibold mr-2">Позиция:</span>
                            {{ order.details.item_name }}
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="font-semibold mr-2">Количество:</span>
                            {{ order.details.quantity }} {{ order.details.unit }}
                        </div>
                        <div v-if="order.details.sum" class="flex items-center text-sm text-gray-600">
                            <span class="font-semibold mr-2">Сумма:</span>
                            {{ Number(order.details.sum).toLocaleString('ru-RU') }} ₽
                        </div>
                    </template>
                    <template v-else>
                        <div class="flex items-center text-sm text-gray-600">
                            <CalendarIcon class="h-4 w-4 mr-2 text-gray-400" />
                            {{ order.details.date }}
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <MapPinIcon class="h-4 w-4 mr-2 text-gray-400" />
                            <span class="truncate">{{ order.details.address_to }}</span>
                        </div>
                    </template>
                </div>

                <div v-if="order.comment" class="mt-4 p-3 bg-gray-50 rounded-lg text-sm text-gray-600 italic">
                    "{{ order.comment }}"
                </div>
            </div>
        </div>
    </div>
  </div>
</template>
