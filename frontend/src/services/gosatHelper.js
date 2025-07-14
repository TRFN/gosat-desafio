export function encontrarMelhorOferta(listaInstituicoes) {
	const melhores = []

	for (const inst of listaInstituicoes) {
		for (const modalidade of inst.modalidades) {
			const oferta = modalidade.oferta

			melhores.push({
				instituicao: inst.nome,
				modalidade: modalidade.nome,
				codModalidade: modalidade.cod,
				jurosMes: oferta.jurosMes,
				valorMin: oferta.valorMin,
				valorMax: oferta.valorMax,
				QntParcelaMin: oferta.QntParcelaMin,
				QntParcelaMax: oferta.QntParcelaMax,
				valorSelecionado: (oferta.valorMax + oferta.valorMin) / 2, // Valor médio para o range
				parcelasSelecionadas: Math.max(Math.floor((oferta.QntParcelaMax + oferta.QntParcelaMin) / 12) * 6, oferta.QntParcelaMin), // Média de parcelas
			})
		}
	}

	melhores.sort((a, b) => {
		// 1. Maior valor máximo
		if (a.valorMax !== b.valorMax) {
			return b.valorMax - a.valorMax
		}

		// 2. Menor taxa de juros
		if (a.jurosMes !== b.jurosMes) {
			return a.jurosMes - b.jurosMes
		}

		// 3. Maior quantidade de parcelas
		return b.QntParcelaMax - a.QntParcelaMax
	})

	return melhores
}

