import { createRouter, createWebHistory } from 'vue-router'
import { setTitle } from '@/router/guards/SetTitleGuard.mjs'
import Index from '@pages/index.vue'
import Barbers from '@pages/barbers.vue'
import EmployeeDetails from '@pages/employeeDetails.vue'
import Contact from '@pages/contact.vue'
import Booking from '@pages/booking.vue'
import Summary from '@pages/summary.vue'
import Confirm from '@pages/confirm.vue'
import ConfirmationPending from '@pages/confirmationPending.vue'
import YourAppointments from '@pages/yourAppointments.vue'
import ForgotPassword from '@pages/forgotPassword.vue'
import ResetPassword from '@pages/resetPassword.vue'
import Services from '@pages/admin/Services.vue'
import Employees from '@pages/admin/Employees.vue'
import Schedule from '@pages/admin/Schedule.vue'
import VerifyEmail from '@pages/verifyEmail.vue'
import AdminSignIn from '@pages/admin/AdminSignIn.vue'
import EmployeeSignIn from '@pages/employee/EmployeeSignIn.vue'
//import AppointmentsPage from '@pages/employee/Appointments.vue'
import ProfilePage from '@pages/employee/Profile.vue'
import ReviewsPage from '@pages/employee/Reviews.vue'
import TimeOffPage from '@pages/employee/TimeOff.vue'
import TimeOff from '@pages/admin/TimeOff.vue'
import ShopProfile from '@pages/admin/ShopProfile.vue'

const routes = [
  {
    path: '/',
    name: 'Main Page',
    component: Index,
    meta: {
      title: 'Main Page'
    }
  },
  {
    path: '/barbers',
    name: 'Barbers',
    component: Barbers,
    meta: {
      title: 'Barbers'
    }
  },
  {
    path: '/barbers/:id',
    name: 'EmployeeDetails',
    component: EmployeeDetails,
    meta: {
      title: 'Barber Profile',
    },
  },
  {
    path: '/contact',
    name: 'Contact',
    component: Contact,
    meta: {
      title: 'Contact'
    }
  },
  {
    path: '/booking',
    name: 'Booking',
    component: Booking,
    meta: {
      title: 'Booking'
    }
  },
  {
    path: '/summary',
    name: 'Summary',
    component: Summary,
    meta: {
      title: 'Summary'
    }
  },
  {
    path: '/confirm/:appointmentId',
    name: 'Confirm',
    component: Confirm,
    meta: {
      title: 'Confirm'
    }
  },
  {
    path: '/confirmation-pending',
    name: 'ConfirmationPending',
    component: ConfirmationPending,
    meta: {
      title: 'Confirmation Pending'
    }
  },
  {
    path: '/yourAppointments',
    name: 'YourAppointments',
    component: YourAppointments,
    meta: {
      title: 'YourAppointments'
    }
  },
  {
    path: '/employee',
    redirect: '/employee/appointments',
  },
  // {
  //   path: '/employee/appointments',
  //   name: 'EmployeeAppointments',
  //   component: AppointmentsPage,
  //   meta: {
  //     title: 'Employee Appointments',
  //     requiresEmployee: true,
  //   },
  // },
  {
    path: '/employee/profile',
    name: 'EmployeeProfile',
    component: ProfilePage,
    meta: {
      title: 'Employee Profile',
      requiresEmployee: true,
    },
  },
  {
    path: '/employee/reviews',
    name: 'EmployeeReviews',
    component: ReviewsPage,
    meta: {
      title: 'Employee Reviews',
      requiresEmployee: true,
    },
  },
  {
    path: '/employee/time-off',
    name: 'EmployeeTimeOff',
    component: TimeOffPage,
    meta: {
      title: 'Employee Time Off',
      requiresEmployee: true,
    },
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
  },
  {
    path: '/admin/services',
    name: 'Services',
    component: Services,
    meta: {
      title: 'Services',
      requiresAdmin: true,
    }
  },
  {
    path: '/admin/employees',
    name: 'Employees',
    component: Employees,
    meta: {
      title: 'Employees',
      requiresAdmin: true,
    }
  },
  {
    path: '/admin/schedule',
    name: 'Schedule',
    component: Schedule,
    meta: {
      title: 'Shop Schedule',
      requiresAdmin: true,
    }
  },
  {
    path: '/verify-email/:id/:hash',
    name: 'VerifyEmail',
    component: VerifyEmail,
    meta: {
      title: 'Verify Email'
    }
  },
  {
    path: '/admin/login',
    name: 'AdminSignIn',
    component: AdminSignIn,
    meta: {
      title: 'Admin Signin'
    }
  },
  {
    path: '/employee/login',
    name: 'EmployeeSignIn',
    component: EmployeeSignIn,
    meta: {
      title: 'Employee Signin'
    }
  },
  {
    path: '/admin/time-offs',
    name: 'TimeOff',
    component: TimeOff,
    meta: {
      title: 'TimeOff',
      requiresAdmin: true,
    }
  },
  {
    path: '/admin/shop-profile',
    name: 'shop-profile',
    component: ShopProfile,
    meta: {
      title: 'Shop Profile',
      requiresAdmin: true,
    }
  },
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
            top: 68
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
  const token = localStorage.getItem('token')
  const role = localStorage.getItem('role')
  const requiresEmployee = to.matched.some((route) => route.meta?.requiresEmployee)
  const requiresAdmin = to.matched.some((route) => route.meta?.requiresAdmin)

  if (requiresEmployee) {
    const canAccessEmployee = Boolean(token) && role === 'employee'
    if (!canAccessEmployee) {
      next(token ? '/' : '/employee/login')
      return
    }
  }

  if (requiresAdmin) {
    const canAccessAdmin = Boolean(token) && role === 'admin'
    if (!canAccessAdmin) {
      next(token ? '/' : '/admin/login')
      return
    }
  }

  setTitle(to, from, next)
})
