<template>
	<div class="container py-4">
		<h2>Etapa 01</h2>

		<form @submit.prevent="consultarCpf">
			<div class="mb-3">
				<label for="cpf" class="form-label">Insira seu CPF abaixo para consultar as ofertas disponíveis</label>
				<input id="cpf" v-model="cpf" type="text" class="form-control"
					placeholder="Digite o CPF (somente números)" maxlength="11" @input="limparErro"
					:class="{ 'is-invalid': erro }" />
				<div class="invalid-feedback" v-if="erro">{{ erro }}</div>
			</div>

			<button type="submit" class="btn btn-primary" :disabled="loading">
				<i v-if="loading" class="bi bi-arrow-repeat spin me-2"></i>
				<i v-if="!loading" class="bi bi-search me-2"></i>
				{{ loading ? 'Consultando...' : 'Consultar' }}
			</button><br><br>
		</form>

		<div v-if="resultado" class="alert alert-success">
			<pre>{{ resultado }}</pre>
		</div>
	</div>
</template>

<script setup>

import { ref } from 'vue'
import api from '../services/api.js'

const cpf = ref('')
const erro = ref('')
const loading = ref(false)
const resultado = ref(null)

function limparErro(event) {
	erro.value = ''
	resultado.value = null
	cpf.value = event.target.value.replace(/\D/g, '')
}

function validarCpfBasico(cpfStr) {
	// Só permite números, e tem que ter 11 dígitos
	return /^\d{11}$/.test(cpfStr)
}

async function consultarCpf() {
	if (!validarCpfBasico(cpf.value)) {
		erro.value = 'CPF inválido. Deve conter 11 números.'
		return
	}

	loading.value = true
	erro.value = ''
	resultado.value = null

	try {
		const _cpf = cpf.value;
		const response = await api.get(`/consultarCpf/${_cpf}`)
		resultado.value = JSON.stringify(response.data); // Debug
		const ofertas = [];
		if (response.data.success) {
			let instituicoes = response.data.response.instituicoes || [];
			if (instituicoes.length === 0) {
				erro.value = 'Nenhuma oferta encontrada para o CPF informado.'
				return;
			}

			for (let i = 0; i < instituicoes.length; i++) {
				let _instituicao = instituicoes[i], _modalidades;
				resultado.value = JSON.stringify(_instituicao); // Debug

				ofertas[i] = structuredClone(_instituicao);

				_modalidades = _instituicao.modalidades || [];
				if (_modalidades.length === 0) {
					continue; // Pula se não tiver modalidades
				}

				for (let j = 0; j < _modalidades.length; j++) {
					let _modalidade = _modalidades[j];
					resultado.value = JSON.stringify(_modalidade); // Debug

					// Chamada assíncrona aguardada
					try {
						const _oferta = await api.post('/consultarOfertas', {
							cpf: _cpf,
							instituicao_id: _instituicao.id,
							codModalidade: _modalidade.cod
						});

						resultado.value = JSON.stringify(_oferta.data); // Debug

						ofertas[i].modalidades[j].oferta = _oferta.data.response;
					} catch (err) {
						console.warn('Erro ao buscar oferta:', err?.response?.data?.response || err.message);
					}
				}
			}
			resultado.value = JSON.stringify(ofertas);
		} else {
			erro.value = response.data.response || 'Erro ao consultar CPF.'
			return;
		}
	} catch (e) {
		if (e.response?.data?.response?.code === 422) {
			const rawMessage = e.response?.data?.response?.return || ''
			const match = rawMessage.match(/"([^"]+)"\s?$/)
			erro.value = match ? match[1].replace(/\\u00e3/, 'ã') : 'Erro ao consultar CPF.'
		} else {
			erro.value = e.response?.data?.response || e.message || 'Erro desconhecido ao consultar CPF.'
		}
	} finally {
		loading.value = false
	}
}
</script>

<style scoped>
pre {
	white-space: pre-wrap;
	word-break: break-word;
}

.spin {
	animation: spin 1s linear infinite;
	display: inline-block;
}

@keyframes spin {
	0% {
		transform: rotate(0deg);
	}

	100% {
		transform: rotate(360deg);
	}
}
</style>
