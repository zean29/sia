<template>
  <div class="unauthorized-page">
    <h1>401 - Tidak Memiliki Akses</h1>
    <p>Anda tidak memiliki akses ke halaman ini.</p>
    <router-link to="/dashboard">Kembali ke Dashboard</router-link>
  </div>
</template>

<script setup lang="ts">
// 401 page component
</script>

<style scoped>
.unauthorized-page {
  padding: 24px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  text-align: center;
}
</style>