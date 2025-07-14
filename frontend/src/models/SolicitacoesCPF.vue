<template>
	<div>
		<table class="table table-sm table-bordered">
			<thead>
				<tr>
					<th>Instituição</th>
					<th>Modalidade</th>
					<th>Valor</th>
					<th>Parcelas</th>
					<th>Juros</th>
					<th>Data</th>
					<th>Ação</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="sol in solicitacoes" :key="sol.id">
					<td>{{ sol.instituicao }}</td>
					<td>{{ sol.modalidade }}</td>
					<td>R$ {{ sol.valor.toLocaleString() }}</td>
					<td>{{ sol.parcelas }}</td>
					<td>{{ (sol.jurosMes * 100).toFixed(2) }}%</td>
					<td>{{ new Date(sol.created_at).toLocaleString() }}</td>
					<td>
						<button class="btn btn-sm btn-danger" @click="$emit('desfazer', sol.id)">
							<i class="bi bi-x-circle"></i> Desfazer
						</button>
					</td>
				</tr>
				<tr v-if="solicitacoes.length === 0">
					<td colspan="7" class="text-center">Nenhuma solicitação feita ainda.</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>

<script setup>
import { defineProps } from 'vue'

const props = defineProps({
	solicitacoes: {
		type: Array,
		required: true
	}
})
</script>
