<template>
  <div class="h-[calc(100vh-64px)] flex flex-col">
    <!-- Header (Sticky/Fixed) -->
    <div class="flex-none py-6 px-8 border-b border-gray-200/50 bg-gray-50/90 backdrop-blur z-20 sticky top-0">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Дашборд</h1>
        <p class="text-slate-500 text-sm mt-1">Обзор ключевых показателей статистики</p>
    </div>

    <!-- Content (Scrollable) -->
    <div class="flex-1 overflow-y-auto px-8 pb-8 pt-0">
        <div v-if="loading" class="flex h-full items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
            <!-- Stats Cards -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
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

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
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

            <!-- Chart -->
            <div class="col-span-1 md:col-span-2 lg:col-span-2 xl:col-span-2 w-full bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
                <div class="flex items-center justify-between mb-8">
                    <div>
                         <h3 class="text-lg font-bold text-gray-900 tracking-tight">Активность заявок</h3>
                         <p class="text-sm text-gray-500 font-medium">За последние 7 дней</p>
                    </div>
                    <!-- Legend/Badge -->
                    <div class="flex items-center gap-2 text-xs font-medium text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        Статистика
                    </div>
                </div>
                
                <div v-if="stats.chart_data && stats.chart_data.data" class="relative z-10" style="height: 240px;">
                    <!-- Grid Lines (Background) -->
                    <div class="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-50">
                        <div v-for="i in 5" :key="i" class="border-b border-dashed border-gray-200 w-full h-px"></div>
                    </div>

                    <div class="flex items-end justify-between space-x-4 h-full pt-4 relative z-20 px-2">
                        <div v-for="(val, idx) in stats.chart_data.data" :key="idx" 
                                class="flex flex-col items-center justify-end flex-1 h-full group cursor-default"
                            >
                            <!-- Value Label (Visible) -->
                            <div class="mb-2 font-bold text-sm transition-all duration-300 transform group-hover:-translate-y-1"
                                 :class="val > 0 ? 'text-indigo-600' : 'text-gray-300 opacity-0 group-hover:opacity-100'"
                            >
                                {{ val }}
                            </div>

                            <!-- Bar Container -->
                            <div class="w-full max-w-[100px] rounded-t-xl relative overflow-hidden transition-all duration-500 ease-out group-hover:brightness-110 shadow-sm" 
                                :style="{ 
                                    height: val > 0 ? `${Math.min(Math.max(val * 15, 5), 100)}%` : '4px',
                                }"
                                :class="val > 0 ? 'bg-gradient-to-t from-blue-600 to-violet-400 shadow-[0_4px_20px_-8px_rgba(79,70,229,0.5)]' : 'bg-gray-100'"
                            >
                                <!-- Shine Effect -->
                                <div class="absolute inset-0 bg-gradient-to-tr from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            </div>
                            
                            <!-- X Axis Label -->
                            <span class="text-xs font-semibold text-gray-400 mt-3 group-hover:text-indigo-500 transition-colors uppercase tracking-wider">{{ stats.chart_data.labels[idx] }}</span>
                        </div>
                    </div>
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
