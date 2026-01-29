<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const phone = ref('')
const code = ref('')
const step = ref<'phone' | 'otp' | 'success'>('phone')
const loading = ref(false)
const error = ref('')
const router = useRouter()
const authStore = useAuthStore()

// Ссылка на DOM элемент инпута
const phoneInput = ref<HTMLInputElement | null>(null)
let autofillInterval: number | null = null

// Валидация
const isPhoneValid = computed(() => {
    // Проверка полной длины маски: +7 (XXX) XXX-XX-XX — 18 символов
    return phone.value.length === 18
})

const isOtpValid = computed(() => code.value.length >= 4)

const isInputInvalid = ref(false)

const processPhoneNumber = (inputValue: string) => {
    // Сбрасываем ошибку API при любом вводе
    if (error.value) error.value = ''

    let value = inputValue.replace(/\D/g, '') // Оставляем только цифры
    
    // Если пусто
    if (!value) {
        phone.value = ''
        isInputInvalid.value = false
        return
    }

    // Лимит 11 цифр (для РФ) или просто 18 символов маски
    value = value.substring(0, 11)

    // Если первая цифра 7, 8 или 9, считаем это российским номером -> Маска
    if (/^[789]/.test(value)) {
        isInputInvalid.value = false
        
        // Если начали с 9, добавим 7 вперед
        if (value[0] === '9') value = '7' + value
        // Если начали с 8, заменяем на 7 (стандарт +7)
        if (value[0] === '8') value = '7' + value.substring(1)
        
        // Гарантируем, что первая цифра 7
        if (value[0] !== '7') value = '7' + value
        
        // Обрезаем снова, так как могли добавить префикс
        value = value.substring(0, 11)

        // Формируем маску (Eager mode)
        let formattedValue = '+7'
        
        if (value.length > 1) {
            formattedValue += ' (' + value.substring(1, 4)
        }
        if (value.length >= 5) {
            formattedValue += ') ' + value.substring(4, 7)
        }
        if (value.length >= 8) {
            formattedValue += '-' + value.substring(7, 9)
        }
        if (value.length >= 10) {
            formattedValue += '-' + value.substring(9, 11)
        }
        
        if (value.length === 1) {
            formattedValue += ' ('
        }

        phone.value = formattedValue

        // Force DOM update if desync (crucial for autofill/paste)
        if (phoneInput.value && phoneInput.value.value !== formattedValue) {
             phoneInput.value.value = formattedValue
        }

    } else {
        // Если первая цифра НЕ 7, 8, 9 -> Красный ввод, без маски (просто цифры)
        isInputInvalid.value = true
        phone.value = value
        
        // Force DOM update here too
        if (phoneInput.value && phoneInput.value.value !== value) {
             phoneInput.value.value = value
        }
    }
}

const handlePhoneInput = (event: Event) => {
    const input = event.target as HTMLInputElement
    processPhoneNumber(input.value)
}

// Удаление последнего символа корректно
const handlePhoneKeydown = (e: KeyboardEvent) => {
    // Разрешаем backspace, delete, стрелки, таб и ENTER
    if (['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Enter'].includes(e.key)) return
    
    // Запрещаем ввод не цифр
    if (!/\d/.test(e.key) && !e.ctrlKey && !e.metaKey) {
        e.preventDefault()
    }
    
    // Доп. проверка на лимит символов при вводе
    const maxLength = isInputInvalid.value ? 11 : 18
    if (phone.value.length >= maxLength && /\d/.test(e.key) && !window.getSelection()?.toString()) {
         e.preventDefault()
    }
}


const handlePhoneSubmit = async () => {
    // Если нажали Enter, но телефон не валиден, ничего не делаем (ошибка отобразится)
    // Но input type="tel" в форме и кнопка submit сработают.
  error.value = ''
  if (!isPhoneValid.value) {
     // Можно не показывать ошибку если просто пусто, но тут лучше показать
      error.value = 'Введите корректный номер телефона'
      // Возвращаем фокус на поле
      phoneInput.value?.focus()
      return
  }
  
  loading.value = true
  try {
    // Имитация задержки API
    await new Promise(r => setTimeout(r, 600))
    
    // Демонстрационный бэкдор
    if (phone.value.includes('000-00-00')) { // +7 (999) 000-00-00
        step.value = 'success'
    } else {
        await authStore.login(phone.value)
        router.push('/dashboard')
    }
  } catch (e) {
      error.value = 'Ошибка отправки SMS. Попробуйте еще раз.'
      // Возвращаем фокус на поле при ошибке
      phoneInput.value?.focus()
  } finally {
      loading.value = false
  }
}

const handleOtpSubmit = async () => {
    error.value = ''
    if(!isOtpValid.value) {
        error.value = 'Код должен содержать 4 цифры'
        return
    }

    loading.value = true
    setTimeout(() => {
        loading.value = false
        router.push('/dashboard')
    }, 800)
}

// Проверка на автозаполнение
const checkAutofill = () => {
    if (phoneInput.value && phoneInput.value.value !== phone.value) {
        // Если значение в инпуте отличается от стейта (например автозаполнение)
        // И оно не пустое
        if (phoneInput.value.value) {
             processPhoneNumber(phoneInput.value.value)
        }
    }
}

// Автофокус при загрузке
onMounted(() => {
    if (step.value === 'phone' && phoneInput.value) {
        phoneInput.value.focus()
    }
    
    // Запускаем поллинг для отлова автозаполнения (каждые 100мс)
    autofillInterval = window.setInterval(checkAutofill, 100)
})

onUnmounted(() => {
    if (autofillInterval) clearInterval(autofillInterval)
})
</script>

<template>
  <div>
      <!-- Карточка формы: используем card класс -->
      <div class="card py-10 px-6 sm:px-12 bg-white">
        
        <!-- Логотип и заголовок ВНУТРИ карточки (Лого слева) -->
        <div class="flex items-center justify-center mb-8 gap-4">
            <img src="/favicon.webp" alt="Логотип" class="h-12 w-12" />
            <div class="text-left">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 leading-tight">Новосталь-М</h1>
                <p class="text-sm text-slate-500">портал управления заявками</p>
            </div>
        </div>

        <!-- Шаг 1: Ввод телефона -->
        <div v-if="step === 'phone'">
            <h3 class="text-lg font-semibold leading-6 mb-6 text-center text-slate-900">Авторизация</h3>
            
            <form class="space-y-6" @submit.prevent="handlePhoneSubmit">
                <div>
                    <label for="phone" class="block text-sm font-medium mb-1.5 text-slate-700">Номер телефона</label>
                    <input 
                        ref="phoneInput"
                        id="phone" 
                        name="phone" 
                        type="tel" 
                        autocomplete="tel" 
                        required 
                        :value="phone"
                        @input="handlePhoneInput"
                        @change="handlePhoneInput"
                        @keydown="handlePhoneKeydown"
                        placeholder="+7 (999) 000-00-00" 
                        class="font-mono bg-white transition-all duration-200"
                        :style="{ '--autofill-color': (isPhoneValid && !isInputInvalid && !error) ? '#16a34a' : ((isInputInvalid || error) ? '#dc2626' : '#0f172a') } as any"
                        :class="{
                            '!text-red-600': isInputInvalid || error,
                            '!text-green-600 font-bold drop-shadow-sm shadow-green-200': isPhoneValid && !isInputInvalid && !error,
                            '!text-slate-900': !isPhoneValid && !isInputInvalid && !error,
                            '!border-red-500 !shadow-[0_0_0_4px_rgba(239,68,68,0.3)]': error || isInputInvalid,
                            '!border-green-500 !shadow-[0_0_20px_rgba(34,197,94,0.6)]': isPhoneValid && !isInputInvalid && !error
                        }"
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
                        {{ loading ? 'Отправка...' : 'Получить код' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Шаг 2: Ввод OTP -->
        <div v-else-if="step === 'otp'">
            <h3 class="text-lg font-semibold leading-6 mb-2 text-center text-slate-900">Введите код</h3>
            <div class="mb-6 text-sm text-center text-slate-500">
                Код отправлен на <span class="font-mono font-medium text-slate-900">{{ phone }}</span>
            </div>

            <form class="space-y-6" @submit.prevent="handleOtpSubmit">
                <div>
                    <input 
                        id="code" 
                        name="code" 
                        type="text" 
                        autocomplete="one-time-code" 
                        required 
                        v-model="code" 
                        class="text-center font-mono tracking-[0.5em] text-2xl font-bold"
                        placeholder="0000"
                    />
                </div>

                <div v-if="error" class="text-sm text-center font-medium text-red-600 bg-red-50 p-2 rounded-lg border border-red-100">
                    {{ error }}
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button 
                        type="button" 
                        @click="step = 'phone'" 
                        class="flex w-full justify-center rounded-lg px-4 py-2.5 text-sm font-semibold shadow-sm transition-colors border bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100"
                    >
                        Назад
                    </button>
                    <button 
                        type="submit" 
                        :disabled="loading" 
                        class="btn-primary w-full"
                    >
                        {{ loading ? '...' : 'Войти' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Шаг 3: Успех -->
        <div v-else-if="step === 'success'" class="text-center py-2">
             <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full mb-6 bg-green-100">
                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            <h3 class="text-xl font-bold leading-6 mb-2 text-slate-900">Заявка принята!</h3>
            <p class="text-sm mb-8 text-slate-500">Менеджер свяжется с вами в ближайшее время.</p>
            <button 
                type="button" 
                @click="step = 'phone'; phone = ''" 
                class="text-sm font-semibold text-sky-700 hover:text-sky-800 transition-colors"
            >
                Вернуться на главную
            </button>
        </div>

      </div>
      
      <!-- Подвал -->
      <div class="mt-8 text-center">
        <p class="text-xs text-slate-400">
            © 2026 Новосталь-М. Все права защищены.
        </p>
      </div>
  </div>
</template>

<style scoped>
/* Override Chrome/Safari autofill styling to match our validation colors */
input:-webkit-autofill,
input:-webkit-autofill:hover, 
input:-webkit-autofill:focus, 
input:-webkit-autofill:active {
    -webkit-text-fill-color: var(--autofill-color, #0f172a) !important;
    -webkit-box-shadow: 0 0 0px 1000px white inset !important;
    transition: background-color 5000s ease-in-out 0s;
}
</style>