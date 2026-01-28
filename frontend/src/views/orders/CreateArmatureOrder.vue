<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import { useOrdersStore } from '@/stores/orders'

const router = useRouter()
const ordersStore = useOrdersStore()
const loading = ref(false)

// Mock data
const availableItems = [
    { id: '1c-id-001', name: 'Арматура А500С d=10мм', unit: 'т' },
    { id: '1c-id-002', name: 'Арматура А500С d=12мм', unit: 'т' },
    { id: '1c-id-003', name: 'Арматура А500С d=16мм', unit: 'т' },
]

const form = ref({
    item_id: '',
    quantity: 0,
    comment: ''
})

const submit = async () => {
    loading.value = true
    try {
        await ordersStore.createOrder({
            type: 'armature',
            details: {
                item_id: form.value.item_id,
                item_name: availableItems.find(i => i.id === form.value.item_id)?.name,
                quantity: form.value.quantity,
                unit: availableItems.find(i => i.id === form.value.item_id)?.unit
            },
            comment: form.value.comment
        })
        router.push('/orders')
    } catch (e) {
        alert('Ошибка при создании заявки')
    } finally {
        loading.value = false
    }
}
</script>

<template>
  <div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <button @click="router.back()" class="flex items-center text-sm text-gray-500 hover:text-gray-700 transition-colors">
            <ArrowLeftIcon class="h-4 w-4 mr-1" />
            Назад к выбору
        </button>
    </div>

    <div class="bg-white shadow-xl shadow-gray-200/50 rounded-2xl overflow-hidden border border-gray-100">
      <div class="px-6 py-6 sm:px-10 bg-gray-50/50 border-b border-gray-100">
          <h1 class="text-2xl font-bold text-gray-900">Новая заявка на арматуру</h1>
          <p class="mt-1 text-sm text-gray-500">Заполните данные по позициям из спецификации</p>
      </div>

      <div class="px-6 py-8 sm:px-10">
        <form @submit.prevent="submit" class="space-y-8">
            
            <div class="grid grid-cols-1 gap-y-8 gap-x-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="item" class="block text-sm font-semibold text-gray-700 mb-2">Позиция</label>
                    <div class="relative">
                        <select id="item" v-model="form.item_id" required 
                            class="block w-full h-12 rounded-lg border-gray-300 pl-3 pr-10 text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm shadow-sm transition-shadow cursor-pointer bg-white">
                            <option value="" disabled>Выберите позицию из списка</option>
                            <option v-for="item in availableItems" :key="item.id" :value="item.id">
                                {{ item.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="sm:col-span-1">
                    <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-2">
                        Количество <span v-if="form.item_id" class="font-normal text-gray-500">({{ availableItems.find(i => i.id === form.item_id)?.unit }})</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="quantity" id="quantity" v-model="form.quantity" min="0.1" step="0.1" required 
                            placeholder="0.00"
                            class="block w-full h-12 rounded-lg border-gray-300 pl-4 text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm shadow-sm transition-shadow" />
                    </div>
                </div>

                <div class="sm:col-span-2">
                     <label for="comment" class="block text-sm font-semibold text-gray-700 mb-2">Комментарий</label>
                     <div class="relative">
                        <textarea id="comment" name="comment" rows="3" v-model="form.comment" 
                            placeholder="Дополнительная информация..."
                            class="block w-full rounded-lg border-gray-300 pl-4 pt-3 text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm shadow-sm transition-shadow"></textarea>
                     </div>
                </div>
            </div>

            <div class="flex justify-end pt-6 border-t border-gray-100">
                <button type="button" @click="router.back()" class="mr-3 bg-white py-3 px-6 border border-gray-300 rounded-xl shadow-sm text-sm font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    Отмена
                </button>
                <button type="submit" :disabled="loading" class="inline-flex justify-center py-3 px-6 border border-transparent shadow-lg shadow-indigo-500/30 text-sm font-bold rounded-xl text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:-translate-y-0.5">
                    {{ loading ? 'Отправка...' : 'Отправить заявку' }}
                </button>
            </div>

        </form>
      </div>
    </div>
  </div>
</template>