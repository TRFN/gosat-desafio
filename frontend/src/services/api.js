// src/services/api.js
import axios from 'axios'

// Pode pegar o token fixo ou dinamicamente (ex: localStorage, Pinia etc)
const token = 'meu_token_secreto_123' // ou localStorage.getItem('token')

const api = axios.create({
	baseURL: '/api', // Vite proxy vai redirecionar para http://backend:7001
	headers: {
		Authorization: `Bearer ${token}`
	}
})

export default api