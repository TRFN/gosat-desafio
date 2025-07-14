import { createRouter, createWebHistory } from 'vue-router'

function autoImportViews() {
	const pages = import.meta.glob('../views/*.vue', { eager: true })
	return Object.keys(pages).map((path) => {
		const name = path.match(/\/([\w-]+)\.vue$/)[1]
		return {
			path: name === 'Home' ? '/' : '/' + name.toLowerCase(),
			name,
			component: pages[path].default
		}
	})
}

const router = createRouter({
	history: createWebHistory(),
	routes: autoImportViews()
})

export default router