<template>
	<p><strong>Modalidade:</strong> <span class="text-capitalize">{{ oferta.modalidade }}</span></p>
	<p><strong>Juros:</strong> {{ (oferta.jurosMes * 100).toFixed(2) }}% ao mês</p>

	<div class="mb-3">
		<label><strong>Valor desejado:</strong> R$ {{ props.valorSelecionado.toLocaleString() }}</label>
		<input type="range" class="form-range" :min="oferta.valorMin" :max="oferta.valorMax" :value="valorSelecionado"
			@input="onValorChange($event.target.value)" step="100" />
	</div>

	<div class="mb-3">
		<label><strong>Meses:</strong> {{ props.parcelasSelecionadas }} / {{ oferta.QntParcelaMax }}</label>
		<input type="range" step="6" class="form-range" :min="oferta.QntParcelaMin" :max="oferta.QntParcelaMax"
			:value="parcelasSelecionadas" @input="onParcelasChange($event.target.value)" />
	</div>

	<div v-if="parcelaCalculada">
		<p><strong>Parcela:</strong> {{ parcelaCalculada.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
		}}</p>
		<p><strong>Total a pagar:</strong> {{ totalPagar.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
		}}</p>
		<p><strong>Juros total:</strong> {{ percentualJuros.toFixed(2) }}%</p>
	</div>
	<canvas ref="chartCanvas"></canvas>
</template>

<script setup>
import { defineProps, defineEmits, ref, computed, watch, onMounted } from 'vue'
import Chart from 'chart.js/auto'

// Define as propriedades que o componente espera receber do pai
const props = defineProps({
	oferta: Object,             // Objeto com dados da oferta financeira (ex: jurosMensais)
	valorSelecionado: Number,   // Valor do empréstimo selecionado
	parcelasSelecionadas: Number, // Quantidade de parcelas selecionadas
})

// Define os eventos que o componente pode emitir para o pai
const emit = defineEmits(['update:valorSelecionado', 'update:parcelasSelecionadas'])

// Função para emitir atualização do valor selecionado
function onValorChange(novoValor) {
	emit('update:valorSelecionado', Number(novoValor))
}

// Função para emitir atualização da quantidade de parcelas
function onParcelasChange(novoValor) {
	emit('update:parcelasSelecionadas', Number(novoValor))
}

// Referência ao elemento canvas onde o gráfico será desenhado
const chartCanvas = ref(null)

// Variável que armazenará a instância do gráfico
let chart = null

// Cálculo da parcela mensal com juros compostos
const parcelaCalculada = computed(() => {
	const i = props.oferta.jurosMes      // Taxa de juros mensal (ex: 0.02 para 2%)
	const n = props.parcelasSelecionadas // Número de parcelas
	const PV = props.valorSelecionado    // Valor presente (valor solicitado)
	// Fórmula de cálculo da parcela de financiamento com juros compostos
	const pmt = PV * i / (1 - Math.pow(1 + i, -n))
	return pmt
})

// Total a pagar é a parcela vezes o número de parcelas
const totalPagar = computed(() => parcelaCalculada.value * props.parcelasSelecionadas)

// Percentual de juros pago em relação ao valor solicitado
const percentualJuros = computed(() => ((totalPagar.value - props.valorSelecionado) / props.valorSelecionado) * 100)

// Função que renderiza o gráfico usando Chart.js
function renderChart() {
	// Se já existir um gráfico renderizado, destrói para evitar sobreposição
	if (chart) chart.destroy()

	const valorSolicitado = props.valorSelecionado
	const valorTotal = totalPagar.value
	const jurosPercent = percentualJuros.value

	// Normaliza o percentual de juros para um valor entre 0 e 2 (escala para a barra)
	const jurosNormalizado = Math.min((jurosPercent / 120) * 2, 2)

	// Define cor do gráfico de juros com base no valor normalizado
	function corJuros(normalizado) {
		const r = Math.min(220, 255 * (normalizado / 2)) // Intensidade do vermelho
		const g = 0
		const b = 0
		const a = 0.7 // Transparência
		return `rgba(${r},${g},${b},${a})`
	}

	// Instancia um novo gráfico do tipo barra
	chart = new Chart(chartCanvas.value, {
		type: 'bar',
		data: {
			labels: ['Solicitado', 'Total a Pagar', 'Intensidade Juros'], // Legendas do eixo X
			datasets: [{
				label: 'Análise Financeira',
				data: [
					valorSolicitado,
					valorTotal,
					valorSolicitado * jurosNormalizado // Barra proporcional à intensidade do juros
				],
				backgroundColor: [
					'#0d6efd',        // Azul para valor solicitado
					'#198754',        // Verde para total a pagar
					corJuros(jurosNormalizado) // Vermelho para intensidade dos juros
				],
			}]
		},
		options: {
			responsive: true,
			scales: {
				y: {
					beginAtZero: true,
					title: {
						display: true,
						text: 'Valores (R$)'
					}
				}
			},
			plugins: {
				legend: {
					display: false // Oculta legenda pois temos labels no eixo X
				},
				tooltip: {
					callbacks: {
						label: function (ctx) {
							// Tooltip customizada para a barra de juros
							if (ctx.label === 'Intensidade Juros') {
								return `Juros (~${jurosPercent.toFixed(2)}%)`
							}
							// Formata os valores monetários para real brasileiro
							return `R$ ${ctx.raw.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`
						}
					}
				}
			}
		}
	})
}

// Observa mudanças nos valores selecionados para refazer o gráfico automaticamente
watch(() => [props.valorSelecionado, props.parcelasSelecionadas], renderChart)

// No carregamento do componente, renderiza o gráfico inicialmente
onMounted(() => {
	renderChart()
})

</script>