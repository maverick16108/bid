<template>
  <div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">Все заявки</h2>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-gray-500 text-sm">
                <tr>
                    <th class="px-6 py-4 font-medium">ID</th>
                    <th class="px-6 py-4 font-medium">Пользователь</th>
                    <th class="px-6 py-4 font-medium">Тип</th>
                    <th class="px-6 py-4 font-medium">Статус</th>
                    <th class="px-6 py-4 font-medium">Дата</th>
                    <th class="px-6 py-4 font-medium text-right">Действия</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr v-for="bid in bids" :key="bid.id" class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-gray-600">#{{ bid.id }}</td>
                    <td class="px-6 py-4 font-medium text-gray-900">
                        <div class="flex flex-col">
                            <span>{{ bid.user?.name || 'Unknown' }}</span>
                            <span class="text-xs text-gray-400">{{ bid.user?.email }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ bid.type }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full"
                           :class="{
                               'bg-yellow-100 text-yellow-700': bid.status === 'new',
                               'bg-green-100 text-green-700': bid.status === 'completed',
                               'bg-gray-100 text-gray-600': !['new','completed'].includes(bid.status)
                           }"
                        >
                            {{ bid.status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500 text-sm">{{ new Date(bid.created_at).toLocaleDateString() }}</td>
                    <td class="px-6 py-4 text-right">
                         <!-- Details Button -->
                         <button class="text-blue-500 hover:text-blue-700 text-sm font-medium mr-3 select-none">
                            Подробнее
                        </button>
                        <button @click="confirmDelete(bid)" class="text-red-500 hover:text-red-700 text-sm font-medium transition-colors select-none">
                            Удалить
                        </button>
                    </td>
                </tr>
                <tr v-if="bids.length === 0">
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">Заявок не найдено</td>
                </tr>
            </tbody>
        </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAdminStore } from '@/stores/admin'

const adminStore = useAdminStore()
const bids = ref<any[]>([])

onMounted(async () => {
    bids.value = await adminStore.fetchBids()
})

const confirmDelete = async (bid: any) => {
    if(confirm(`Удалить заявку #${bid.id}?`)) {
        await adminStore.deleteBid(bid.id)
        bids.value = bids.value.filter(b => b.id !== bid.id)
    }
}
</script>
