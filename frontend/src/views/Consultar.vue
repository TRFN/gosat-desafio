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
		<div v-if="solicitacoesCpf.length > 0" class="alert alert-info mb-4">
			<h3>Solicitações já feitas para CPF: {{ cpf }}</h3>
			<SolicitacoesCPF :solicitacoes="solicitacoesCpf" @desfazer="desfazerPedido" />
		</div>
		<h3>Ofertas disponíveis</h3>
		<p>Compare e escolha a melhor oferta</p>
		<form @submit.prevent="confirmarEscolha">
			<div class="row row-cols-1 row-cols-md-2 g-4">
				<div class="col" v-for="(oferta, index) in ofertas.filter(oferta => !oferta.jaTemSolicitacao)"
					:key="index">
					<label :for="'oferta-' + index" class="card h-100 p-3 position-relative shadow-sm oferta-card"
						:class="{ 'border-primary': ofertaEscolhida === index }">
						<input type="radio" class="visually-hidden" :id="'oferta-' + index" :value="index"
							v-model="ofertaEscolhida" />
						<h5 class="card-title">{{ oferta.instituicao }}</h5>
						<OfertaCard :oferta="oferta" v-model:valorSelecionado="oferta.valorSelecionado"
							v-model:parcelasSelecionadas="oferta.parcelasSelecionadas" />
					</label>
				</div>
			</div>

			<div class="mt-4 text-end">
				<button class="btn btn-secondary me-2" @click.prevent="limparErro(); etapa = 1">
					<i class="bi bi-arrow-left me-2"></i> Voltar
				</button>
				<button type="submit" class="btn btn-success" :disabled="ofertaEscolhida === null">
					<i class="bi bi-check-circle me-2"></i> Confirmar Escolha
				</button>
			</div>

			<div v-if="erro" class="alert alert-danger mt-5">
				<pre>{{ erro }}</pre>
			</div>
		</form>
	</div>


	<div v-if="etapa === 3" class="container py-4">
		<h2>Etapa 03</h2>

		<!-- Um obrigado avisando que a solicitação dele foi feita com sucesso! -->
		<div class="alert alert-success">
			<p>Obrigado! Sua solicitação foi feita com sucesso.</p>
			<p>Detalhes da solicitação:</p>
			<ul class="list-unstyled text-start">
				<li><strong>Instituição:</strong> {{ solicitacaoRegistrada.instituicao }}</li>
				<li><strong>Modalidade:</strong> {{ solicitacaoRegistrada.modalidade }}</li>
				<li><strong>Valor:</strong> R$ {{ solicitacaoRegistrada.valor.toLocaleString() }}</li>
				<li><strong>Parcelas:</strong> {{ solicitacaoRegistrada.parcelas }}</li>
				<li><strong>Juros ao Mês:</strong> {{ (solicitacaoRegistrada.jurosMes * 100).toFixed(2) }}%</li>
			</ul>
		</div>

		<!-- voltar ao inicio -->
		<router-link to="/" class="btn btn-primary btn-lg d-flex align-items-center gap-2">
			<i class="bi bi-home"></i>
			Voltar ao inicio
		</router-link>
	</div>
</template>

<script setup>

import { ref } from 'vue'
import api from '../services/api'
import OfertaCard from '../models/OfertaCard.vue'
import SolicitacoesCPF from '../models/SolicitacoesCPF.vue'
import { encontrarMelhorOferta } from '../services/gosatHelper'
import Swal from 'sweetalert2/dist/sweetalert2.all.js'


const ofertaEscolhida = ref(null)

const cpf = ref('')
const erro = ref('')
const loading = ref(false)
const resultado = ref(null)
const etapa = ref(1)
const ofertas = ref([]);
const solicitacoesCpf = ref([])
const solicitacaoRegistrada = ref({})

async function desfazerPedido(id) {
	const confirmacao = await Swal.fire({
		title: 'Tem certeza?',
		text: 'Deseja realmente desfazer essa solicitação?',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Sim, desfazer',
		cancelButtonText: 'Cancelar'
	});

	if (!confirmacao.isConfirmed) return

	try {
		// Buscar a solicitação que será removida
		let solicitacao = solicitacoesCpf.value.find(s => s.id === id);

		ofertas.value = ofertas.value.map(oferta => {
			if (oferta.codModalidade === solicitacao.codModalidade && oferta.instituicao === solicitacao.instituicao) {
				oferta.jaTemSolicitacao = false; // Remove a marcação de solicitação
			}
			return oferta;
		});

		// Remove do banco
		await api.delete(`/solicitacoes/${id}`)

		// Remove da lista local
		solicitacoesCpf.value = solicitacoesCpf.value.filter(s => s.id !== id);

		Swal.fire('Pronto!', 'Solicitação desfeita com sucesso.', 'success')
	} catch (e) {
		console.error(e)
		Swal.fire('Erro!', 'Não foi possível desfazer a solicitação.', 'error')
	}
}


function limparErro(event = false) {
	erro.value = '';
	resultado.value = null;
	event ? (cpf.value = event.target.value.replace(/\D/g, '')) : (cpf.value = '');
	ofertaEscolhida.value = null;
	loading.value = false;
	resultado.value = null;
	ofertas.value = [];
	solicitacoesCpf.value = [];
}

function validarCpfBasico(cpfStr) {
	// Só permite números, e tem que ter 11 dígitos
	return /^\d{11}$/.test(cpfStr)
}

async function confirmarEscolha() {
	if (ofertaEscolhida.value === null) return

	const oferta = ofertas.value[ofertaEscolhida.value]
	const payload = {
		cpf: cpf.value,
		instituicao: oferta.instituicao,
		modalidade: oferta.modalidade,
		codModalidade: oferta.codModalidade,
		valor: oferta.valorSelecionado || oferta.valorMin,
		jurosMes: oferta.jurosMes,
		parcelas: oferta.parcelasSelecionadas || oferta.QntParcelaMax
	}

	try {
		const response = await api.post('/solicitarEmprestimo', payload)
		console.log('Solicitação registrada com sucesso:', response.data);
		if (!response.data.success) {
			erro.value = 'Erro ao registrar sua solicitação.'
			return
		}
		solicitacaoRegistrada.value = response.data.response || {}
		etapa.value = 3
	} catch (error) {
		console.error('Erro ao registrar:', error.message || error);
		erro.value = 'Erro ao registrar sua escolha. Tente novamente.'
	}
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
		// depois que carregar as ofertas, carregar as solicitações feitas com este CPF
		try {
			const responseSolicitacoes = await api.get(`/solicitacoesPorCpf/${cpf.value}`)
			resultado.value = JSON.stringify(responseSolicitacoes.data); // Debug
			solicitacoesCpf.value = responseSolicitacoes.data.response || [];
		} catch (e) {
			solicitacoesCpf.value = []
		}
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
			ofertas.value = encontrarMelhorOferta(ofertas.value).map(oferta => {
				oferta.jaTemSolicitacao = solicitacoesCpf.value.some(s =>
					s.codModalidade === oferta.codModalidade &&
					s.instituicao === oferta.instituicao
				);
				return oferta;
			});
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
