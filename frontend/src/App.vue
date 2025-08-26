<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'
import { useNotificationStore } from './stores/notification'

const authStore = useAuthStore()
const notificationStore = useNotificationStore()

onMounted(async () => {
  // Check for existing authentication token
  const token = localStorage.getItem('auth_token')
  if (token) {
    try {
      await authStore.checkAuth()
    } catch (error) {
      console.error('Auth check failed:', error)
      authStore.logout()
    }
  }

  // Initialize notifications
  notificationStore.initialize()
})
</script>

<style>
#app {
  font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  height: 100vh;
}

body {
  margin: 0;
  padding: 0;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Indonesian localization styles */
.text-indonesian {
  font-family: 'Roboto', 'Open Sans', sans-serif;
}

.currency-idr::before {
  content: 'Rp ';
}

.academic-year::after {
  content: ' TA';
}
</style>