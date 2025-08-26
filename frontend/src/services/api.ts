import axios from 'axios'
import type { AxiosInstance, AxiosResponse, AxiosError } from 'axios'

// Create axios instance
export const api: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// Request interceptor
api.interceptors.request.use(
  (config) => {
    // Add auth token if available
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    // Add CSRF token if available
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (csrfToken) {
      config.headers['X-CSRF-TOKEN'] = csrfToken
    }

    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response: AxiosResponse) => {
    return response
  },
  async (error: AxiosError) => {
    const originalRequest = error.config as any

    // Handle 401 Unauthorized
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true
      
      // Clear auth data
      localStorage.removeItem('auth_token')
      delete api.defaults.headers.common['Authorization']
      
      // Redirect to login if not already there
      if (window.location.pathname !== '/login') {
        const currentPath = encodeURIComponent(window.location.pathname + window.location.search)
        window.location.href = `/login?redirect=${currentPath}`
      }
    }

    // Handle 403 Forbidden
    if (error.response?.status === 403) {
      console.error('Access denied:', error.response.data?.pesan)
    }

    // Handle network errors
    if (!error.response) {
      console.error('Network error:', error.message)
    }

    return Promise.reject(error)
  }
)

// API service methods
export const apiService = {
  // Authentication
  auth: {
    login: (credentials: { email: string; kata_sandi: string }) =>
      api.post('/autentikasi/masuk', credentials),
    logout: () => api.post('/autentikasi/keluar'),
    me: () => api.get('/autentikasi/saya'),
    changePassword: (data: any) => api.put('/autentikasi/ubah-kata-sandi', data),
    forgotPassword: (email: string) => 
      api.post('/autentikasi/lupa-kata-sandi', { email }),
    resetPassword: (data: any) => api.post('/autentikasi/reset-kata-sandi', data)
  },

  // Dashboard
  dashboard: {
    get: () => api.get('/dashboard')
  },

  // Mahasiswa
  mahasiswa: {
    list: (params?: any) => api.get('/mahasiswa', { params }),
    get: (id: number) => api.get(`/mahasiswa/${id}`),
    create: (data: any) => api.post('/mahasiswa', data),
    update: (id: number, data: any) => api.put(`/mahasiswa/${id}`, data),
    delete: (id: number) => api.delete(`/mahasiswa/${id}`),
    transkrip: (id: number) => api.get(`/mahasiswa/${id}/transkrip`),
    khs: (id: number) => api.get(`/mahasiswa/${id}/kartu-hasil-studi`),
    uploadDokumen: (id: number, data: FormData) => 
      api.post(`/mahasiswa/${id}/upload-dokumen`, data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
  },

  // Dosen
  dosen: {
    list: (params?: any) => api.get('/dosen', { params }),
    get: (id: number) => api.get(`/dosen/${id}`),
    create: (data: any) => api.post('/dosen', data),
    update: (id: number, data: any) => api.put(`/dosen/${id}`, data),
    delete: (id: number) => api.delete(`/dosen/${id}`),
    jadwalMengajar: (id: number) => api.get(`/dosen/${id}/jadwal-mengajar`)
  },

  // Mata Kuliah
  mataKuliah: {
    list: (params?: any) => api.get('/mata-kuliah', { params }),
    get: (id: number) => api.get(`/mata-kuliah/${id}`),
    create: (data: any) => api.post('/mata-kuliah', data),
    update: (id: number, data: any) => api.put(`/mata-kuliah/${id}`, data),
    delete: (id: number) => api.delete(`/mata-kuliah/${id}`),
    cekPrasyarat: (id: number) => api.get(`/mata-kuliah/${id}/cek-prasyarat`),
    tersediaSekarang: () => api.get('/mata-kuliah/tersedia-sekarang')
  },

  // Jadwal Kelas
  jadwalKelas: {
    list: (params?: any) => api.get('/jadwal-kelas', { params }),
    get: (id: number) => api.get(`/jadwal-kelas/${id}`),
    create: (data: any) => api.post('/jadwal-kelas', data),
    update: (id: number, data: any) => api.put(`/jadwal-kelas/${id}`, data),
    delete: (id: number) => api.delete(`/jadwal-kelas/${id}`),
    ambil: (id: number) => api.post(`/jadwal-kelas/${id}/ambil`),
    batalkan: (id: number) => api.delete(`/jadwal-kelas/${id}/batalkan`),
    setujui: (id: number, data?: any) => api.put(`/jadwal-kelas/${id}/setujui`, data),
    tolak: (id: number, data: any) => api.put(`/jadwal-kelas/${id}/tolak`, data)
  },

  // Nilai
  nilai: {
    list: (params?: any) => api.get('/nilai', { params }),
    get: (id: number) => api.get(`/nilai/${id}`),
    create: (data: any) => api.post('/nilai', data),
    update: (id: number, data: any) => api.put(`/nilai/${id}`, data),
    finalisasi: (id: number) => api.put(`/nilai/${id}/finalisasi`),
    revisi: (id: number, data: any) => api.put(`/nilai/${id}/revisi`, data)
  },

  // Pembayaran
  pembayaran: {
    list: (params?: any) => api.get('/pembayaran', { params }),
    get: (id: number) => api.get(`/pembayaran/${id}`),
    create: (data: any) => api.post('/pembayaran', data),
    update: (id: number, data: any) => api.put(`/pembayaran/${id}`, data),
    verifikasi: (id: number, data?: any) => api.put(`/pembayaran/${id}/verifikasi`, data),
    uploadBukti: (id: number, data: FormData) => 
      api.post(`/pembayaran/${id}/upload-bukti`, data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
  },

  // Laporan
  laporan: {
    mahasiswa: (params?: any) => api.get('/laporan/mahasiswa', { params }),
    akademik: (params?: any) => api.get('/laporan/akademik', { params }),
    keuangan: (params?: any) => api.get('/laporan/keuangan', { params }),
    statistik: (params?: any) => api.get('/laporan/statistik', { params })
  },

  // PDDIKTI
  pddikti: {
    syncMahasiswa: () => api.post('/pddikti/sync-mahasiswa'),
    syncDosen: () => api.post('/pddikti/sync-dosen'),
    syncMataKuliah: () => api.post('/pddikti/sync-mata-kuliah'),
    syncNilai: () => api.post('/pddikti/sync-nilai'),
    statusSync: () => api.get('/pddikti/status-sync')
  }
}

// Utility functions
export const downloadFile = async (url: string, filename?: string) => {
  try {
    const response = await api.get(url, { responseType: 'blob' })
    
    const blob = new Blob([response.data])
    const downloadUrl = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = downloadUrl
    link.download = filename || 'download'
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(downloadUrl)
  } catch (error) {
    console.error('Download failed:', error)
    throw error
  }
}

export const uploadFile = async (file: File, endpoint: string) => {
  const formData = new FormData()
  formData.append('file', file)
  
  return api.post(endpoint, formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
}

export default api