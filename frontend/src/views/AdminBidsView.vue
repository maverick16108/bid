<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAdminStore } from '@/stores/admin'
import { TrashIcon, CubeIcon, TruckIcon } from '@heroicons/vue/24/outline'

const adminStore = useAdminStore()
const bids = ref<any[]>([])
const loading = ref(false)
const error = ref('')

// Filter & Sort State (Persisted)
const search = computed({
    get: () => adminStore.bidsListState.search,
    set: (val) => adminStore.bidsListState.search = val
})
const sortField = computed({
    get: () => adminStore.bidsListState.sortField,
    set: (val) => adminStore.bidsListState.sortField = val
})
const sortDirection = computed({
    get: () => adminStore.bidsListState.sortDirection,
    set: (val) => adminStore.bidsListState.sortDirection = val
})
const page = computed({
    get: () => adminStore.bidsListState.page,
    set: (val) => adminStore.bidsListState.page = val
})
const per_page = ref(15)

// Infinite Scroll State
const sentinel = ref<HTMLElement | null>(null)
const canLoadMore = ref(true)
let observer: IntersectionObserver | null = null

let debounceTimer: any = null

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'type', label: 'Тип' },
    { key: 'status', label: 'Статус' },
    { key: 'user.name', label: 'Пользователь' }, // Not sortable by this key directly in backend unless joined, logic in controller
    { key: 'created_at', label: 'Дата' }
]

const fetchBids = async (reset = false) => {
    let targetPage = page.value
    if (reset) {
        targetPage = 1
        canLoadMore.value = true
    }

    if (!canLoadMore.value && !reset) return

    loading.value = true
    try {
        const data = await adminStore.fetchBids({
            page: targetPage,
            per_page: per_page.value,
            sort_field: sortField.value,
            sort_direction: sortDirection.value,
            search: search.value
        })
        
        if (reset) {
            bids.value = data.data
            page.value = 1 
        } else {
            bids.value = [...bids.value, ...data.data]
        }
        
        canLoadMore.value = data.current_page < data.last_page
        if (canLoadMore.value && !reset) {
             adminStore.bidsListState.page = targetPage + 1
        }
        else if (canLoadMore.value && reset) {
             adminStore.bidsListState.page = 2
        }
        
    } catch (e) {
        error.value = 'Ошибка загрузки заявок'
    } finally {
        loading.value = false
    }
}

const handleSearch = () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
        fetchBids(true)
    }, 300)
}

const clearSearch = () => {
    search.value = ''
    handleSearch()
}

const handleSort = (key: string) => {
    // Only allow sorting by supported fields
    if (!['id', 'type', 'status', 'created_at'].includes(key)) return

    if (sortField.value === key) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortField.value = key
        sortDirection.value = 'asc'
    }
    fetchBids(true)
}

const confirmDelete = async (bid: any) => {
    if(confirm(`Удалить заявку #${bid.id}?`)) {
        await adminStore.deleteBid(bid.id)
        fetchBids(true)
    }
}

const searchInput = ref<HTMLInputElement | null>(null)
const handleGlobalKeydown = (e: KeyboardEvent) => {
    if (e.target instanceof HTMLInputElement || e.target instanceof HTMLTextAreaElement || (e.target as HTMLElement).isContentEditable) return
    if (e.metaKey || e.ctrlKey || e.altKey || e.key.length > 1) return
    if (searchInput.value) searchInput.value.focus()
}

onMounted(() => {
    fetchBids(true)
    
    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && canLoadMore.value && !loading.value) {
            fetchBids()
        }
    }, { rootMargin: '100px' })
    
    if (sentinel.value) observer.observe(sentinel.value)
    
    document.addEventListener('keydown', handleGlobalKeydown)
})

onUnmounted(() => {
    document.removeEventListener('keydown', handleGlobalKeydown)
})
</script>

<template>
  <div class="h-[calc(100vh-64px)] flex flex-col">
    <!-- Header (Sticky/Fixed) -->
    <div class="flex-none py-6 px-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/90 backdrop-blur z-20 border-b border-gray-200/50 sticky top-0">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Заявки</h1>
            <p class="text-slate-500 text-sm mt-1">Управление входящими заявками</p>
        </div>
    </div>

    <!-- Content (Scrollable) -->
    <div class="flex-1 overflow-hidden px-8 pb-8 pt-0">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col h-full bg-clip-border">
             
            <!-- Toolbar (Sticky inside card) -->
            <div class="flex-none p-4 border-b border-gray-100 flex flex-col sm:flex-row gap-4 justify-between items-center bg-white z-10 sticky top-0 rounded-t-xl">
                 <div class="relative w-full max-w-md group transition-all duration-300 focus-within:max-w-xl">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-indigo-600 text-slate-400">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        ref="searchInput"
                        v-model="search" 
                        type="text" 
                        placeholder="Поиск по ID, клиенту или дате..." 
                        class="block w-full rounded-xl border-gray-200 pl-12 pr-4 py-3 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-sm group-hover:border-gray-300 bg-white"
                        style="padding-left: 50px !important"
                        @input="handleSearch"
                        @keydown.esc="clearSearch"
                    />
                 </div>
            </div>

            <!-- Scrollable Table Area -->
            <div class="flex-1 overflow-y-auto relative scroll-smooth">
                <table class="min-w-full divide-y divide-gray-200 relative">
                    <thead class="bg-gray-50 z-10 shadow-sm ring-1 ring-black/5 sticky top-0">
                        <tr>
                            <th 
                                v-for="col in columns" 
                                :key="col.key"
                                scope="col" 
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider select-none bg-gray-50"
                                :class="{'cursor-pointer hover:bg-gray-100': ['id', 'type', 'status', 'created_at'].includes(col.key)}"
                                @click="handleSort(col.key)"
                            >
                                <div class="flex items-center gap-1">
                                    {{ col.label }}
                                    <span v-if="sortField === col.key" class="text-indigo-600">
                                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 sticky top-0 z-10">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 transition-opacity duration-200" :class="{'opacity-50 blur-[2px] pointer-events-none': loading && bids.length > 0}">
                         <!-- Skeletons (Loading Initial) -->
                        <template v-if="loading && bids.length === 0">
                            <tr v-for="n in 10" :key="'skel-' + n" class="animate-pulse">
                                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-8"></div></td>
                                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-16"></div></td>
                                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-gray-200 mr-3"></div>
                                        <div class="h-4 bg-gray-200 rounded w-32"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-24"></div></td>
                                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-8 ml-auto"></div></td>
                            </tr>
                        </template>

                        <template v-else>
                            <tr v-for="bid in bids" :key="bid.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-600 font-mono">#{{ bid.id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="flex items-center gap-2">
                                        <CubeIcon v-if="bid.type === 'armature'" class="w-4 h-4 text-indigo-500" />
                                        <TruckIcon v-else class="w-4 h-4 text-emerald-500" />
                                        <span>{{ bid.type === 'armature' ? 'Арматура' : 'Транспорт' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full font-medium"
                                    :class="{
                                        'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20': bid.status === 'new',
                                        'bg-green-50 text-green-700 ring-1 ring-green-600/20': bid.status === 'completed',
                                        'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20': bid.status === 'in_progress',
                                        'bg-red-50 text-red-700 ring-1 ring-red-600/20': bid.status === 'rejected',
                                        'bg-gray-50 text-gray-600 ring-1 ring-gray-500/20': !['new','completed','in_progress', 'rejected'].includes(bid.status)
                                    }"
                                    >
                                        {{ bid.status === 'new' ? 'Новая' : (bid.status === 'in_progress' ? 'В работе' : (bid.status === 'completed' ? 'Выполнена' : (bid.status === 'rejected' ? 'Отклонена' : bid.status))) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    <div class="flex items-center">
                                         <div class="h-8 w-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-xs mr-3">
                                            {{ bid.user?.name?.charAt(0) || '?' }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm">{{ bid.user?.name || 'Unknown' }}</span>
                                            <span class="text-xs text-slate-400 font-mono">{{ bid.user?.phone }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-500 text-sm">
                                    {{ new Date(bid.created_at).toLocaleDateString('ru-RU') }}
                                    <div class="text-xs text-gray-400">{{ new Date(bid.created_at).toLocaleTimeString('ru-RU', {hour: '2-digit', minute:'2-digit'}) }}</div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button @click="confirmDelete(bid)" class="text-red-400 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 transition-colors" title="Удалить">
                                            <TrashIcon class="w-5 h-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="bids.length === 0 && !loading">
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">Заявок не найдено</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                 <!-- Sentinel for Infinite Scroll (Loading More) -->
                <div ref="sentinel" class="h-10 flex items-center justify-center w-full py-4">
                     <div v-if="loading && bids.length > 0" class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>
