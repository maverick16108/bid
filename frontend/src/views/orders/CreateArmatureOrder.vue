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
    sum: null,
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
                unit: availableItems.find(i => i.id === form.value.item_id)?.unit,
                sum: form.value.sum
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
  <div class="max-w-2xl">
    
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <!-- Card Header -->
        <div class="flex items-center px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <button 
                @click="router.back()" 
                class="mr-4 p-2 rounded-full text-slate-400 hover:text-slate-700 hover:bg-white hover:shadow-sm transition-all border border-transparent hover:border-slate-200"
                title="Вернуться назад"
            >
                <ArrowLeftIcon class="w-5 h-5" />
            </button>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Новая заявка на арматуру</h1>
        </div>

      <div class="px-8 py-8">
          <div class="text-center mb-8">
              <p class="text-slate-500">Заполните данные по позициям из спецификации</p>
          </div>

        <form @submit.prevent="submit" class="space-y-6 max-w-lg mx-auto">
            
            <!-- Item Select -->
            <div>
                <label for="item" class="block text-sm font-medium mb-1.5 text-slate-700">Позиция</label>
                <select id="item" v-model="form.item_id" required 
                    class="form-select w-full rounded-lg border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500 transition-shadow">
                    <option value="" disabled>Выберите позицию из списка</option>
                    <option v-for="item in availableItems" :key="item.id" :value="item.id">
                        {{ item.name }}
                    </option>
                </select>
            </div>

            <!-- Quantity and Sum -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="quantity" class="block text-sm font-medium mb-1.5 text-slate-700">
                        Количество <span v-if="form.item_id" class="font-normal text-slate-500">({{ availableItems.find(i => i.id === form.item_id)?.unit }})</span>
                    </label>
                    <input type="number" name="quantity" id="quantity" v-model="form.quantity" min="0.1" step="0.1" required 
                        placeholder="0.00"
                        class="form-input w-full rounded-lg border-slate-300 text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 transition-shadow" />
                </div>
                
                <div>
                    <label for="sum" class="block text-sm font-medium mb-1.5 text-slate-700">Сумма (₽)</label>
                    <input type="number" name="sum" id="sum" v-model="form.sum" min="0" step="0.01" 
                        placeholder="0.00"
                        class="form-input w-full rounded-lg border-slate-300 text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 transition-shadow" />
                </div>
            </div>

            <!-- Comment -->
            <div>
                 <label for="comment" class="block text-sm font-medium mb-1.5 text-slate-700">Комментарий</label>
                 <textarea id="comment" name="comment" rows="3" v-model="form.comment" 
                     placeholder="Дополнительная информация..."
                     class="form-textarea w-full rounded-lg border-slate-300 text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 transition-shadow"></textarea>
            </div>

            <div class="pt-4">
                <button 
                    type="submit" 
                    :disabled="loading" 
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-lg shadow-indigo-500/20 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none transition-all duration-200 transform hover:-translate-y-0.5"
                >
                    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ loading ? 'Отправить заявку' : 'Отправить заявку' }}
                </button>
            </div>

        </form>
      </div>
    </div>
  </div>
</template>