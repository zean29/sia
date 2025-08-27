<template>
  <div class="not-found-page">
    <h1>404 - Halaman Tidak Ditemukan</h1>
    <p>Halaman yang Anda cari tidak ditemukan.</p>
    <router-link to="/dashboard">Kembali ke Dashboard</router-link>
  </div>
</template>

<script setup lang="ts">
// 404 page component
</script>

<style scoped>
.not-found-page {
  padding: 24px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  text-align: center;
}
</style>