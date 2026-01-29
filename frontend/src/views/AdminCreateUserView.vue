<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const loading = ref(false)
const error = ref('')

const name = ref('')
const email = ref('')
const phone = ref('')
const inn = ref('')
const organization_name = ref('')

const nameInput = ref<HTMLInputElement | null>(null)
const emailInput = ref<HTMLInputElement | null>(null)
const phoneInput = ref<HTMLInputElement | null>(null)

// Validation
const isNameValid = computed(() => name.value.length >= 2)
const isEmailValid = computed(() => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value))
const isInnValid = computed(() => inn.value.length >= 10)
const isOrgValid = computed(() => organization_name.value.length >= 3)
const isPhoneValid = computed(() => phone.value.length === 18) // +7 (XXX) XXX-XX-XX

const isFormValid = computed(() => {
    return isNameValid.value && 
           (!email.value || isEmailValid.value) && 
           isInnValid.value &&
           isOrgValid.value &&
           isPhoneValid.value
})

const touched = ref({
    name: false,
    email: false,
    phone: false,
    inn: false,
    organization_name: false
})

const serverErrors = ref<Record<string, string>>({})

// Phone Masking Logic
const processPhoneNumber = (inputValue: string) => {
    let value = inputValue.replace(/\D/g, '')
    
    if (!value) {
        phone.value = ''
        return
    }

    value = value.substring(0, 11)

    if (/^[789]/.test(value)) {
        if (value[0] === '9') value = '7' + value
        if (value[0] === '8') value = '7' + value.substring(1)
        if (value[0] !== '7') value = '7' + value
        
        value = value.substring(0, 11)

        let formattedValue = '+7'
        if (value.length > 1) formattedValue += ' (' + value.substring(1, 4)
        if (value.length >= 5) formattedValue += ') ' + value.substring(4, 7)
        if (value.length >= 8) formattedValue += '-' + value.substring(7, 9)
        if (value.length >= 10) formattedValue += '-' + value.substring(9, 11)
        if (value.length === 1) formattedValue += ' ('

        phone.value = formattedValue

        if (phoneInput.value && phoneInput.value.value !== formattedValue) {
             phoneInput.value.value = formattedValue
        }
    } else {
        phone.value = value
    }
}

const handlePhoneInput = (event: Event) => {
    const input = event.target as HTMLInputElement
    processPhoneNumber(input.value)
}

const handlePhoneKeydown = (e: KeyboardEvent) => {
    if (['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Enter'].includes(e.key)) return
    if (e.ctrlKey || e.metaKey) return
    if (!/\d/.test(e.key)) e.preventDefault()
    if (phone.value.length >= 18 && /\d/.test(e.key) && !window.getSelection()?.toString()) e.preventDefault()
}

const handleSubmit = async () => {
    touched.value = { 
        name: true, email: true, phone: true, inn: true, 
        organization_name: true
    }
    error.value = ''
    serverErrors.value = {}

    if (!isFormValid.value) return

    loading.value = true
    try {
        await axios.post('/api/admin/users', {
            name: name.value,
            email: email.value,
            phone: phone.value,
            inn: inn.value,
            organization_name: organization_name.value
        })
        router.push('/admin/users')
    } catch (e: any) {
        error.value = e.response?.data?.message || 'Ошибка создания пользователя'
        if (e.response?.status === 422 && e.response?.data?.errors) {
            const errors = e.response.data.errors
            if (errors.email) serverErrors.value.email = errors.email[0]
            if (errors.phone) serverErrors.value.phone = errors.phone[0]
            // We can also map others if needed, but these are the main unique fields
        }
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    // Clear inputs with delay/tick
    setTimeout(() => {
        name.value = ''
        email.value = ''
        phone.value = ''
        inn.value = ''
        organization_name.value = ''
        
        nameInput.value?.focus()
    }, 100)
    
    // Exit on Escape
    document.addEventListener('keydown', handleKeydown)
})

import { onUnmounted } from 'vue'

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
  <div class="max-w-2xl mx-auto py-8 px-4">



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
            <h1 class="text-lg font-semibold text-slate-800">Новый пользователь</h1>
        </div>

        <div class="px-8 py-8">
            <div class="text-center mb-8">
                <p class="text-slate-500">Заполните данные для создания учетной записи клиента</p>
            </div>

            <form class="space-y-6 max-w-lg mx-auto" @submit.prevent="handleSubmit">
                <!-- Dummy inputs -->
                <input type="text" style="display:none" />
                <input type="password" style="display:none" />

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
                        placeholder="Иван Иванов" 
                        class="form-input w-full rounded-lg border-slate-300 transition-shadow"
                        :class="{
                             'focus:border-indigo-500 focus:ring-indigo-500': !isNameValid,
                             '!text-red-600': touched.name && !isNameValid,
                             '!text-green-600 font-bold drop-shadow-sm shadow-green-200': isNameValid,
                             '!text-slate-900': !isNameValid && !touched.name,
                             '!border-red-500 !shadow-[0_0_0_4px_rgba(239,68,68,0.3)]': touched.name && !isNameValid,
                             '!border-green-500 !shadow-[0_0_20px_rgba(34,197,94,0.6)]': isNameValid
                        }"
                        @focus="touched.name = false"
                        @blur="touched.name = true"
                        :style="{ '--autofill-color': isNameValid ? '#16a34a' : ((touched.name && !isNameValid) ? '#dc2626' : '#0f172a') } as any"
                    />
                </div>

                <!-- Org Name -->
                <div>
                    <label for="org" class="block text-sm font-medium mb-1.5 text-slate-700">Наименование организации</label>
                    <input 
                        id="org" 
                        type="text" 
                        required 
                        autocomplete="off"
                        v-model="organization_name"
                        placeholder="ООО Новосталь-М" 
                        class="form-input w-full rounded-lg border-slate-300 transition-shadow"
                        :class="{
                             'focus:border-indigo-500 focus:ring-indigo-500': !isOrgValid,
                             '!text-red-600': touched.organization_name && !isOrgValid,
                             '!text-green-600 font-bold drop-shadow-sm shadow-green-200': isOrgValid,
                             '!text-slate-900': !isOrgValid && !touched.organization_name,
                             '!border-red-500 !shadow-[0_0_0_4px_rgba(239,68,68,0.3)]': touched.organization_name && !isOrgValid,
                             '!border-green-500 !shadow-[0_0_20px_rgba(34,197,94,0.6)]': isOrgValid
                        }"
                        @focus="touched.organization_name = false"
                        @blur="touched.organization_name = true"
                        :style="{ '--autofill-color': isOrgValid ? '#16a34a' : ((touched.organization_name && !isOrgValid) ? '#dc2626' : '#0f172a') } as any"
                    />
                </div>

                <!-- INN -->
                <div>
                    <label for="inn" class="block text-sm font-medium mb-1.5 text-slate-700">ИНН Организации</label>
                    <input 
                        id="inn" 
                        type="text" 
                        required 
                        autocomplete="off"
                        v-model="inn"
                        maxlength="12"
                        @input="inn = inn.replace(/\D/g, '')"
                        placeholder="7700000000" 
                        class="form-input w-full rounded-lg border-slate-300 transition-shadow"
                        :class="{
                             'focus:border-indigo-500 focus:ring-indigo-500': !isInnValid,
                             '!text-red-600': touched.inn && !isInnValid,
                             '!text-green-600 font-bold drop-shadow-sm shadow-green-200': isInnValid,
                             '!text-slate-900': !isInnValid && !touched.inn,
                             '!border-red-500 !shadow-[0_0_0_4px_rgba(239,68,68,0.3)]': touched.inn && !isInnValid,
                             '!border-green-500 !shadow-[0_0_20px_rgba(34,197,94,0.6)]': isInnValid
                        }"
                        @focus="touched.inn = false"
                        @blur="touched.inn = true"
                        :style="{ '--autofill-color': isInnValid ? '#16a34a' : ((touched.inn && !isInnValid) ? '#dc2626' : '#0f172a') } as any"
                    />
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium mb-1.5 text-slate-700">Телефон</label>
                    <input 
                        ref="phoneInput"
                        id="phone" 
                        type="tel" 
                        required 
                        autocomplete="off"
                        :value="phone"
                        @input="handlePhoneInput"
                        @keydown="handlePhoneKeydown"
                        @focus="touched.phone = false; serverErrors.phone = ''"
                        @blur="touched.phone = true"
                        placeholder="+7 (999) 000-00-00" 
                        class="form-input w-full rounded-lg border-slate-300 transition-shadow font-mono"
                        :class="{
                             'focus:border-indigo-500 focus:ring-indigo-500': !isPhoneValid && !serverErrors.phone,
                             '!text-red-600': (touched.phone && !isPhoneValid) || serverErrors.phone,
                             '!text-green-600 font-bold drop-shadow-sm shadow-green-200': isPhoneValid && !serverErrors.phone,
                             '!text-slate-900': !isPhoneValid && !touched.phone && !serverErrors.phone,
                             '!border-red-500 !shadow-[0_0_0_4px_rgba(239,68,68,0.3)]': (touched.phone && !isPhoneValid) || serverErrors.phone,
                             '!border-green-500 !shadow-[0_0_20px_rgba(34,197,94,0.6)]': isPhoneValid && !serverErrors.phone
                        }"
                        :style="{ '--autofill-color': (isPhoneValid && !serverErrors.phone) ? '#16a34a' : (((touched.phone && !isPhoneValid) || serverErrors.phone) ? '#dc2626' : '#0f172a') } as any"
                    />
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium mb-1.5 text-slate-700">Email (необязательно)</label>
                    <input 
                        ref="emailInput"
                        id="email" 
                        type="email" 
                        autocomplete="off"
                        v-model="email"
                        placeholder="client@company.com" 
                        class="form-input w-full rounded-lg border-slate-300 transition-shadow"
                        :class="{
                             'focus:border-indigo-500 focus:ring-indigo-500': (!email || isEmailValid) && !serverErrors.email,
                             '!text-red-600': (touched.email && email && !isEmailValid) || serverErrors.email,
                             '!text-green-600 font-bold drop-shadow-sm shadow-green-200': email && isEmailValid && !serverErrors.email,
                             '!text-slate-900': ((!email || !isEmailValid) && !touched.email && !serverErrors.email),
                             '!border-red-500 !shadow-[0_0_0_4px_rgba(239,68,68,0.3)]': (touched.email && email && !isEmailValid) || serverErrors.email,
                             '!border-green-500 !shadow-[0_0_20px_rgba(34,197,94,0.6)]': email && isEmailValid && !serverErrors.email
                        }"
                        @focus="touched.email = false; serverErrors.email = ''"
                        @blur="touched.email = true"
                        :style="{ '--autofill-color': (email && isEmailValid && !serverErrors.email) ? '#16a34a' : (((touched.email && email && !isEmailValid) || serverErrors.email) ? '#dc2626' : '#0f172a') } as any"
                    />
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
                        {{ loading ? 'Создание...' : 'Создать пользователя' }}
                    </button>
                </div>
            </form>
        </div>
      </div>
  </div>
</template>

<style scoped>
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
