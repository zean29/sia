<template>
  <div class="main-layout">
    <header class="main-header">
      <div class="header-content">
        <div class="header-left">
          <h1 class="app-title">SIA Universitas</h1>
        </div>
        <div class="header-right">
          <nav class="main-nav">
            <router-link to="/dashboard" class="nav-link">Dashboard</router-link>
            <div class="user-menu">
              <span class="user-name">{{ authStore.user?.nama_pengguna || 'User' }}</span>
              <button @click="logout" class="logout-btn">Logout</button>
            </div>
          </nav>
        </div>
      </div>
    </header>
    
    <main class="main-content">
      <div class="content-container">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const logout = async () => {
  try {
    console.log('MainLayout: Logging out user')
    await authStore.logout()
    // Auth store will handle the redirect
  } catch (error) {
    console.error('MainLayout: Logout failed:', error)
    // Force redirect to login on error
    router.replace('/auth/login')
  }
}
</script>

<style scoped>
.main-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-header {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 64px;
}

.app-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #667eea;
  margin: 0;
}

.main-nav {
  display: flex;
  align-items: center;
  gap: 20px;
}

.nav-link {
  color: #374151;
  text-decoration: none;
  font-weight: 500;
  padding: 8px 16px;
  border-radius: 6px;
  transition: all 0.2s;
}

.nav-link:hover,
.nav-link.router-link-active {
  background: #667eea;
  color: white;
}

.user-menu {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-name {
  font-weight: 500;
  color: #374151;
}

.logout-btn {
  background: #ef4444;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.logout-btn:hover {
  background: #dc2626;
}

.main-content {
  flex: 1;
  background: #f9fafb;
}

.content-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}
</style>