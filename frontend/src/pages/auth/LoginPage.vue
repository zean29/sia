<template>
  <div class="login-page">
    <form @submit.prevent="handleLogin" class="login-form">
      <h2 class="form-title">Masuk ke Akun Anda</h2>
      
      <!-- Show error message if login fails -->
      <div v-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>
      
      <!-- Show success message -->
      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>
      
      <div class="form-group">
        <label for="email" class="form-label">Email</label>
        <input
          id="email"
          v-model="loginForm.email"
          type="email"
          class="form-input"
          placeholder="Masukkan email Anda"
          required
        />
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Kata Sandi</label>
        <input
          id="password"
          v-model="loginForm.password"
          type="password"
          class="form-input"
          placeholder="Masukkan kata sandi"
          required
        />
      </div>

      <div class="form-actions">
        <button type="submit" class="login-btn" :disabled="isLoading">
          {{ isLoading ? 'Sedang masuk...' : 'Masuk' }}
        </button>
      </div>

      <div class="form-links">
        <router-link to="/auth/forgot-password" class="forgot-link">
          Lupa kata sandi?
        </router-link>
      </div>
      
      <!-- Test accounts info -->
      <div class="test-accounts">
        <p class="test-title">Akun untuk Testing:</p>
        <div class="test-account">
          <strong>Admin:</strong> superadmin@sia.ac.id / superadmin123
        </div>
        <div class="test-account">
          <strong>Dosen:</strong> bambang.suharto@sia.ac.id / dosen123
        </div>
        <div class="test-account">
          <strong>Mahasiswa:</strong> ahmad.sutanto@student.sia.ac.id / mahasiswa123
        </div>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { useNotificationStore } from '../../stores/notification'

const authStore = useAuthStore()
const notificationStore = useNotificationStore()

const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const loginForm = ref({
  email: '',
  password: ''
})

const handleLogin = async () => {
  isLoading.value = true
  errorMessage.value = ''
  successMessage.value = ''
  
  try {
    console.log('LoginPage: Starting login with:', { email: loginForm.value.email })
    
    const result = await authStore.login({
      email: loginForm.value.email,
      kata_sandi: loginForm.value.password
    })
    
    console.log('LoginPage: Login result:', result)
    
    if (result.success) {
      console.log('LoginPage: Login successful, user role:', result.user?.peran)
      successMessage.value = 'Login berhasil! Mengalihkan ke dashboard...'
      
      // Clear form
      loginForm.value = { email: '', password: '' }
      
      // Show success notification
      notificationStore.success('Login berhasil', 'Selamat datang kembali!')
      
      // The auth store will handle redirection automatically
    } else {
      console.error('LoginPage: Login failed:', result.error)
      errorMessage.value = result.error || 'Login gagal. Silakan coba lagi.'
      notificationStore.error('Login gagal', result.error || 'Terjadi kesalahan saat login')
    }
  } catch (error: any) {
    console.error('LoginPage: Login exception:', error)
    errorMessage.value = error.message || 'Terjadi kesalahan saat login'
    notificationStore.error('Login gagal', error.message || 'Terjadi kesalahan saat login')
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.login-page {
  width: 100%;
}

.login-form {
  width: 100%;
}

.form-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #374151;
  margin: 0 0 24px 0;
  text-align: center;
}

.error-message {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-size: 0.9rem;
}

.success-message {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #166534;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-size: 0.9rem;
}

.form-group {
  margin-bottom: 20px;
}

.form-label {
  display: block;
  font-weight: 500;
  color: #374151;
  margin-bottom: 6px;
}

.form-input {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-actions {
  margin: 32px 0 24px 0;
}

.login-btn {
  width: 100%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 14px 24px;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: opacity 0.2s;
}

.login-btn:hover:not(:disabled) {
  opacity: 0.9;
}

.login-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.form-links {
  text-align: center;
  margin-bottom: 24px;
}

.forgot-link {
  color: #667eea;
  text-decoration: none;
  font-size: 0.9rem;
}

.forgot-link:hover {
  text-decoration: underline;
}

.test-accounts {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
  margin-top: 20px;
}

.test-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin: 0 0 12px 0;
}

.test-account {
  font-size: 0.8rem;
  color: #6b7280;
  margin-bottom: 6px;
  font-family: monospace;
  background: white;
  padding: 6px 8px;
  border-radius: 4px;
  border: 1px solid #e5e7eb;
}

.test-account:last-child {
  margin-bottom: 0;
}

.test-account strong {
  color: #374151;
}
</style>