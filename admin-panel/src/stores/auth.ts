import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi, type User } from '../api/auth'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(JSON.parse(localStorage.getItem('user') || 'null'))
  const token = ref<string | null>(localStorage.getItem('token'))
  const loading = ref(false)

  // Both user AND token must exist to be considered authenticated
  const isAuthenticated = computed(() => !!user.value && !!token.value)
  const isAdmin = computed(() => user.value?.type === 'admin')

  async function initialize() {
    // If user exists but token doesn't, clear stale user
    if (user.value && !token.value) {
      user.value = null
      localStorage.removeItem('user')
    }

    if (token.value && !user.value) {
      loading.value = true
      try {
        const res = await authApi.me()
        const u = res.data.data?.user ?? null
        if (u && u.type !== 'admin') {
          throw new Error('Student access denied')
        }
        user.value = u
        if (u) localStorage.setItem('user', JSON.stringify(u))
      } catch {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        token.value = null
        user.value = null
      } finally {
        loading.value = false
      }
    }
  }

  async function login(email: string, password: string) {
    const res = await authApi.login({ email, password })
    const { user: u, token: t } = res.data.data

    if (u && u.type !== 'admin') {
      throw new Error('Yalnız Admin hesabları daxil ola bilər.')
    }

    if (t) {
      localStorage.setItem('token', t)
      token.value = t
    }

    if (u) {
      localStorage.setItem('user', JSON.stringify(u))
      user.value = u
    }
  }

  function logout() {
    authApi.logout().catch(() => {})
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    user.value = null
    token.value = null
  }

  return { user, token, loading, isAuthenticated, initialize, login, logout }
})
