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

const props = defineProps({
	oferta: Object,
	valorSelecionado: Number,
	parcelasSelecionadas: Number,
})

const emit = defineEmits(['update:valorSelecionado', 'update:parcelasSelecionadas'])

function onValorChange(novoValor) {
	emit('update:valorSelecionado', Number(novoValor))
}

function onParcelasChange(novoValor) {
	emit('update:parcelasSelecionadas', Number(novoValor))
}

const chartCanvas = ref(null)

let chart = null

// Cálculo da parcela mensal com juros compostos
const parcelaCalculada = computed(() => {
	const i = props.oferta.jurosMes
	const n = props.parcelasSelecionadas
	const PV = props.valorSelecionado
	const pmt = PV * i / (1 - Math.pow(1 + i, -n))
	return pmt
})

const totalPagar = computed(() => parcelaCalculada.value * props.parcelasSelecionadas)

const percentualJuros = computed(() => ((totalPagar.value - props.valorSelecionado) / props.valorSelecionado) * 100)

function renderChart() {
	if (chart) chart.destroy()

	const valorSolicitado = props.valorSelecionado
	const valorTotal = totalPagar.value
	const jurosPercent = percentualJuros.value

	const jurosNormalizado = Math.min((jurosPercent / 120) * 2, 2)

	function corJuros(normalizado) {
		const r = Math.min(220, 255 * (normalizado / 2))
		const g = 0
		const b = 0
		const a = 0.7
		return `rgba(${r},${g},${b},${a})`
	}

	chart = new Chart(chartCanvas.value, {
		type: 'bar',
		data: {
			labels: ['Solicitado', 'Total a Pagar', 'Intensidade Juros'],
			datasets: [{
				label: 'Análise Financeira',
				data: [
					valorSolicitado,
					valorTotal,
					valorSolicitado * jurosNormalizado // Proporcional ao valor inicial
				],
				backgroundColor: [
					'#0d6efd',
					'#198754',
					corJuros(jurosNormalizado)
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
					display: false
				},
				tooltip: {
					callbacks: {
						label: function (ctx) {
							if (ctx.label === 'Intensidade Juros') {
								return `Juros (~${jurosPercent.toFixed(2)}%)`
							}
							return `R$ ${ctx.raw.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`
						}
					}
				}
			}
		}
	})
}


// Atualiza o gráfico sempre que os valores mudarem
watch(() => [props.valorSelecionado, props.parcelasSelecionadas], renderChart)


onMounted(() => {
	renderChart()
})

</script>