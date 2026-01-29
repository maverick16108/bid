<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminStore } from '@/stores/admin'

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')
const router = useRouter()
const adminStore = useAdminStore()

const emailInput = ref<HTMLInputElement | null>(null)
const passwordInput = ref<HTMLInputElement | null>(null)
let autofillInterval: number | null = null

// Validation logic
const isEmailValid = computed(() => {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)
})

const isPasswordValid = computed(() => {
    return password.value.length >= 6
})

const isEmailTouched = ref(false)
const isPasswordTouched = ref(false)

const processEmail = (inputValue: string) => {
    if (error.value) error.value = ''
    email.value = inputValue
    
    // Force DOM sync
     if (emailInput.value && emailInput.value.value !== inputValue) {
        emailInput.value.value = inputValue
    }
}

const processPassword = (inputValue: string) => {
    if (error.value) error.value = ''
    password.value = inputValue
     
    // Force DOM sync
    if (passwordInput.value && passwordInput.value.value !== inputValue) {
        passwordInput.value.value = inputValue
    }
}

const handleEmailInput = (event: Event) => {
    const input = event.target as HTMLInputElement
    processEmail(input.value)
}

const handlePasswordInput = (event: Event) => {
    const input = event.target as HTMLInputElement
    processPassword(input.value)
}

const checkAutofill = () => {
    // Email autofill check
    if (emailInput.value && emailInput.value.value !== email.value) {
        if (emailInput.value.value) processEmail(emailInput.value.value)
    }
    // Password autofill check
    if (passwordInput.value && passwordInput.value.value !== password.value) {
        if (passwordInput.value.value) processPassword(passwordInput.value.value)
    }
}

const handleSubmit = async () => {
    isEmailTouched.value = true
    isPasswordTouched.value = true
    error.value = ''

    if (!isEmailValid.value || !isPasswordValid.value) {
        if (!isEmailValid.value) emailInput.value?.focus()
        else if (!isPasswordValid.value) passwordInput.value?.focus()
        return
    }

    loading.value = true
    try {
        await adminStore.login({ email: email.value, password: password.value })
        router.push('/admin/dashboard')
    } catch (e: any) {
        error.value = e.response?.data?.message || 'Ошибка входа'
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    autofillInterval = window.setInterval(checkAutofill, 100)
    emailInput.value?.focus()
})

onUnmounted(() => {
    if (autofillInterval) clearInterval(autofillInterval)
})
</script>

<template>
  <div>
      <!-- Card Container -->
      <div class="card py-10 px-6 sm:px-12 bg-white">
        
        <!-- Logo & Title -->
        <div class="flex items-center justify-center mb-8 gap-4">
            <img src="/favicon.webp" alt="Логотип" class="h-12 w-12" />
            <div class="text-left">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 leading-tight">Новосталь-М</h1>
                <p class="text-sm text-slate-500">Вход для сотрудников</p>
            </div>
        </div>

        <form class="space-y-6" @submit.prevent="handleSubmit">
            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-medium mb-1.5 text-slate-700">Email</label>
                <input 
                    ref="emailInput"
                    id="email" 
                    name="email" 
                    type="email" 
                    autocomplete="email" 
                    required 
                    :value="email"
                    @input="handleEmailInput"
                    @change="handleEmailInput"
                    placeholder="sotrudnik@novostal.ru" 
                    class="font-sans bg-white transition-all duration-200"
                    :style="{ '--autofill-color': (isEmailValid && !error) ? '#16a34a' : ((isEmailTouched && !isEmailValid) || error ? '#dc2626' : '#0f172a') } as any"
                    :class="{
                        '!text-red-600': (isEmailTouched && !isEmailValid) || error,
                        '!text-green-600 font-bold drop-shadow-sm shadow-green-200': isEmailValid && !error,
                        '!text-slate-900': !isEmailValid && (!isEmailTouched || !error),
                        '!border-red-500 !shadow-[0_0_0_4px_rgba(239,68,68,0.3)]': (isEmailTouched && !isEmailValid) || error,
                        '!border-green-500 !shadow-[0_0_20px_rgba(34,197,94,0.6)]': isEmailValid && !error
                    }"
                    @blur="isEmailTouched = true"
                />
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-sm font-medium mb-1.5 text-slate-700">Пароль</label>
                <input 
                    ref="passwordInput"
                    id="password" 
                    name="password" 
                    type="password" 
                    autocomplete="current-password" 
                    required 
                    :value="password"
                    @input="handlePasswordInput"
                    @change="handlePasswordInput"
                    placeholder="••••••" 
                    class="font-sans bg-white transition-all duration-200"
                    :style="{ '--autofill-color': (isPasswordValid && !error) ? '#16a34a' : ((isPasswordTouched && !isPasswordValid) || error ? '#dc2626' : '#0f172a') } as any"
                    :class="{
                        '!text-red-600': (isPasswordTouched && !isPasswordValid) || error,
                        '!text-green-600 font-bold drop-shadow-sm shadow-green-200': isPasswordValid && !error,
                        '!text-slate-900': !isPasswordValid && (!isPasswordTouched || !error),
                        '!border-red-500 !shadow-[0_0_0_4px_rgba(239,68,68,0.3)]': (isPasswordTouched && !isPasswordValid) || error,
                        '!border-green-500 !shadow-[0_0_20px_rgba(34,197,94,0.6)]': isPasswordValid && !error
                    }"
                    @blur="isPasswordTouched = true"
                />
            </div>

            <div v-if="error" class="text-sm text-center font-medium text-red-600 bg-red-50 p-2 rounded-lg border border-red-100">
                {{ error }}
            </div>

            <div>
                <button 
                    type="submit" 
                    :disabled="loading" 
                    class="btn-primary w-full"
                >
                    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ loading ? 'Вход...' : 'Войти' }}
                </button>
            </div>
            
            <div class="text-center">
                <router-link to="/" class="text-sm font-semibold text-sky-700 hover:text-sky-800 transition-colors select-none">
                    Вернуться к входу для клиентов
                </router-link>
            </div>
        </form>
      </div>
      
       <div class="mt-8 text-center">
        <p class="text-xs text-slate-400">
            © 2026 Новосталь-М
        </p>
      </div>
  </div>
</template>

<style scoped>
/* Perfect Input Styles for Admin Login */
input:-webkit-autofill,
input:-webkit-autofill:hover, 
input:-webkit-autofill:focus, 
input:-webkit-autofill:active {
    -webkit-text-fill-color: var(--autofill-color, #0f172a) !important;
    -webkit-box-shadow: 0 0 0px 1000px white inset !important;
    transition: background-color 5000s ease-in-out 0s;
}
</style>
