import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api } from '../services/api'
import router from '../router'

export interface User {
  id: number
  nama_pengguna: string
  email: string
  peran: 'admin' | 'mahasiswa' | 'dosen' | 'staf'
  aktif: boolean
  data_mahasiswa?: any
  data_dosen?: any
  data_staf?: any
}

export interface LoginCredentials {
  email: string
  kata_sandi: string
}

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const userRole = computed(() => user.value?.peran || null)
  const isAdmin = computed(() => user.value?.peran === 'admin')
  const isMahasiswa = computed(() => user.value?.peran === 'mahasiswa')
  const isDosen = computed(() => user.value?.peran === 'dosen')
  const isStaf = computed(() => user.value?.peran === 'staf')

  // Actions
  const login = async (credentials: LoginCredentials) => {
    try {
      loading.value = true
      error.value = null

      console.log('Auth store: sending login request with', credentials)
      const response = await api.post('/autentikasi/masuk', credentials)
      console.log('Auth store: received response', response.data)
      
      if (response.data.sukses) {
        const { pengguna, token: authToken } = response.data.data
        
        console.log('Auth store: login successful, storing data')
        console.log('Auth store: user data:', pengguna)
        // Store authentication data
        user.value = pengguna
        token.value = authToken
        localStorage.setItem('auth_token', authToken)
        
        // Set default axios header
        api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
        
        console.log('Auth store: about to redirect after login')
        // Redirect based on role
        await redirectAfterLogin()
        
        console.log('Auth store: login process completed')
        return { success: true, user: pengguna }
      } else {
        throw new Error(response.data.pesan || 'Login gagal')
      }
    } catch (err: any) {
      console.error('Auth store: login error', err)
      const message = err.response?.data?.pesan || err.message || 'Terjadi kesalahan saat login'
      error.value = message
      return { success: false, error: message }
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      if (token.value) {
        await api.post('/autentikasi/keluar')
      }
    } catch (err) {
      console.error('Logout API call failed:', err)
    } finally {
      // Clear local state regardless of API call result
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
      delete api.defaults.headers.common['Authorization']
      
      // Redirect to login
      router.replace('/auth/login')
    }
  }

  const checkAuth = async () => {
    try {
      if (!token.value) {
        throw new Error('No token available')
      }

      // Set authorization header
      api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      
      const response = await api.get('/autentikasi/saya')
      
      if (response.data.sukses) {
        user.value = response.data.data
        return true
      } else {
        throw new Error('Auth check failed')
      }
    } catch (err) {
      // Clear invalid token
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
      delete api.defaults.headers.common['Authorization']
      throw err
    }
  }

  const changePassword = async (data: {
    kata_sandi_lama: string
    kata_sandi_baru: string
    kata_sandi_baru_confirmation: string
  }) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.put('/autentikasi/ubah-kata-sandi', data)
      
      if (response.data.sukses) {
        return { success: true, message: response.data.pesan }
      } else {
        throw new Error(response.data.pesan || 'Gagal mengubah kata sandi')
      }
    } catch (err: any) {
      const message = err.response?.data?.pesan || err.message || 'Terjadi kesalahan'
      error.value = message
      return { success: false, error: message }
    } finally {
      loading.value = false
    }
  }

  const forgotPassword = async (email: string) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.post('/autentikasi/lupa-kata-sandi', { email })
      
      if (response.data.sukses) {
        return { success: true, message: response.data.pesan }
      } else {
        throw new Error(response.data.pesan || 'Gagal mengirim reset password')
      }
    } catch (err: any) {
      const message = err.response?.data?.pesan || err.message || 'Terjadi kesalahan'
      error.value = message
      return { success: false, error: message }
    } finally {
      loading.value = false
    }
  }

  const redirectAfterLogin = async () => {
    const role = user.value?.peran
    const intendedRoute = router.currentRoute.value.query.redirect as string

    console.log('Auth store: redirectAfterLogin called with role:', role, 'intendedRoute:', intendedRoute)

    // Use nextTick to ensure the user state is properly set
    await new Promise(resolve => setTimeout(resolve, 100))

    if (intendedRoute) {
      console.log('Auth store: redirecting to intended route:', intendedRoute)
      router.replace(intendedRoute)
    } else {
      switch (role) {
        case 'admin':
          console.log('Auth store: redirecting admin to /admin/dashboard')
          router.replace('/admin/dashboard')
          break
        case 'mahasiswa':
          console.log('Auth store: redirecting mahasiswa to /mahasiswa/dashboard')
          router.replace('/mahasiswa/dashboard')
          break
        case 'dosen':
          console.log('Auth store: redirecting dosen to /dosen/dashboard')
          router.replace('/dosen/dashboard')
          break
        case 'staf':
          console.log('Auth store: redirecting staf to /staf/dashboard')
          router.replace('/staf/dashboard')
          break
        default:
          console.log('Auth store: redirecting to default /dashboard')
          router.replace('/dashboard')
      }
    }
  }

  const hasRole = (roles: string | string[]) => {
    if (!user.value) return false
    
    const userRole = user.value.peran
    if (Array.isArray(roles)) {
      return roles.includes(userRole)
    }
    return userRole === roles
  }

  const clearError = () => {
    error.value = null
  }

  // Initialize token in axios if available
  if (token.value) {
    api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }

  return {
    // State
    user,
    token,
    loading,
    error,
    
    // Getters
    isAuthenticated,
    userRole,
    isAdmin,
    isMahasiswa,
    isDosen,
    isStaf,
    
    // Actions
    login,
    logout,
    checkAuth,
    changePassword,
    forgotPassword,
    hasRole,
    clearError
  }
})