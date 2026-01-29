<script setup lang="ts">
import { ref, onMounted } from 'vue'
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

// Delete Modal State
const isDeleteModalOpen = ref(false)
const moderatorToDelete = ref<any>(null)
const isDeleting = ref(false)

const fetchModerators = async () => {
    loading.value = true
    try {
        const { data } = await axios.get('/api/admin/moderators')
        moderators.value = data.sort((a: any, b: any) => {
            if (a.email === 'admin@novostal.ru') return -1
            if (b.email === 'admin@novostal.ru') return 1
            return 0
        })
    } catch (e) {
        error.value = 'Ошибка загрузки модераторов'
    } finally {
        loading.value = false
    }
}

const openDeleteModal = (mod: any) => {
    moderatorToDelete.value = mod
    isDeleteModalOpen.value = true
}

const confirmDelete = async () => {
    if (!moderatorToDelete.value) return
    
    isDeleting.value = true
    try {
        await axios.delete(`/api/admin/moderators/${moderatorToDelete.value.id}`)
        fetchModerators()
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

onMounted(() => {
    fetchModerators()
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Модераторы</h1>
            <p class="text-slate-500 text-sm mt-1">Управление сотрудниками с доступом к админ-панели</p>
        </div>
        
        <button @click="goToCreate" class="btn-primary flex items-center justify-center gap-2 px-4 py-2.5 select-none">
            <PlusIcon class="w-5 h-5" />
            <span>Добавить модератора</span>
        </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="text-center py-10 text-slate-500">Загрузка списка...</div>
    <div v-if="loading" class="text-center py-10 text-slate-500">Загрузка списка...</div>
    
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
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
                    <tr v-if="moderators.length === 0">
                        <td colspan="3" class="px-6 py-10 text-center text-sm text-gray-500">
                            Список модераторов пуст
                        </td>
                    </tr>
                </tbody>
            </table>
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
