<template>
  <div class="container py-5">
    <div class="text-center mb-5">
      <h1 class="display-5 fw-bold">Bem-vindo à API Gosat</h1>
      <p class="lead">
        Utilize os recursos abaixo para consultar ofertas financeiras ou acessar a documentação da API.
      </p>
    </div>

    <div class="d-flex justify-content-center gap-4">
      <router-link to="/consultar" class="btn btn-primary btn-lg d-flex align-items-center gap-2">
        <i class="bi bi-search"></i>
        Consultar Ofertas
      </router-link>

      <a href="http://localhost:7001/docs/" target="blank" class="btn btn-outline-secondary btn-lg d-flex align-items-center gap-2">
        <i class="bi bi-file-earmark-text"></i>
        Documentação
	  </a>
    </div>
  </div>
</template>

<script setup>
// Nada necessário por enquanto
</script>

<style scoped>
/* Sem estilos adicionais */
</style>
