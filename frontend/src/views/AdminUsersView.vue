<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-base font-semibold text-slate-900 font-sans">Пользователи</h1>
        <p class="mt-2 text-sm text-slate-700">Список всех пользователей системы: имя, телефон, ИНН и статус.</p>
      </div>
      <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
         <!-- Potential 'Add User' button here -->
      </div>
    </div>
    <div class="mt-8 flow-root">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
          <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
            <table class="min-w-full divide-y divide-slate-300">
              <thead class="bg-slate-50">
                <tr>
                  <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-slate-900 sm:pl-6">Имя</th>
                  <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Телефон</th>
                  <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">ИНН</th>
                  <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Роль</th>
                  <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Статус</th>
                  <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                    <span class="sr-only">Редактировать</span>
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 bg-white">
                <tr v-for="user in users" :key="user.id">
                  <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-slate-900 sm:pl-6">{{ user.name }}</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500 font-mono">{{ user.phone }}</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500 font-mono">{{ user.inn || '-' }}</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500 bg-slate-50 rounded-md inline-block my-3 px-2 py-0.5 border border-slate-200 text-xs uppercase tracking-wide">{{ user.role }}</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm">
                    <span v-if="user.is_active" class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Активен</span>
                    <span v-else class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">Ожидает</span>
                  </td>
                  <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                    <button v-if="!user.is_active" @click="activateUser(user.id)" class="text-sky-600 hover:text-sky-900 mr-4 font-semibold transition-colors">Активировать</button>
                    <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-900 font-semibold transition-colors">Удалить</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface User {
    id: number;
    name: string;
    phone: string;
    inn: string;
    role: string;
    is_active: boolean;
}

const users = ref<User[]>([])

const fetchUsers = async () => {
    try {
        const response = await fetch('/api/admin/users', {
            headers: { 'Accept': 'application/json' }
        });
        if (response.ok) {
            const data = await response.json();
            users.value = data.data; 
        } else {
             // Mock fallback
             users.value = [
                 { id: 1, name: 'Admin User', phone: '+79990000000', inn: '', role: 'admin', is_active: true },
                 { id: 2, name: 'Ivan Petrov', phone: '+79991112233', inn: '7701234567', role: 'client', is_active: false },
             ]
        }
    } catch (e) {
        console.error(e)
    }
}

const activateUser = async (id: number) => {
    await fetch(`/api/admin/users/${id}/activate`, {
        method: 'PATCH',
        headers: { 'Accept': 'application/json' }
    });
    fetchUsers();
}

const deleteUser = async (id: number) => {
    if(!confirm('Вы уверены?')) return;
    await fetch(`/api/admin/users/${id}`, {
        method: 'DELETE',
        headers: { 'Accept': 'application/json' }
    });
    fetchUsers();
}

onMounted(() => {
    fetchUsers()
})
</script>
