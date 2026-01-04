import { createRouter, createWebHistory } from 'vue-router'
import { setTitle } from '@/router/guards/SetTitleGuard.mjs'
import Index from '@pages/index.vue'
import Login from '@pages/login.vue'
import Register from '@pages/signup.vue'
import Barbers from '@pages/barbers.vue'
import Contact from '@pages/contact.vue'
import Booking from '@pages/booking.vue'

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
  {
    path: '/contact',
    name: 'Contact',
    component: Contact,
  },
  {
    path: '/booking',
    name: 'Booking',
    component: Booking
  }
]

export const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(setTitle)
