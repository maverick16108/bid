<script setup lang="ts">
import { useRouter } from 'vue-router'
import { TruckIcon, CubeIcon, ArrowRightIcon } from '@heroicons/vue/24/outline'

const router = useRouter()

const navigate = (path: string) => {
  router.push(path)
}
</script>

<template>
  <div class="max-w-5xl mx-auto">
    <div class="md:flex md:items-center md:justify-between mb-8">
      <div class="min-w-0 flex-1">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
          Создать заявку
        </h2>
        <p class="mt-2 text-sm text-gray-500">Выберите необходимый тип заявки для продолжения</p>
      </div>
    </div>
    
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      
      <!-- Card 1 -->
      <div @click="navigate('/create-order/armature')" 
           class="group relative flex flex-col justify-between rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition-all duration-300 hover:shadow-xl hover:shadow-indigo-500/10 hover:-translate-y-1 hover:ring-indigo-500/50 cursor-pointer overflow-hidden">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-indigo-50 opacity-50 blur-xl group-hover:bg-indigo-100 transition-colors"></div>
        
        <div>
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 shadow-md shadow-indigo-500/30 group-hover:scale-110 transition-transform duration-300">
                 <CubeIcon class="h-6 w-6 text-white" aria-hidden="true" />
            </div>
            <h3 class="mt-4 text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Заказ арматуры</h3>
            <p class="mt-2 text-sm text-gray-500 line-clamp-2">Оформление заказа продукции по согласованной спецификации.</p>
        </div>
        
        <div class="mt-6 flex items-center text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
            Перейти к оформлению
            <ArrowRightIcon class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
        </div>
      </div>

      <!-- Card 2 -->
      <div @click="navigate('/create-order/transport')" 
           class="group relative flex flex-col justify-between rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition-all duration-300 hover:shadow-xl hover:shadow-emerald-500/10 hover:-translate-y-1 hover:ring-emerald-500/50 cursor-pointer overflow-hidden">
         <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-emerald-50 opacity-50 blur-xl group-hover:bg-emerald-100 transition-colors"></div>

        <div>
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-md shadow-emerald-500/30 group-hover:scale-110 transition-transform duration-300">
                 <TruckIcon class="h-6 w-6 text-white" aria-hidden="true" />
            </div>
            <h3 class="mt-4 text-lg font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">Заказ транспорта</h3>
            <p class="mt-2 text-sm text-gray-500 line-clamp-2">Заявка на подачу транспорта для перевозки грузов.</p>
        </div>
        
        <div class="mt-6 flex items-center text-sm font-medium text-emerald-600 group-hover:text-emerald-700">
            Перейти к оформлению
            <ArrowRightIcon class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
        </div>
      </div>

    </div>
  </div>
</template>