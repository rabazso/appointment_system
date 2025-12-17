import { createRouter, createWebHistory } from 'vue-router'
import { setTitle } from '@/router/guards/SetTitleGuard.mjs'
import Index from '@pages/index.vue'
import Login from '@pages/login.vue'
import Register from '@pages/signup.vue'
import Barbers from '@pages/barbers.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Index,
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
  },
  {
    path: '/signup',
    name: 'SignUp',
    component: Register,
  },
  {
    path: '/barbers',
    name: 'Barbers',
    component: Barbers,
  },
]

export const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(setTitle)
