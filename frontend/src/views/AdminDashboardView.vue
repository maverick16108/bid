<template>
  <div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">Дашборд</h2>

    <div v-if="loading" class="text-center py-10 text-gray-500">Загрузка...</div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Stats Cards -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">Пользователи</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.users_count }}</p>
          </div>
          <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
            <UserGroupIcon class="w-6 h-6" />
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">Всего заявок</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.bids_count }}</p>
          </div>
          <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
            <ClipboardDocumentListIcon class="w-6 h-6" />
          </div>
        </div>
      </div>

      <!-- Simple CSS Chart (Placeholder) -->
      <div class="col-span-1 md:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Активность заявок (последние 7 дней)</h3>
        
        <div class="flex items-end justify-between h-48 space-x-2">
           <div v-for="(val, idx) in stats.chart_data?.data" :key="idx" 
                class="flex flex-col items-center flex-1 group"
            >
             <div class="w-full bg-blue-100 rounded-t-lg relative overflow-hidden transition-all duration-300 hover:bg-blue-200" 
                  :style="{ height: `${val * 10}%` }"> <!-- assuming max 10 for demo scaling -->
                <div class="absolute bottom-0 w-full bg-blue-500/80 h-full"></div>
                <!-- Tooltip -->
                <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-xs py-1 px-2 rounded transition-opacity">
                    {{ val }}
                </div>
             </div>
             <span class="text-xs text-gray-500 mt-2">{{ stats.chart_data?.labels[idx] }}</span>
           </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAdminStore } from '@/stores/admin'
import { UserGroupIcon, ClipboardDocumentListIcon } from '@heroicons/vue/24/outline'

const adminStore = useAdminStore()
const stats = ref<any>({})
const loading = ref(true)

onMounted(async () => {
    try {
        stats.value = await adminStore.fetchStats()
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
})
</script>
