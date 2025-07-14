export function encontrarMelhorOferta(listaInstituicoes) {
	const melhores = []

	// Itera sobre todas as instituições financeiras recebidas
	for (const inst of listaInstituicoes) {
		// Para cada instituição, percorre suas modalidades de empréstimo/oferta
		for (const modalidade of inst.modalidades) {
			const oferta = modalidade.oferta

			// Monta um objeto simplificado que representa a oferta em destaque,
			// com dados importantes para comparação e decisão
			melhores.push({
				instituicao: inst.nome,            // Nome da instituição financeira
				modalidade: modalidade.nome,       // Nome da modalidade de empréstimo
				codModalidade: modalidade.cod,     // Código da modalidade para referência
				jurosMes: oferta.jurosMes,          // Taxa de juros mensal aplicada
				valorMin: oferta.valorMin,          // Valor mínimo permitido no empréstimo
				valorMax: oferta.valorMax,          // Valor máximo permitido no empréstimo
				QntParcelaMin: oferta.QntParcelaMin, // Quantidade mínima de parcelas
				QntParcelaMax: oferta.QntParcelaMax, // Quantidade máxima de parcelas

				// Valor selecionado para análise é a média entre mínimo e máximo,
				// oferecendo um valor intermediário representativo para comparação
				valorSelecionado: (oferta.valorMax + oferta.valorMin) / 2,

				// Seleciona a quantidade de parcelas considerando uma média ponderada:
				// - Soma as parcelas máxima e mínima
				// - Divide por 12 para normalizar em anos (aproximadamente)
				// - Multiplica por 6 para ajustar a parcela a aproximadamente metade do período total
				// - Usa o maior valor entre esse cálculo e a quantidade mínima de parcelas
				parcelasSelecionadas: Math.max(
					Math.floor((oferta.QntParcelaMax + oferta.QntParcelaMin) / 12) * 6,
					oferta.QntParcelaMin
				),
			})
		}
	}

	// Ordena as ofertas montadas de acordo com critérios de prioridade:
	melhores.sort((a, b) => {
		// 1. Prioriza ofertas com maior valor máximo disponível
		if (a.valorMax !== b.valorMax) {
			return b.valorMax - a.valorMax
		}

		// 2. Caso empate no valor máximo, prioriza as ofertas com menor taxa de juros mensal
		if (a.jurosMes !== b.jurosMes) {
			return a.jurosMes - b.jurosMes
		}

		// 3. Se ainda houver empate, prioriza a oferta com maior quantidade máxima de parcelas
		return b.QntParcelaMax - a.QntParcelaMax
	})

	// Retorna a lista ordenada de melhores ofertas
	return melhores
}
