<template>
	<div v-if="etapa === 1" class="container py-4">
		<h2 class="mb-4">Etapa 01</h2>

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

	<div v-if="etapa === 2" class="container py-4">
		<h2 class="mb-4">Etapa 02</h2>
		<p>Compare e escolha a melhor oferta</p>

		<div class="row row-cols-1 row-cols-md-2 g-4">
			<div class="col" v-for="(oferta, index) in ofertas" :key="index">
				<label :for="'oferta-' + index" class="card h-100 p-3 position-relative shadow-sm oferta-card"
					:class="{ 'border-primary': ofertaEscolhida === index }">
					<input type="radio" class="visually-hidden" :id="'oferta-' + index" :value="index"
						v-model="ofertaEscolhida" />
					<h5 class="card-title">{{ oferta.instituicao }}</h5>
					<p class="card-text">
						<strong>Modalidade:</strong> <span class="text-capitalize">{{ oferta.modalidade }}</span><br>
						<strong>Juros ao Mês:</strong> {{ (oferta.jurosMes * 100).toFixed(2) }}%<br>
						<strong>Parcelas:</strong> {{ oferta.QntParcelaMin }} a {{ oferta.QntParcelaMax }}<br>
						<strong>Valor:</strong> R$ {{ oferta.valorMin }} até R$ {{
							oferta.valorMax }}
					</p>

					<div>
						<input type="range" :min="oferta.valorMin" :max="oferta.valorMax"
							v-model.number="oferta.valorSelecionado" :step="100" class="form-range" />
						<div>
							Valor selecionado:
							<strong>R$ {{ oferta.valorSelecionado.toLocaleString() }}</strong>
						</div>
					</div>

					<canvas :id="'chart-' + index" height="150"></canvas>
				</label>
			</div>
		</div>

		<div class="mt-4 text-end">
			<!-- Botão de confirmar escolha -->
			<!-- Desabilitado se nenhuma oferta for escolhida -->
			<button class="btn btn-secondary me-2" @click="etapa = 1">
				<i class="bi bi-arrow-left me-2"></i> Voltar
			</button>
			<button class="btn btn-success" :disabled="ofertaEscolhida === null" @click="confirmarEscolha">
				<i class="bi bi-check-circle me-2"></i> Confirmar Escolha
			</button>
		</div>
	</div>


	<div v-if="etapa === 3" class="container py-4">
		<h2>Etapa 03</h2>

		<!-- Um obrigado avisando que a solicitação dele foi feita com sucesso! -->
		<div class="alert alert-success">
			<p>Obrigado! Sua solicitação foi feita com sucesso.</p>
		</div>

		<!-- voltar ao inicio -->
		<router-link to="/home" class="btn btn-primary btn-lg d-flex align-items-center gap-2">
			<i class="bi bi-home"></i>
			Voltar ao inicio
		</router-link>
	</div>
</template>

<script setup>

import { onMounted, nextTick, ref } from 'vue'
import api from '../services/api'
import Chart from 'chart.js/auto'
import { encontrarMelhorOferta } from '../services/gosatHelper'

const ofertaEscolhida = ref(null)

onMounted(async () => {
	await nextTick()
	desenharGraficos()
})

function desenharGraficos() {
	ofertas.value.forEach((oferta, index) => {
		const ctx = document.getElementById(`chart-${index}`)
		if (ctx) {
			new Chart(ctx, {
				type: 'bar',
				data: {
					labels: ['Juros (%)', 'Valor Máx (mil)', 'Parcelas'],
					datasets: [{
						label: 'Oferta',
						data: [
							(oferta.jurosMes * 100).toFixed(2),
							(oferta.valorMax / 1000).toFixed(2),
							oferta.QntParcelaMax
						],
						backgroundColor: ['#0d6efd', '#20c997', '#ffc107']
					}]
				},
				options: {
					responsive: true,
					plugins: { legend: { display: false } }
				}
			})
		}
	})
}

function confirmarEscolha() {
	etapa.value = 3
}

const cpf = ref('')
const erro = ref('')
const loading = ref(false)
const resultado = ref(null)
const etapa = ref(1)
const ofertas = ref([]);

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
		if (response.data.success) {
			let instituicoes = response.data.response.instituicoes || [];
			if (instituicoes.length === 0) {
				erro.value = 'Nenhuma oferta encontrada para o CPF informado.'
				return;
			}

			for (let i = 0; i < instituicoes.length; i++) {
				let _instituicao = instituicoes[i], _modalidades;
				resultado.value = JSON.stringify(_instituicao); // Debug

				ofertas.value[i] = structuredClone(_instituicao);

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

						ofertas.value[i].modalidades[j].oferta = _oferta.data.response;
					} catch (err) {
						console.warn('Erro ao buscar oferta:', err?.response?.data?.response || err.message);
					}
				}
			}
			ofertas.value = encontrarMelhorOferta(ofertas.value);
			resultado.value = JSON.stringify(ofertas.value); // Debug
			etapa.value = 2;
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

.oferta-card:hover {
	cursor: pointer;
	border: 2px solid #0d6efd;
}
</style>
