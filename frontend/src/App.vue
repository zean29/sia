<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'

const authStore = useAuthStore()

onMounted(async () => {
  // Check for existing authentication token
  const token = localStorage.getItem('auth_token')
  if (token) {
    try {
      console.log('App: Found existing token, checking auth')
      await authStore.checkAuth()
      console.log('App: Auth check successful')
    } catch (error) {
      console.error('App: Auth check failed:', error)
      // Clear invalid token but don't automatically logout
      localStorage.removeItem('auth_token')
    }
  }
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