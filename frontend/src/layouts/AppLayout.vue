<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'
import {
  Bars3Icon,
  XMarkIcon,
  HomeIcon,
  DocumentPlusIcon,
  ClipboardDocumentListIcon,
  UserIcon
} from '@heroicons/vue/24/outline'

const navigation = [
  { name: 'Дашборд', href: '/dashboard', icon: HomeIcon, current: true },
  { name: 'Создать заявку', href: '/create-order', icon: DocumentPlusIcon, current: false },
  { name: 'Мои заявки', href: '/orders', icon: ClipboardDocumentListIcon, current: false },
  { name: 'Профиль', href: '/profile', icon: UserIcon, current: false },
]

const sidebarOpen = ref(false)
const router = useRouter()

const navigate = (href: string) => {
  router.push(href)
  sidebarOpen.value = false
}
</script>

<template>
  <div class="h-full bg-gray-50">
    <TransitionRoot as="template" :show="sidebarOpen">
      <Dialog as="div" class="relative z-50 lg:hidden" @close="sidebarOpen = false">
        <TransitionChild as="template" enter="transition-opacity ease-linear duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="transition-opacity ease-linear duration-300" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 flex">
          <TransitionChild as="template" enter="transition ease-in-out duration-300 transform" enter-from="-translate-x-full" enter-to="translate-x-0" leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0" leave-to="-translate-x-full">
            <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1">
              <TransitionChild as="template" enter="ease-in-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in-out duration-300" leave-from="opacity-100" leave-to="opacity-0">
                <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                  <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                    <span class="sr-only">Закрыть меню</span>
                    <XMarkIcon class="h-6 w-6 text-white" aria-hidden="true" />
                  </button>
                </div>
              </TransitionChild>
              
              <!-- Mobile Sidebar -->
              <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-slate-900 px-6 pb-4 ring-1 ring-white/10">
                <div class="flex h-16 shrink-0 items-center justify-center border-b border-white/5">
                  <img class="h-8 w-auto" src="/logo/Logo_Novostal_white.webp" alt="Новосталь-М" />
                </div>
                <nav class="flex flex-1 flex-col">
                  <ul role="list" class="flex flex-1 flex-col gap-y-7">
                    <li>
                      <ul role="list" class="-mx-2 space-y-2">
                        <li v-for="item in navigation" :key="item.name">
                          <a @click.prevent="navigate(item.href)" 
                             :class="[
                               item.current 
                               ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-indigo-500/20' 
                               : 'text-slate-400 hover:text-white hover:bg-white/5',
                               'group flex gap-x-3 rounded-xl p-3 text-sm leading-6 font-medium cursor-pointer transition-all duration-200'
                             ]">
                            <component :is="item.icon" class="h-6 w-6 shrink-0" aria-hidden="true" />
                            {{ item.name }}
                          </a>
                        </li>
                      </ul>
                    </li>
                  </ul>
                </nav>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Desktop Sidebar -->
    <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
      <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-slate-900 px-6 pb-4 shadow-2xl">
        <div class="flex h-20 shrink-0 items-center justify-center border-b border-white/5">
           <img class="h-9 w-auto hover:opacity-90 transition-opacity" src="/logo/Logo_Novostal_white.webp" alt="Новосталь-М" />
        </div>
        <nav class="flex flex-1 flex-col mt-4">
          <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li>
              <ul role="list" class="-mx-2 space-y-2">
                <li v-for="item in navigation" :key="item.name">
                   <a @click.prevent="navigate(item.href)" 
                      :class="[
                        item.current 
                        ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-1 ring-white/10' 
                        : 'text-slate-400 hover:text-white hover:bg-white/5',
                        'group flex gap-x-3 rounded-xl p-3 text-sm leading-6 font-medium cursor-pointer transition-all duration-200'
                      ]">
                    <component :is="item.icon" 
                        :class="[item.current ? 'text-white' : 'text-slate-500 group-hover:text-white', 'h-6 w-6 shrink-0 transition-colors']" 
                        aria-hidden="true" />
                    {{ item.name }}
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </nav>
        <div class="text-xs text-slate-500 text-center pb-4">
            &copy; 2026 Novostal-M
        </div>
      </div>
    </div>

    <div class="lg:pl-72 transition-all duration-300">
      <!-- Top header with glassmorphism -->
      <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200/50 bg-white/80 backdrop-blur-md px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
        <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden" @click="sidebarOpen = true">
          <span class="sr-only">Открыть меню</span>
          <Bars3Icon class="h-6 w-6" aria-hidden="true" />
        </button>

        <div class="h-6 w-px bg-gray-900/10 lg:hidden" aria-hidden="true" />

        <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
            <!-- Breadcrumbs or Title could go here -->
          <div class="relative flex flex-1 items-center">
             <!-- Placeholder for future search/breadcrumbs -->
          </div>
          <div class="flex items-center gap-x-4 lg:gap-x-6">
            
            <!-- Separator -->
            <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-900/10" aria-hidden="true" />

            <!-- Profile dropdown -->
            <div class="relative">
              <button type="button" class="-m-1.5 flex items-center p-1.5 transition-opacity hover:opacity-80" id="user-menu-button">
                <span class="sr-only">Open user menu</span>
                <span class="hidden lg:flex lg:items-center">
                  <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold border border-indigo-200">
                      K
                  </div>
                  <span class="ml-4 text-sm font-semibold leading-6 text-gray-900" aria-hidden="true">Клиент</span>
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <main class="py-10 bg-gray-50 min-h-[calc(100vh-4rem)]">
        <div class="px-4 sm:px-6 lg:px-8">
          <slot></slot>
        </div>
      </main>
    </div>
  </div>
</template>
