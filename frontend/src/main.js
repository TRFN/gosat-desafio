import { createApp } from 'vue'
import Base from './templates/Base.vue'
import router from './router'

import './style.css'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'

createApp(Base)
	.use(router) // <-- conecta o roteador
	.mount('#app')
