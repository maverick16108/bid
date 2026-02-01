<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminStore } from '@/stores/admin'
import axios from 'axios'
import { Dialog, DialogPanel, TransitionChild, TransitionRoot, DialogTitle } from '@headlessui/vue'
import { PlusIcon, PencilSquareIcon, TrashIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const adminStore = useAdminStore()
const router = useRouter()
const moderators = ref<any[]>([])
const loading = ref(false)
const error = ref('')

// Filter & Sort State (Persisted)
const search = computed({
    get: () => adminStore.moderatorsListState.search,
    set: (val) => adminStore.moderatorsListState.search = val
})
const sortField = computed({
    get: () => adminStore.moderatorsListState.sortField,
    set: (val) => adminStore.moderatorsListState.sortField = val
})
const sortDirection = computed({
    get: () => adminStore.moderatorsListState.sortDirection,
    set: (val) => adminStore.moderatorsListState.sortDirection = val
})
const page = computed({
    get: () => adminStore.moderatorsListState.page,
    set: (val) => adminStore.moderatorsListState.page = val
})
const per_page = ref(15)
const meta = ref({
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
    total: 0
})

let debounceTimer: any = null

const columns = [
    { key: 'name', label: 'Имя' },
    { key: 'email', label: 'Email' }
]

// Delete Modal State
const isDeleteModalOpen = ref(false)
const moderatorToDelete = ref<any>(null)
const isDeleting = ref(false)

const fetchModerators = async (reset = false) => {
    let targetPage = page.value
    if (reset) {
        targetPage = 1
        canLoadMore.value = true
        // Do not clear moderators yet
    }

    if (!canLoadMore.value && !reset) return

    loading.value = true
    try {
        const { data } = await axios.get('/api/admin/moderators', {
            params: {
                 page: targetPage,
                 per_page: per_page.value,
                 sort_field: sortField.value,
                 sort_direction: sortDirection.value,
                 search: search.value
            }
        })
        
        if (reset) {
            moderators.value = data.data
            page.value = 1
        } else {
            moderators.value = [...moderators.value, ...data.data]
        }
        
        meta.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            from: data.from,
            to: data.to,
            total: data.total
        }
        
        canLoadMore.value = data.current_page < data.last_page
        if (canLoadMore.value && !reset) {
            adminStore.moderatorsListState.page = targetPage + 1
        }
        else if (canLoadMore.value && reset) {
            adminStore.moderatorsListState.page = 2
        }
        
    } catch (e) {
        error.value = 'Ошибка загрузки модераторов'
    } finally {
        loading.value = false
    }
}

const handleSearch = () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
        fetchModerators(true)
    }, 300)
}

const clearSearch = () => {
    search.value = ''
    handleSearch()
}

const handleSort = (key: string) => {
    if (sortField.value === key) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortField.value = key
        sortDirection.value = 'asc'
    }
    fetchModerators(true)
}

// Infinite Scroll Sentinel
const sentinel = ref<HTMLElement | null>(null)
const canLoadMore = ref(true)
let observer: IntersectionObserver | null = null

const openDeleteModal = (mod: any) => {
    moderatorToDelete.value = mod
    isDeleteModalOpen.value = true
}

const confirmDelete = async () => {
    if (!moderatorToDelete.value) return
    
    isDeleting.value = true
    try {
        await axios.delete(`/api/admin/moderators/${moderatorToDelete.value.id}`)
        fetchModerators(true)
        isDeleteModalOpen.value = false
    } catch (e) {
        alert('Ошибка удаления')
    } finally {
        isDeleting.value = false
        moderatorToDelete.value = null
    }
}

const goToCreate = () => router.push('/admin/moderators/create')
const goToEdit = (id: number) => router.push(`/admin/moderators/${id}/edit`)


const searchInput = ref<HTMLInputElement | null>(null)

const handleGlobalKeydown = (e: KeyboardEvent) => {
    if (e.target instanceof HTMLInputElement || e.target instanceof HTMLTextAreaElement || (e.target as HTMLElement).isContentEditable) return
    if (e.metaKey || e.ctrlKey || e.altKey) return
    if (e.key.length > 1) return

    if (searchInput.value) {
        searchInput.value.focus()
    }
}

onMounted(() => {
    fetchModerators(true)
    
     observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && canLoadMore.value && !loading.value) {
            fetchModerators()
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
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Модераторы</h1>
            <p class="text-slate-500 text-sm mt-1">Управление сотрудниками</p>
        </div>
        
        <button @click="goToCreate" class="btn-primary flex items-center justify-center gap-2 px-4 py-2.5 select-none shadow-sm hover:shadow-md transition-all active:scale-95 bg-indigo-600 text-white rounded-xl font-medium">
            <PlusIcon class="w-5 h-5" />
            <span>Добавить модератора</span>
        </button>
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
                        placeholder="Начните вводить для поиска..." 
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
                    <thead class="bg-gray-50 z-10 shadow-sm ring-1 ring-black/5">
                        <tr>
                             <th 
                                v-for="col in columns" 
                                :key="col.key"
                                scope="col" 
                                class="sticky top-0 bg-gray-50 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors select-none z-10"
                                @click="handleSort(col.key)"
                            >
                                <div class="flex items-center gap-1">
                                    {{ col.label }}
                                    <span v-if="sortField === col.key" class="text-indigo-600">
                                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="sticky top-0 bg-gray-50 px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider z-10">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 transition-opacity duration-200" :class="{'opacity-50 blur-[2px] pointer-events-none': loading && moderators.length > 0}">
                        <!-- Skeletons (Loading Initial) -->
                        <template v-if="loading && moderators.length === 0">
                            <tr v-for="n in 5" :key="'skel-' + n" class="animate-pulse">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex-shrink-0"></div>
                                        <div class="ml-4">
                                            <div class="h-4 bg-gray-200 rounded w-32"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="h-4 bg-gray-200 rounded w-48"></div>
                                </td>
                                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-16 ml-auto"></div></td>
                            </tr>
                        </template>
    
                        <!-- Real Data -->
                        <template v-else>
                            <tr v-for="mod in moderators" :key="mod.id" class="group hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full"
                                                :class="mod.email === 'admin@novostal.ru' ? 'bg-red-100 text-red-700' : 'bg-indigo-100 text-indigo-700'">
                                                <span class="font-medium leading-none">{{ mod.name.charAt(0) }}</span>
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ mod.name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a :href="'mailto:' + mod.email" class="text-sm text-gray-500 hover:text-indigo-600 transition-colors">{{ mod.email }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-3">
                                        <button @click="goToEdit(mod.id)" class="text-indigo-600 hover:text-indigo-900 p-1 rounded hover:bg-indigo-50 select-none" title="Редактировать">
                                            <PencilSquareIcon class="w-5 h-5" />
                                        </button>
                                        <button v-if="mod.email !== 'admin@novostal.ru'" @click="openDeleteModal(mod)" class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 select-none" title="Удалить">
                                            <TrashIcon class="w-5 h-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="moderators.length === 0 && !loading">
                                <td colspan="3" class="px-6 py-10 text-center text-sm text-gray-500">
                                    Ничего не найдено
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            
                 <!-- Sentinel for Infinite Scroll -->
                 <div ref="sentinel" class="h-10 flex items-center justify-center w-full py-4">
                     <div v-if="loading && moderators.length > 0" class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
                </div>
            </div>
        </div>
    </div>
  </div>


  <!-- Delete Confirmation Modal -->
  <TransitionRoot as="template" :show="isDeleteModalOpen">
    <Dialog as="div" class="relative z-50" @close="isDeleteModalOpen = false">
      <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <DialogPanel class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">
              <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                  <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <ExclamationTriangleIcon class="h-6 w-6 text-red-600" aria-hidden="true" />
                  </div>
                  <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                    <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-slate-900">Удалить модератора?</DialogTitle>
                    <div class="mt-2">
                        <p class="text-sm text-slate-500">
                            Вы действительно хотите удалить <strong>{{ moderatorToDelete?.name }}</strong>? <br>
                            Это действие нельзя будет отменить.
                        </p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button type="button" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition-colors focus:ring-2 focus:ring-red-500 focus:ring-offset-2" @click="confirmDelete" :disabled="isDeleting">
                    {{ isDeleting ? 'Удаление...' : 'Удалить' }}
                </button>
                <button type="button" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors" @click="isDeleteModalOpen = false" ref="cancelButtonRef">
                    Отмена
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
