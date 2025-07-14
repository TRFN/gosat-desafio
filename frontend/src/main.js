// Importa a função createApp do Vue 3 para criar a aplicação
import { createApp } from 'vue'

// Importa o componente raiz da aplicação, que define a estrutura base/layout principal
import Base from './templates/Base.vue'

// Importa o roteador Vue Router configurado para gerenciar rotas da aplicação
import router from './router'

// Importa o arquivo principal de estilos CSS customizados da aplicação
import './style.css'

// Importa estilos e funcionalidades do Bootstrap (CSS e ícones)
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.css'
import 'bootstrap' // Importa o JavaScript do Bootstrap para funcionalidades como dropdowns, modais, etc.

// Cria a instância da aplicação Vue usando o componente raiz 'Base'
createApp(Base)
	.use(router) // Conecta o roteador Vue para navegação entre páginas/componentes
	.mount('#app') // Monta a aplicação no elemento HTML com id="app"
