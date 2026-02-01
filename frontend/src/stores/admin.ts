import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const API_URL = import.meta.env.VITE_API_URL || '/api'

interface AdminUser {
    id: number;
    name: string;
    email: string;
    role?: string;
    created_at?: string;
}

interface Bid {
    id: number;
    user_id: number;
    type: string;
    status: string;
    details: any;
    created_at: string;
    user?: AdminUser;
}

export const useAdminStore = defineStore('admin', () => {
  const token = ref(localStorage.getItem('admin_token') || '')
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }
  const user = ref<AdminUser | null>(null)

  // Persistence State
  const usersListState = ref({
      search: '',
      page: 1,
      sortField: 'created_at',
      sortDirection: 'desc',
      scrollY: 0
  })

  const moderatorsListState = ref({
      search: '',
      page: 1,
      sortField: 'created_at',
      sortDirection: 'desc',
      scrollY: 0
  })

  const bidsListState = ref({
      search: '',
      page: 1,
      sortField: 'created_at',
      sortDirection: 'desc',
      scrollY: 0
  })



  const isMaster = computed(() => user.value?.role === 'master' || user.value?.email === 'admin@novostal.ru')

  const setToken = (newToken: string) => {
    token.value = newToken
    localStorage.setItem('admin_token', newToken)
    axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
  }

  const clearToken = () => {
    token.value = ''
    user.value = null
    localStorage.removeItem('admin_token')
    delete axios.defaults.headers.common['Authorization']
  }

  const login = async (credentials: any) => {
    const { data } = await axios.post(`${API_URL}/admin/login`, credentials)
    setToken(data.token)
    user.value = data.user
    if(data.role) user.value!.role = data.role
    return data
  }

  const fetchMe = async () => {
    if (!token.value) return
    try {
        const { data } = await axios.get(`${API_URL}/admin/me`, {
            headers: { Authorization: `Bearer ${token.value}` }
        })
        user.value = data
        // If backend doesn't return role in 'me', we might need to persist it or infer it
        // For now master admin usually has specific email
    } catch (e) {
        clearToken()
    }
  }

  const logout = async () => {
      try {
        await axios.post(`${API_URL}/admin/logout`, {}, {
            headers: { Authorization: `Bearer ${token.value}` }
        })
      } catch(e) {}
      clearToken()
  }

  // Dashboard & Management Actions
  const fetchStats = async () => {
      const { data } = await axios.get(`${API_URL}/admin/stats`, {
           headers: { Authorization: `Bearer ${token.value}` }
      })
      return data
  }

  const fetchUsers = async (params: any = {}) => {
      const { data } = await axios.get(`${API_URL}/admin/users`, {
           headers: { Authorization: `Bearer ${token.value}` },
           params
      })
      return data
  }

  const deleteUser = async (id: number) => {
      await axios.delete(`${API_URL}/admin/users/${id}`, {
           headers: { Authorization: `Bearer ${token.value}` }
      })
  }

  const fetchBids = async (params: any = {}) => {
      const { data } = await axios.get(`${API_URL}/admin/bids`, {
           headers: { Authorization: `Bearer ${token.value}` },
           params
      })
      return data
  }

  const deleteBid = async (id: number) => {
      await axios.delete(`${API_URL}/admin/bids/${id}`, {
           headers: { Authorization: `Bearer ${token.value}` }
      })
  }

  const fetchModerators = async (params: any = {}) => {
      const { data } = await axios.get(`${API_URL}/admin/moderators`, {
           headers: { Authorization: `Bearer ${token.value}` },
           params
      })
      return data
  }

  const createModerator = async (modData: any) => {
      const { data } = await axios.post(`${API_URL}/admin/moderators`, modData, {
           headers: { Authorization: `Bearer ${token.value}` }
      })
      return data
  }

  const deleteModerator = async (id: number) => {
      await axios.delete(`${API_URL}/admin/moderators/${id}`, {
           headers: { Authorization: `Bearer ${token.value}` }
      })
  }

  // Attempt to re-fetch user if we have a token but no user data (page reload)
  if (token.value && !user.value) {
      fetchMe().catch(() => clearToken())
  }

  return {
    token,
    user,
    isMaster,
    login,
    logout,
    fetchMe,
    setToken,
    fetchStats,
    fetchUsers,
    deleteUser,
    fetchBids,
    deleteBid,
    fetchModerators,
    createModerator,
    deleteModerator,
    usersListState,
    moderatorsListState,
    bidsListState
  }
})
