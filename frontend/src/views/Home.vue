<template>
  <div class="container py-5">
    <div class="text-center mb-5">
      <h1 class="display-5 fw-bold">Consulta de ofertas gosat</h1>
      <p class="lead">
		Consulte as melhores ofertas disponíveis de forma simples e rápida ou acesse a documentação completa da API para integração. 
      </p>
    </div>

    <div class="d-flex justify-content-center gap-4">
      <router-link to="/consultar" class="btn btn-primary btn-lg d-flex align-items-center gap-2">
        <i class="bi bi-search"></i>
        Consultar Ofertas
      </router-link>

      <a href="http://localhost:7001/docs/" rel="noopener noreferrer" target="blank" class="btn btn-secondary btn-lg d-flex align-items-center gap-2">
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
