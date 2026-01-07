import { createRouter, createWebHistory } from 'vue-router'
import { setTitle } from '@/router/guards/SetTitleGuard.mjs'
import Index from '@pages/index.vue'
import Barbers from '@pages/barbers.vue'
import Contact from '@pages/contact.vue'
import Booking from '@pages/booking.vue'

const routes = [
  {
    path: '/',
    name: 'Main Page',
    component: Index,
    meta:{
      title: 'Main Page'
    }
  },
  {
    path: '/barbers',
    name: 'Barbers',
    component: Barbers,
    meta:{
      title: 'Barbers'
    }
  },
  {
    path: '/contact',
    name: 'Contact',
    component: Contact,
    meta:{
      title: 'Contact'
    }
  },
  {
    path: '/booking',
    name: 'Booking',
    component: Booking,
    meta:{
      title: 'Booking'
    }
  }
]

export const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(setTitle)
