import { createRouter, createWebHistory } from 'vue-router'
import { auth } from './lib/auth'
import LoginView from './views/LoginView.vue'
import SchedulesView from './views/SchedulesView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', name: 'login', component: LoginView, meta: { guest: true } },
    { path: '/', name: 'schedules', component: SchedulesView, meta: { requiresAuth: true } },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

router.beforeEach((to) => {
  if (to.meta.requiresAuth && !auth.token) return { name: 'login' }
  if (to.meta.guest && auth.token) return { name: 'schedules' }
})

export default router
