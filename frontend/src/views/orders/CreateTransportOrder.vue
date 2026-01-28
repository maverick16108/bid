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
  <div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <button @click="router.back()" class="flex items-center text-sm text-gray-500 hover:text-gray-700 transition-colors">
            <ArrowLeftIcon class="h-4 w-4 mr-1" />
            Назад к выбору
        </button>
    </div>

    <div class="bg-white shadow-xl shadow-gray-200/50 rounded-2xl overflow-hidden border border-gray-100">
      <div class="px-6 py-6 sm:px-10 bg-gray-50/50 border-b border-gray-100">
          <h1 class="text-2xl font-bold text-gray-900">Заказ транспорта</h1>
          <p class="mt-1 text-sm text-gray-500">Заполните параметры перевозки</p>
      </div>

      <div class="px-6 py-8 sm:px-10">
        <form @submit.prevent="submit" class="space-y-8">
            
            <div class="grid grid-cols-1 gap-y-8 gap-x-6 sm:grid-cols-2">
                
                <div>
                    <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">Дата подачи</label>
                    <div class="relative">
                        <input type="date" name="date" id="date" v-model="form.date" required 
                            class="block w-full h-12 rounded-lg border-gray-300 pl-4 text-gray-900 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm shadow-sm transition-shadow cursor-pointer" />
                    </div>
                </div>

                <div>
                    <label for="vehicle_type" class="block text-sm font-semibold text-gray-700 mb-2">Тип ТС</label>
                     <select id="vehicle_type" v-model="form.vehicle_type" required 
                        class="block w-full h-12 rounded-lg border-gray-300 pl-3 pr-10 text-gray-900 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm shadow-sm transition-shadow cursor-pointer bg-white">
                        <option value="" disabled>Выберите тип транспорта</option>
                        <option value="gazelle">Газель (1.5т)</option>
                        <option value="fura">Фура (20т)</option>
                        <option value="manipulator">Манипулятор</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label for="address_from" class="block text-sm font-semibold text-gray-700 mb-2">Откуда</label>
                    <div class="relative">
                        <input type="text" name="address_from" id="address_from" v-model="form.address_from" required 
                            placeholder="Адрес загрузки"
                            class="block w-full h-12 rounded-lg border-gray-300 pl-4 text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm shadow-sm transition-shadow" />
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label for="address_to" class="block text-sm font-semibold text-gray-700 mb-2">Куда</label>
                    <div class="relative">
                        <input type="text" name="address_to" id="address_to" v-model="form.address_to" required 
                             placeholder="Адрес выгрузки"
                             class="block w-full h-12 rounded-lg border-gray-300 pl-4 text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm shadow-sm transition-shadow" />
                    </div>
                </div>

                 <div class="sm:col-span-2">
                     <label for="comment" class="block text-sm font-semibold text-gray-700 mb-2">Комментарий</label>
                     <div class="relative">
                        <textarea id="comment" name="comment" rows="3" v-model="form.comment" 
                             placeholder="Дополнительные требования к транспорту..."
                             class="block w-full rounded-lg border-gray-300 pl-4 pt-3 text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm shadow-sm transition-shadow"></textarea>
                     </div>
                </div>
            </div>

            <div class="flex justify-end pt-6 border-t border-gray-100">
                <button type="button" @click="router.back()" class="mr-3 bg-white py-3 px-6 border border-gray-300 rounded-xl shadow-sm text-sm font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                    Отмена
                </button>
                <button type="submit" :disabled="loading" class="inline-flex justify-center py-3 px-6 border border-transparent shadow-lg shadow-emerald-500/30 text-sm font-bold rounded-xl text-white bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all transform hover:-translate-y-0.5">
                    {{ loading ? 'Отправка...' : 'Отправить заявку' }}
                </button>
            </div>

        </form>
      </div>
    </div>
  </div>
</template>