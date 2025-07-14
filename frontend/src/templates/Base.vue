<template>
  <!-- Botão de tema no topo direito -->
    <div class="d-flex position-absolute top-0 end-0 p-5">
      <button @click="toggleTheme" :class="theme === 'dark' ? 'btn-warning' : 'btn-dark'" class="btn btn-sm d-flex align-items-center gap-1 px-3">
        <i :class="theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon'" class="me-1"></i>
        {{ theme === 'dark' ? 'Claro' : 'Escuro' }}
      </button>
    </div>

  <!-- logo -->
  <div class="text-center mb-4">
    <img src="/gosat.webp" alt="Logo" class="img-fluid">
  </div>
  <router-view />
</template>

<style scoped>


</style>

<script setup>
import { ref, onMounted } from 'vue'

const theme = ref('light')

onMounted(() => {
  const saved = localStorage.getItem('theme')
  if (saved === 'dark') {
    theme.value = 'dark'
    document.body.setAttribute('data-bs-theme', 'dark')
  }
})

function toggleTheme() {
  theme.value = theme.value === 'dark' ? 'light' : 'dark'
  document.body.setAttribute('data-bs-theme', theme.value)
  localStorage.setItem('theme', theme.value)
}
</script>
