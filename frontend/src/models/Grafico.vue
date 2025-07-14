<script setup>
import { onMounted, ref, watch } from 'vue'
import { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend } from 'chart.js'

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend)

const props = defineProps({
	oferta: Object
})

const canvasEl = ref(null)
let chart = null

onMounted(() => {
	if (!props.oferta || !canvasEl.value) return

	chart = new Chart(canvasEl.value, {
		type: 'bar',
		data: {
			labels: ['Valor Mínimo', 'Valor Máximo', 'Selecionado'],
			datasets: [{
				label: 'Valores da Oferta',
				data: [
					props.oferta.valorMin,
					props.oferta.valorMax,
					props.oferta.valorSelecionado || props.oferta.valorMin
				],
				backgroundColor: ['#6c757d', '#0d6efd', '#ffc107'],
				borderRadius: 6
			}]
		},
		options: {
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: ctx => `R$ ${ctx.raw.toLocaleString()}`
					}
				}
			},
			scales: {
				y: {
					beginAtZero: true,
					ticks: {
						callback: val => 'R$ ' + val.toLocaleString()
					}
				}
			},
			responsive: true,
			maintainAspectRatio: false
		}
	})
})

// Atualiza o valor "Selecionado" dinamicamente
watch(() => props.oferta.valorSelecionado, (novo) => {
	if (chart) {
		chart.data.datasets[0].data[2] = novo || props.oferta.valorMin
		chart.update()
	}
})
</script>

<template>
	<div style="height: 180px">
		<canvas ref="canvasEl"></canvas>
	</div>
</template>