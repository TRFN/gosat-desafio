// src/services/api.js
import axios from 'axios'

const api = axios.create({
	baseURL: '/api', // Aponta para o proxy configurado no vite.config.js
	timeout: 10000,
	headers: {
		'Content-Type': 'application/json',
	},
})

export default api