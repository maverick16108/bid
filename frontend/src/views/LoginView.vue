<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const phone = ref('')
const code = ref('')
const step = ref<'phone' | 'otp' | 'success'>('phone')
const loading = ref(false)
const router = useRouter()
const authStore = useAuthStore()

const handlePhoneSubmit = async () => {
  loading.value = true
  try {
    // In MVP, we just proceed to login
    // In real app, we would send SMS here
    if (phone.value.endsWith('000')) {
        step.value = 'success'
    } else {
        await authStore.login(phone.value)
        router.push('/dashboard')
    }
  } catch (e) {
      alert('Ошибка входа')
  } finally {
      loading.value = false
  }
}

const handleOtpSubmit = async () => {
    loading.value = true
    setTimeout(() => {
        loading.value = false
        router.push('/dashboard')
    }, 1000)
}
</script>

<template>
  <div class="sm:mx-auto sm:w-full sm:max-w-md">
     <div class="bg-white py-10 px-6 shadow-[0_20px_50px_rgba(8,_112,_184,_0.1)] rounded-2xl border border-gray-100 sm:px-10 relative overflow-hidden">
        <!-- Decorative element -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
        
        <div v-if="step === 'phone'">
            <h3 class="text-xl font-bold text-gray-900 mb-6 text-center">Вход в систему</h3>
            <form class="space-y-6" @submit.prevent="handlePhoneSubmit">
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Номер телефона</label>
                <div class="relative mt-1 rounded-md shadow-sm">
                    <input 
                        id="phone" 
                        name="phone" 
                        type="tel" 
                        autocomplete="tel" 
                        required 
                        v-model="phone" 
                        placeholder="+7 (999) 000-00-00" 
                        class="block w-full h-12 rounded-lg border-gray-300 pl-4 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-all duration-200 border shadow-sm placeholder:text-gray-400" 
                    />
                </div>
            </div>

            <div>
                <button type="submit" :disabled="loading" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg shadow-indigo-500/30 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-70 disabled:cursor-not-allowed transition-all duration-200 transform hover:-translate-y-0.5">
                {{ loading ? 'Загрузка...' : 'Продолжить' }}
                </button>
            </div>
            </form>
        </div>

        <div v-else-if="step === 'otp'">
            <h3 class="text-xl font-bold text-gray-900 mb-2 text-center">Подтверждение</h3>
            <div class="mb-6 text-center text-sm text-gray-500">
                Код отправлен на {{ phone }}
            </div>
            <form class="space-y-6" @submit.prevent="handleOtpSubmit">
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Код из SMS</label>
                <div class="mt-1">
                    <input 
                        id="code" 
                        name="code" 
                        type="text" 
                        autocomplete="one-time-code" 
                        required 
                        v-model="code" 
                        class="block w-full h-12 rounded-lg border-gray-300 pl-4 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-all duration-200 border shadow-sm text-center tracking-[0.5em] text-lg font-bold" 
                    />
                </div>
            </div>

            <div>
                <button type="submit" :disabled="loading" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg shadow-indigo-500/30 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-70 disabled:cursor-not-allowed transition-all duration-200 transform hover:-translate-y-0.5">
                {{ loading ? 'Проверка...' : 'Войти' }}
                </button>
            </div>
            <div class="text-center">
                <button type="button" @click="step = 'phone'" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 transition-colors">
                    Изменить номер
                </button>
            </div>
            </form>
        </div>

        <div v-else-if="step === 'success'" class="text-center py-4">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-bold text-gray-900">Спасибо!</h3>
            <div class="mt-2 mb-6">
                <p class="text-sm text-gray-500">
                    Менеджер свяжется с вами для заключения договора.
                </p>
            </div>
            <div>
                <button type="button" @click="step = 'phone'; phone = ''" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 transition-colors">
                    Вернуться на главную
                </button>
            </div>
        </div>
     </div>
  </div>
</template>