import { createRouter, createWebHistory } from 'vue-router'
import { setTitle } from '@/router/guards/SetTitleGuard.mjs'
import Index from '@pages/index.vue'
import Barbers from '@pages/barbers.vue'
import Contact from '@pages/contact.vue'
import Booking from '@pages/booking.vue'
import Summary from '@pages/summary.vue'
import Confirm from '@pages/confirm.vue'
import ConfirmationPending from '@pages/confirmationPending.vue'
import YourAppointments from '@pages/yourAppointments.vue'
import BarberAdminPage from '@pages/barberAdminPage.vue'
import ForgotPassword from '@pages/forgotPassword.vue'
import ResetPassword from '@pages/resetPassword.vue'

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
  },
  {
    path: '/summary',
    name: 'Summary',
    component: Summary,
    meta:{
      title: 'Summary'
    }
  },
  {
    path: '/confirm/:appointmentId',
    name: 'Confirm',
    component: Confirm,
    meta:{
      title: 'Confirm'
    }
  },
  {
    path: '/confirmation-pending',
    name: 'ConfirmationPending',
    component: ConfirmationPending,
    meta:{
      title: 'Confirmation Pending'
    }
  },
  {
    path: '/yourAppointments',
    name: 'YourAppointments',
    component: YourAppointments,
    meta:{
      title: 'YourAppointments'
    }
  },
  {
    path: '/barberAdminPage',
    name: 'BarberAdminPage',
    component: BarberAdminPage,
    meta:{
      title: 'BarberAdminPage',
      requiresBarber: true
    }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPassword,
    meta: {
      title: 'Forgot Password'
    }
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: ResetPassword,
    meta: {
      title: 'Reset Password'
    }
  }
]

export const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }

    if (to.hash) {
      return new Promise((resolve) => {
        setTimeout(() => {
          resolve({
            el: to.hash,
            behavior: 'smooth',
            top: 70
          })
        }, 120)
      })
    }

    return {
      top: 0,
      left: 0
    }
  }
})

router.beforeEach((to, from, next) => {
  if (to.meta?.requiresBarber) {
    const token = localStorage.getItem('token')
    const role = localStorage.getItem('role')
    const userId = Number(localStorage.getItem('user_id'))

    const isBarberRole = ['employee', 'barber', 'admin'].includes(role)
    const isBarberId = Number.isInteger(userId) && userId >= 1 && userId <= 5
    const canAccessBarberAdmin = Boolean(token) && (isBarberRole || isBarberId)

    if (!canAccessBarberAdmin) {
      next('/')
      return
    }
  }

  setTitle(to, from, next)
})
