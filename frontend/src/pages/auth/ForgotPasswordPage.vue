<template>
  <div class="forgot-password-page">
    <form @submit.prevent="handleForgotPassword" class="forgot-form">
      <h2 class="form-title">Lupa Kata Sandi</h2>
      <p class="form-description">
        Masukkan email Anda dan kami akan mengirimkan link untuk reset kata sandi.
      </p>
      
      <div class="form-group">
        <label for="email" class="form-label">Email</label>
        <input
          id="email"
          v-model="email"
          type="email"
          class="form-input"
          placeholder="Masukkan email Anda"
          required
        />
      </div>

      <div class="form-actions">
        <button type="submit" class="submit-btn" :disabled="isLoading">
          {{ isLoading ? 'Sedang mengirim...' : 'Kirim Link Reset' }}
        </button>
      </div>

      <div class="form-links">
        <router-link to="/auth/login" class="back-link">
          Kembali ke halaman login
        </router-link>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useNotificationStore } from '../../stores/notification'

const notificationStore = useNotificationStore()

const isLoading = ref(false)
const email = ref('')

const handleForgotPassword = async () => {
  isLoading.value = true
  
  try {
    // TODO: Implement forgot password API call
    await new Promise(resolve => setTimeout(resolve, 2000)) // Simulate API call
    notificationStore.success('Email terkirim', 'Link reset kata sandi telah dikirim ke email Anda')
    email.value = ''
  } catch (error: any) {
    notificationStore.error('Gagal mengirim email', error.message || 'Terjadi kesalahan')
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.forgot-password-page {
  width: 100%;
}

.forgot-form {
  width: 100%;
}

.form-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #374151;
  margin: 0 0 12px 0;
  text-align: center;
}

.form-description {
  color: #6b7280;
  text-align: center;
  margin: 0 0 24px 0;
  line-height: 1.5;
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

.submit-btn {
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

.submit-btn:hover:not(:disabled) {
  opacity: 0.9;
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.form-links {
  text-align: center;
}

.back-link {
  color: #667eea;
  text-decoration: none;
  font-size: 0.9rem;
}

.back-link:hover {
  text-decoration: underline;
}
</style>