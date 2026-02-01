<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import { useOrdersStore } from '@/stores/orders'

const router = useRouter()
const ordersStore = useOrdersStore()
const loading = ref(false)

const form = ref({
    date: '',
    vehicle_type: '',
    address_from: '',
    address_to: '',
    comment: ''
})

const submit = async () => {
    loading.value = true
    try {
        await ordersStore.createOrder({
            type: 'transport',
            details: { ...form.value },
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
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Заказ транспорта</h1>
        </div>

      <div class="px-8 py-8">
          <div class="text-center mb-8">
              <p class="text-slate-500">Заполните параметры перевозки</p>
          </div>

        <form @submit.prevent="submit" class="space-y-6 max-w-lg mx-auto">
            
            <!-- Date -->
            <div>
                <label for="date" class="block text-sm font-medium mb-1.5 text-slate-700">Дата подачи</label>
                <input type="date" name="date" id="date" v-model="form.date" required 
                    class="form-input w-full rounded-lg border-slate-300 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500 transition-shadow cursor-pointer" />
            </div>

            <!-- Vehicle Type -->
            <div>
                <label for="vehicle_type" class="block text-sm font-medium mb-1.5 text-slate-700">Тип ТС</label>
                 <select id="vehicle_type" v-model="form.vehicle_type" required 
                    class="form-select w-full rounded-lg border-slate-300 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500 transition-shadow cursor-pointer">
                    <option value="" disabled>Выберите тип транспорта</option>
                    <option value="gazelle">Газель (1.5т)</option>
                    <option value="fura">Фура (20т)</option>
                    <option value="manipulator">Манипулятор</option>
                </select>
            </div>

            <!-- From -->
            <div>
                <label for="address_from" class="block text-sm font-medium mb-1.5 text-slate-700">Откуда</label>
                <input type="text" name="address_from" id="address_from" v-model="form.address_from" required 
                    placeholder="Адрес загрузки"
                    class="form-input w-full rounded-lg border-slate-300 text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:ring-emerald-500 transition-shadow" />
            </div>

            <!-- To -->
            <div>
                <label for="address_to" class="block text-sm font-medium mb-1.5 text-slate-700">Куда</label>
                <input type="text" name="address_to" id="address_to" v-model="form.address_to" required 
                     placeholder="Адрес выгрузки"
                     class="form-input w-full rounded-lg border-slate-300 text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:ring-emerald-500 transition-shadow" />
            </div>

            <!-- Comment -->
            <div>
                 <label for="comment" class="block text-sm font-medium mb-1.5 text-slate-700">Комментарий</label>
                 <textarea id="comment" name="comment" rows="3" v-model="form.comment" 
                      placeholder="Дополнительные требования к транспорту..."
                      class="form-textarea w-full rounded-lg border-slate-300 text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:ring-emerald-500 transition-shadow"></textarea>
            </div>

            <div class="pt-4">
                <button 
                    type="submit" 
                    :disabled="loading" 
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-lg shadow-emerald-500/20 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none transition-all duration-200 transform hover:-translate-y-0.5"
                >
                    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ loading ? 'Отправка...' : 'Отправить заявку' }}
                </button>
            </div>

        </form>
      </div>
    </div>
  </div>
</template>