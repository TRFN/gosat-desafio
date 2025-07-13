<template>
  <!-- Botão de tema no topo direito -->
    <div class="d-flex justify-content-end mb-3">
      <button @click="toggleTheme" class="btn btn-sm btn-outline-dark d-flex align-items-center gap-1">
        <i :class="theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon'"></i>
        {{ theme === 'dark' ? 'Claro' : 'Escuro' }}
      </button>
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
