<template>
  <div class="auth-layout">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <h1 class="auth-title">SIA Universitas</h1>
          <p class="auth-subtitle">Sistem Informasi Akademik</p>
        </div>
        <div class="auth-content">
          <router-view />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Auth layout for login and authentication pages
</script>

<style scoped>
.auth-layout {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.auth-container {
  width: 100%;
  max-width: 400px;
}

.auth-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.auth-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-align: center;
  padding: 40px 30px;
}

.auth-title {
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.auth-subtitle {
  font-size: 1rem;
  opacity: 0.9;
  margin: 0;
  font-weight: 300;
}

.auth-content {
  padding: 40px 30px;
}
</style>