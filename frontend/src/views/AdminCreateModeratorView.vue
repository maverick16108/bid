<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

// ... existing code ...


import { useRouter } from 'vue-router'
import axios from 'axios'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const loading = ref(false)
const error = ref('')
const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')

const nameInput = ref<HTMLInputElement | null>(null)
const emailInput = ref<HTMLInputElement | null>(null)
const passwordInput = ref<HTMLInputElement | null>(null)


const fieldsReadonly = ref({
    name: true,
    email: true,
    password: true,
    confirmPassword: true
})

const isNameValid = computed(() => name.value.length >= 2)
const isEmailValid = computed(() => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value))
const isPasswordStrong = computed(() => {
    // Min 10 chars, uppercase, lowercase, number
    return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{10,}$/.test(password.value)
})
const doPasswordsMatch = computed(() => password.value === confirmPassword.value)

const isFormValid = computed(() => {
    return isNameValid.value && 
           isEmailValid.value && 
           isPasswordStrong.value && 
           doPasswordsMatch.value &&
           confirmPassword.value.length > 0
})

const touched = ref({
    name: false,
    email: false,
    password: false,
    confirmPassword: false
})

const serverErrors = ref<Record<string, string>>({})



const handleSubmit = async () => {
    touched.value = { name: true, email: true, password: true, confirmPassword: true }
    error.value = ''
    serverErrors.value = {}

    if (!isFormValid.value) return

    loading.value = true
    try {
        await axios.post('/api/admin/moderators', {
            name: name.value,
            email: email.value,
            password: password.value
        })
        router.push('/admin/moderators')
    } catch (e: any) {
        if (e.response?.status === 422 && e.response?.data?.errors) {
            const errors = e.response.data.errors
            if (errors.email) serverErrors.value.email = errors.email[0]
            // Map other fields if necessary
        }
        error.value = e.response?.data?.message || 'Ошибка создания модератора'
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    // Strictly clear inputs
    name.value = ''
    email.value = ''
    password.value = ''
    confirmPassword.value = ''
    
    // Auto-focus on name input with a slight delay to override aggressive autofill
    setTimeout(() => {
        nameInput.value?.focus()
    }, 100)

    document.addEventListener('keydown', handleKeydown)
})

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        router.back()
    }
}

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <div class="w-full max-w-2xl mx-auto py-8 px-8">
      <!-- Better Back Navigation -->



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
            <h1 class="text-lg font-semibold text-slate-800">Новый модератор</h1>
        </div>

        <div class="px-8 py-8">
            <div class="text-center mb-8">
                <p class="text-slate-500">Создание учетной записи сотрудника</p>
            </div>

            <form class="space-y-6 mx-auto" @submit.prevent="handleSubmit">
                <!-- Dummy inputs to fool browser autofill -->
                <input type="text" name="fakeusernameremembered" style="position: absolute; opacity: 0; pointer-events: none; height: 0; width: 0; z-index: -1;" tabindex="-1" />
                <input type="password" name="fakepasswordremembered" style="position: absolute; opacity: 0; pointer-events: none; height: 0; width: 0; z-index: -1;" tabindex="-1" />

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium mb-1.5 text-slate-700">Имя Фамилия</label>
                    <input 
                        ref="nameInput"
                        id="name" 
                        type="text" 
                        required 
                        autocomplete="off"
                        v-model="name"
                        :readonly="fieldsReadonly.name"
                        @focus="fieldsReadonly.name = false; touched.name = false"
                        placeholder="Петр Петров" 
                        class="form-input w-full rounded-lg border-slate-300 transition-shadow"
                        :class="{
                             'focus:border-indigo-500 focus:ring-indigo-500': !isNameValid || !touched.name,
                             '!text-red-600': touched.name && !isNameValid,
                             '!text-green-600 font-bold drop-shadow-sm shadow-green-200': touched.name && isNameValid,
                             '!text-slate-900': !touched.name,
                             '!border-red-500 !shadow-[0_0_0_4px_rgba(239,68,68,0.3)]': touched.name && !isNameValid,
                             '!border-green-500 !shadow-[0_0_20px_rgba(34,197,94,0.6)]': touched.name && isNameValid
                        }"
                        @blur="touched.name = true"
                        :style="{ '--autofill-color': (touched.name && isNameValid) ? '#16a34a' : ((touched.name && !isNameValid) ? '#dc2626' : '#0f172a') } as any"
                    />
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium mb-1.5 text-slate-700">Email (Логин)</label>
                    <input 
                        ref="emailInput"
                        id="email" 
                        type="email" 
                        required 
                        autocomplete="off"
                        v-model="email"
                        :readonly="fieldsReadonly.email"
                        @focus="fieldsReadonly.email = false; touched.email = false; serverErrors.email = ''"
                        placeholder="sotrudnik@novostal.ru" 
                        class="form-input w-full rounded-lg border-slate-300 transition-shadow"
                        :class="{
                             'focus:border-indigo-500 focus:ring-indigo-500': !isEmailValid && !serverErrors.email,
                             '!text-red-600': (touched.email && !isEmailValid) || serverErrors.email,
                             '!text-green-600 font-bold drop-shadow-sm shadow-green-200': isEmailValid && !serverErrors.email,
                             '!text-slate-900': !isEmailValid && !touched.email && !serverErrors.email,
                             '!border-red-500 !shadow-[0_0_0_4px_rgba(239,68,68,0.3)]': (touched.email && !isEmailValid) || serverErrors.email,
                             '!border-green-500 !shadow-[0_0_20px_rgba(34,197,94,0.6)]': isEmailValid && !serverErrors.email
                        }"
                        @blur="touched.email = true"
                        :style="{ '--autofill-color': (isEmailValid && !serverErrors.email) ? '#16a34a' : (((touched.email && !isEmailValid) || serverErrors.email) ? '#dc2626' : '#0f172a') } as any"
                    />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium mb-1.5 text-slate-700">Пароль</label>
                    <input 
                        ref="passwordInput"
                        id="password" 
                        type="password" 
                        required 
                        autocomplete="new-password"
                        v-model="password"
                        :readonly="fieldsReadonly.password"
                        @focus="fieldsReadonly.password = false; touched.password = false"
                        placeholder="••••••••" 
                        class="form-input w-full rounded-lg border-slate-300 transition-shadow"
                        :class="{
                             'focus:border-indigo-500 focus:ring-indigo-500': (!isPasswordStrong) || !touched.password,
                             '!text-red-600': touched.password && !isPasswordStrong,
                             '!text-green-600 font-bold drop-shadow-sm shadow-green-200': touched.password && isPasswordStrong,
                             '!text-slate-900': !touched.password,
                             '!border-red-500 !shadow-[0_0_0_4px_rgba(239,68,68,0.3)]': touched.password && !isPasswordStrong,
                             '!border-green-500 !shadow-[0_0_20px_rgba(34,197,94,0.6)]': touched.password && isPasswordStrong
                        }"
                        @blur="touched.password = true"
                        :style="{ '--autofill-color': (touched.password && isPasswordStrong) ? '#16a34a' : ((touched.password && !isPasswordStrong) ? '#dc2626' : '#0f172a') } as any"
                    />
                    <p class="mt-2 text-xs text-slate-500 flex items-start gap-1">
                        <span class="text-blue-500 font-bold">ℹ️</span>
                        Минимум 10 символов. Должен содержать большие и маленькие буквы, цифры.
                    </p>
                </div>
                
                <!-- Confirm Password -->
                <div>
                    <label for="confirmPassword" class="block text-sm font-medium mb-1.5 text-slate-700">Подтвердите пароль</label>
                    <input 
                        id="confirmPassword" 
                        type="password" 
                        required 
                        v-model="confirmPassword"
                        :readonly="fieldsReadonly.confirmPassword"
                        placeholder="••••••••" 
                        class="form-input w-full rounded-lg border-slate-300 transition-shadow"
                        :class="{
                             'focus:border-indigo-500 focus:ring-indigo-500': doPasswordsMatch && isPasswordStrong,
                             '!text-red-600': touched.confirmPassword && (!doPasswordsMatch || !isPasswordStrong),
                             '!text-green-600 font-bold drop-shadow-sm shadow-green-200': doPasswordsMatch && isPasswordStrong,
                             '!text-slate-900': (doPasswordsMatch && isPasswordStrong) && !touched.confirmPassword,
                             '!border-red-500 !shadow-[0_0_0_4px_rgba(239,68,68,0.3)]': touched.confirmPassword && (!doPasswordsMatch || !isPasswordStrong),
                             '!border-green-500 !shadow-[0_0_20px_rgba(34,197,94,0.6)]': doPasswordsMatch && isPasswordStrong
                        }"
                        @focus="fieldsReadonly.confirmPassword = false; touched.confirmPassword = false"
                        @blur="touched.confirmPassword = true"
                        :style="{ '--autofill-color': (doPasswordsMatch && isPasswordStrong) ? '#16a34a' : ((touched.confirmPassword && (!doPasswordsMatch || !isPasswordStrong)) ? '#dc2626' : '#0f172a') } as any"
                    />
                    <p v-if="touched.confirmPassword && !doPasswordsMatch" class="mt-1 text-xs text-red-600">Пароли не совпадают</p>
                    <p v-else-if="touched.confirmPassword && !isPasswordStrong" class="mt-1 text-xs text-red-600">Пароль недостаточно надежный</p>
                </div>

                <div v-if="error" class="text-sm text-center font-medium text-red-600 bg-red-50 p-3 rounded-xl border border-red-100">
                    {{ error }}
                </div>

                <div class="pt-4">
                    <button 
                        type="submit" 
                        :disabled="loading || !isFormValid" 
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-lg shadow-indigo-500/20 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none transition-all duration-200 transform hover:-translate-y-0.5"
                    >
                        <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ loading ? 'Создание...' : 'Создать модератора' }}
                    </button>
                </div>
            </form>
        </div>
      </div>
  </div>
</template>

<style scoped>
/* Reset some default styles to ensure neutral look initially */
input {
    outline: none;
}

/* Force validation colors override global styles */
.\!border-red-300 { border-color: #fca5a5 !important; }
.\!focus\:border-red-500:focus { border-color: #ef4444 !important; --tw-ring-color: #ef4444 !important; }
.\!focus\:ring-red-500:focus { box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.5) !important; }

.\!border-green-300 { border-color: #86efac !important; }
.\!focus\:border-green-500:focus { border-color: #22c55e !important; --tw-ring-color: #22c55e !important; }
.\!focus\:ring-green-500:focus { box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.5) !important; }

/* Fix autofill text color */
input:-webkit-autofill,
input:-webkit-autofill:hover, 
input:-webkit-autofill:focus, 
input:-webkit-autofill:active {
    -webkit-text-fill-color: var(--autofill-color, #0f172a) !important;
    -webkit-box-shadow: 0 0 0px 1000px white inset !important;
    transition: background-color 5000s ease-in-out 0s;
}
</style>
