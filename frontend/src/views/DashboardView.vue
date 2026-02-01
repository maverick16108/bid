<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import { 
    ClockIcon, 
    CheckCircleIcon, 
    DocumentTextIcon 
} from '@heroicons/vue/24/outline'

const authStore = useAuthStore()
const hoveredIndex = ref(-1)

const stats = ref([
  { name: 'Всего заявок', stat: '0', icon: DocumentTextIcon, color: 'bg-blue-500' },
  { name: 'В обработке', stat: '0', icon: ClockIcon, color: 'bg-yellow-500' },
  { name: 'Выполнено', stat: '0', icon: CheckCircleIcon, color: 'bg-green-500' },
])

const chartData = ref<{
  labels: string[]
  datasets: { label: string; backgroundColor: string; data: number[] }[]
}>({
  labels: [],
  datasets: [
    {
      label: 'Заявки',
      backgroundColor: '#6366f1',
      data: []
    }
  ]
})

// Chart Logic
const maxVal = computed(() => {
    const data = chartData.value.datasets[0].data
    if (!data.length) return 10
    return Math.max(...data.map(Number)) * 1.2 || 5 // +20% buffer
})

const chartPoints = computed(() => {
    const data = chartData.value.datasets[0].data
    if (!data.length) return []
    
    return data.map((val, idx) => {
        // Center the point in the day slot: (idx + 0.5) / length * 100
        const x = ((idx + 0.5) / data.length) * 100
        const y = 100 - (Number(val) / maxVal.value) * 100
        return { x, y }
    })
})

const linePath = computed(() => {
    const points = chartPoints.value
    if (!points.length) return ''
    
    // Smooth curve generator (Catmull-Rom spline)
    let d = `M ${points[0].x},${points[0].y}`
    
    for (let i = 0; i < points.length - 1; i++) {
        const p0 = points[i > 0 ? i - 1 : i]
        const p1 = points[i]
        const p2 = points[i + 1]
        const p3 = points[i + 2 < points.length ? i + 2 : i + 1]

        const cp1x = p1.x + (p2.x - p0.x) / 4
        // Clamp Y to max 100. If point is at bottom (100), force handle to be 100 for horizontal tangent.
        let cp1y = p1.y + (p2.y - p0.y) / 4
        if (p1.y > 99) cp1y = 100
        cp1y = Math.min(cp1y, 100)

        const cp2x = p2.x - (p3.x - p1.x) / 4
        let cp2y = p2.y - (p3.y - p1.y) / 4
        if (p2.y > 99) cp2y = 100
        cp2y = Math.min(cp2y, 100)

        d += ` C ${cp1x},${cp1y} ${cp2x},${cp2y} ${p2.x},${p2.y}`
    }
    
    return d
})

const areaPath = computed(() => {
    const points = chartPoints.value
    if (!points.length) return ''
    if (!linePath.value) return ''

    // Close the path for area fill
    const firstX = points[0].x
    const lastX = points[points.length - 1].x

    return `${linePath.value} L ${lastX},100 L ${firstX},100 Z`
})
// chartOptions removed

onMounted(async () => {
    try {
        const response = await api.get('/orders/stats')
        const data = response.data
        
        stats.value = [
            { name: 'Всего заявок', stat: data.total.toString(), icon: DocumentTextIcon, color: 'bg-blue-500' },
            { name: 'В обработке', stat: data.processing.toString(), icon: ClockIcon, color: 'bg-yellow-500' },
            { name: 'Выполнено', stat: data.completed.toString(), icon: CheckCircleIcon, color: 'bg-green-500' },
        ]

        chartData.value = {
            labels: data.chart.labels,
            datasets: [
                {
                    label: 'Заявки',
                    backgroundColor: '#6366f1',
                    data: data.chart.data
                }
            ]
        }
    } catch (e) {
        console.error('Failed to fetch stats', e)
    }
})
</script>

<template>
  <div>
    <!-- Welcome Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 shadow-xl mb-8">
        <div class="absolute inset-0 bg-[url('/img/grid.svg')] bg-center [mask-image:linear-gradient(180deg,white,rgba(255,255,255,0))]"></div>
        <div class="relative px-6 py-10 sm:px-10 sm:py-12">
            <div class="max-w-2xl">
                <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Добро пожаловать, <span class="text-indigo-400">{{ authStore.user?.name || 'Клиент' }}</span>!
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
    
        <div class="mt-8 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative group">
            <div class="flex items-center justify-between mb-6">
                <div>
                     <h3 class="text-lg font-bold text-gray-900 tracking-tight">Статистика заявок</h3>
                     <p class="text-sm text-gray-500 font-medium">За последние 30 дней</p>
                </div>
                <div class="flex items-center gap-2 text-xs font-medium text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    Активность
                </div>
            </div>
            
            <div class="relative h-[240px] w-full" @mouseleave="hoveredIndex = -1">
                <!-- Data Check -->
                <div v-if="!chartData.datasets[0].data.length" class="h-full flex items-center justify-center text-gray-400">
                    Нет данных
                </div>

                <template v-else>
                     <!-- SVG Chart -->
                    <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 100 100">
                        <defs>
                            <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#4f46e5" stop-opacity="0.3"/>
                                <stop offset="100%" stop-color="#4f46e5" stop-opacity="0"/>
                            </linearGradient>
                            <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                                <feGaussianBlur stdDeviation="3" result="blur" />
                                <feComposite in="SourceGraphic" in2="blur" operator="over" />
                            </filter>
                        </defs>
                        
                        <!-- Grid Lines (Background) -->
                        <line v-for="i in [0, 25, 50, 75, 100]" :key="i" x1="0" :y1="i" x2="100" :y2="i" stroke="#e5e7eb" stroke-width="0.5" stroke-dasharray="2 2" vector-effect="non-scaling-stroke" />

                        <!-- Area Fill -->
                        <path :d="areaPath" fill="url(#chartGradient)" />

                        <!-- Smooth Line -->
                        <path :d="linePath" fill="none" stroke="#4f46e5" stroke-width="5" vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" filter="url(#glow)" />
                    </svg>

                    <!-- Markers & Labels Layer (Absolute positioning for perfect sync) -->
                    <div class="absolute inset-0 pointer-events-none">
                        <template v-for="(point, idx) in chartPoints" :key="idx">
                            <!-- Show only if value > 0 -->
                             <div 
                                v-if="chartData.datasets[0].data[idx] > 0"
                                class="absolute transform -translate-x-1/2 -translate-y-1/2 transition-all duration-300"
                                :style="{ left: `${point.x}%`, top: `${point.y}%` }"
                            >
                                <!-- Permanent Dot -->
                                <div class="w-3 h-3 bg-white rounded-full border-[3px] border-indigo-500 shadow-sm relative z-10"></div>
                                
                                <!-- Permanent Label (Alternative Display) -->
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-indigo-50 text-indigo-700 text-[10px] font-bold px-1.5 py-0.5 rounded shadow-sm opacity-0 scale-75 group-hover:opacity-100 group-hover:scale-100 transition-all">
                                    {{ chartData.datasets[0].data[idx] }}
                                </div>
                            </div>
                        </template>

                        <!-- Hover Highlight (Active Dot) -->
                        <div 
                            v-if="hoveredIndex !== -1 && chartPoints[hoveredIndex] && Number(chartData.datasets[0].data[hoveredIndex]) > 0"
                            class="absolute transform -translate-x-1/2 -translate-y-1/2 transition-all duration-75"
                            :style="{ left: `${chartPoints[hoveredIndex].x}%`, top: `${chartPoints[hoveredIndex].y}%` }"
                        >
                             <div class="w-4 h-4 bg-indigo-600 rounded-full border-4 border-white shadow-lg"></div>
                        </div>
                    </div>

                    <!-- Interactive Tooltip Overlay -->
                    <div class="absolute inset-0 pointer-events-none">
                        <div 
                            v-if="hoveredIndex !== -1 && chartPoints[hoveredIndex]"
                             class="absolute transition-all duration-75 z-40"
                             :style="{ left: `${chartPoints[hoveredIndex].x}%`, top: '0', height: '100%' }"
                        >
                            <!-- Vertical Line -->
                            <div class="absolute top-0 bottom-0 left-0 w-px -ml-px bg-indigo-500/30 border-l border-dashed border-indigo-500/50"></div>
                            
                            <!-- Date Label (At the bottom, always separate) -->
                            <div 
                                class="absolute top-[102%] left-1/2 -translate-x-1/2 px-2 py-1 bg-slate-100 text-slate-500 text-[10px] font-bold tracking-wider rounded shadow-sm border border-slate-200 whitespace-nowrap z-30"
                            >
                                {{ chartData.labels[hoveredIndex] }}
                            </div>

                            <!-- Quantity Tooltip (If > 0) -->
                             <div 
                                v-if="Number(chartData.datasets[0].data[hoveredIndex]) > 0"
                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-4 py-2 bg-slate-900/95 backdrop-blur-xl rounded-2xl shadow-[0_10px_40px_-10px_rgba(79,70,229,0.5)] transform min-w-[80px] text-center border border-white/10 origin-bottom"
                                :style="{ bottom: `${100 - chartPoints[hoveredIndex].y}%`, marginBottom: '20px' }"
                            >
                                <div class="text-3xl font-black text-white tracking-tight leading-none mb-1">{{ chartData.datasets[0].data[hoveredIndex] }}</div>
                                <div class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest text-opacity-80">Заявок</div>
                                
                                <div class="absolute inset-0 rounded-2xl bg-indigo-500/10 mix-blend-overlay -z-10"></div>
                                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-px border-8 border-transparent border-t-slate-900/95"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Hit Zones (Invisible, for mouse events) -->
                    <div class="absolute inset-0 flex items-stretch user-select-none">
                        <div 
                            v-for="(_, idx) in chartData.datasets[0].data" 
                            :key="idx" 
                            class="flex-1 cursor-crosshair hover:bg-transparent"
                            @mouseenter="hoveredIndex = idx"
                        ></div>
                    </div>
                </template>
            </div>
        </div>

  </div>
</template>