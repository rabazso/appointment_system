<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet'
import {
  NavigationMenu,
  NavigationMenuLink,
  NavigationMenuList,
  NavigationMenuItem,
  navigationMenuTriggerStyle
} from '@/components/ui/navigation-menu'

import AuthModal from '@/components/auth/AuthModal.vue'
import Toast from '@/components/ui/Toast.vue'
import { useAuthStore } from '@stores/AuthStore.js'
import { getCurrentUser } from '@/api'

const props = defineProps({ variant: String, required: false })
const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const sheetOpen = ref(false)
const loginOpen = ref(false)
const toastMessage = ref('')
const showToast = ref(false)
const accountMenuOpen = ref(false)
const accountMenuRef = ref(null)
const userName = ref('')

const isAuthenticated = computed(() => auth.isLoggedIn)
const isBarberId = computed(() => {
  const id = Number(auth.user_id)
  return Number.isInteger(id) && id >= 1 && id <= 5
})
const isBarberUser = computed(() => ['employee', 'barber', 'admin'].includes(auth.role) || isBarberId.value)
const accountButtonLabel = computed(() => userName.value || 'My Account')
const dashboardPath = computed(() => (isBarberUser.value ? '/barberAdminPage' : '/yourAppointments'))
const isDashboardActive = computed(() => route.path === dashboardPath.value)

let bgcolor = ref(props.variant === 'background' ? 'bg-background' : 'bg-primary')
let textcolor = ref(props.variant === 'background' ? 'text-foreground' : 'text-primary-foreground')
if (props.variant === 'background') {
  bgcolor = ref('bg-background')
  textcolor = ref('text-foreground')
}
// let buttonStyle = ref(props.variant === 'background'
//   ? 'bg-primary text-primary-foreground hover:bg-primary/90'
//   : 'bg-background text-foreground hover:bg-background/90')

const title = import.meta.env.VITE_APP_NAME || 'MyApp'
const links = [
  { label: 'Services', to: '/', hash: '#services' },
  { label: 'Our Barbers', to: '/', hash: '#barbers' },
  { label: 'Reviews', to: '/', hash: '#reviews' },
  { label: 'Contact', to: '/contact' }
]

const scrollToLink = async (link) => {
  if (!link) return
  sheetOpen.value = false

  if (link.hash) {
    const current = router.currentRoute.value
    if (current.path === link.to && current.hash === link.hash) {
      const element = document.querySelector(link.hash)
      if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' })
      }
      return
    }
    await router.push({ path: link.to, hash: link.hash })
    return
  } else {
    if (router.currentRoute.value.path !== link.to) {
      await router.push(link.to)
      return
    }

    window.scrollTo({
      top: 0,
      left: 0,
      behavior: 'smooth'
    })
  }
}

async function signOut() {
  try {
    accountMenuOpen.value = false
    await auth.logout()
    userName.value = ''
    toastMessage.value = 'You have successfully signed out.'
    showToast.value = true
  } catch (error) {
    console.error(error)
  }
}

function handleAuthSuccess(message) {
  loginOpen.value = false
  toastMessage.value = message
  showToast.value = true
  hydrateAccountInfo()
}

function toggleAccountMenu() {
  accountMenuOpen.value = !accountMenuOpen.value
}

function goToDashboard() {
  accountMenuOpen.value = false
  if (isBarberUser.value) {
    router.push('/barberAdminPage')
    return
  }
  router.push('/yourAppointments')
}

async function hydrateAccountInfo() {
  if (!isAuthenticated.value) return
  try {
    const response = await getCurrentUser()
    if (response?.data?.name) {
      userName.value = response.data.name
    }
    if (response?.data?.role && !auth.role) {
      auth.setRole(response.data.role)
    }
  } catch (error) {
    console.error('Failed to hydrate account info', error)
  }
}

function handleClickOutside(event) {
  if (accountMenuRef.value && !accountMenuRef.value.contains(event.target)) {
    accountMenuOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  hydrateAccountInfo()
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

watch(isAuthenticated, (loggedIn) => {
  if (!loggedIn) {
    userName.value = ''
    accountMenuOpen.value = false
    return
  }

  hydrateAccountInfo()
})
</script>

<template>
  <header :class="[bgcolor, textcolor, 'sticky top-0 z-50']">
    <div class="flex justify-between p-4 flex-wrap items-center">
      <div class="w-40">
        <RouterLink to="/" class="flex items-center space-x-3" @click.prevent="scrollToLink({ to: '/', hash: '#hero' })">
          <div class="w-8 h-8 bg-accent rounded-lg flex items-center justify-center">
            <span class="font-bold text-primary text-lg">✂</span>
          </div>
          <span class="text-xl self-center font-bold">{{ title }}</span>
        </RouterLink>
      </div>
      <Sheet v-model:open="sheetOpen">
        <SheetTrigger asChild>
          <button variant="outline" size="icon" class="lg:hidden">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="4" y1="6" x2="20" y2="6" />
              <line x1="4" y1="12" x2="20" y2="12" />
              <line x1="4" y1="18" x2="20" y2="18" />
            </svg>
          </button>
        </SheetTrigger>
        <SheetContent side="left" class="flex flex-col">
          <RouterLink to="/" class="mr-6 hidden lg:flex">
            <span class="sr-only">{{ title }}</span>
          </RouterLink>
          <div class="grid gap-2 py-6 mt-8">
            <RouterLink
              v-for="link in links"
              :key="link.to"
              :to="link.to"
              :class="['flex w-full items-center py-2 text-lg font-semibold text-left indent-1.5 transition-colors duration-300 hover:text-accent']"
              @click.prevent="scrollToLink(link)"
            >
              {{ link.label }}
            </RouterLink>
          </div>
          <div class="flex-1"></div>
          <Button as-child class="px-8 mb-8 w-full" to="/booking">
            Book now
          </Button>
        </SheetContent>
      </Sheet>

      <NavigationMenu class="flex hidden lg:block">
        <NavigationMenuList>
          <NavigationMenuItem v-for="link in links" :key="link.to">
            <RouterLink v-slot="{ isActive }" :to="link.to" custom>
              <NavigationMenuLink
                :active="isActive" 
                class="bgcolor textcolor navigationMenuTriggerStyle() cursor-pointer"
                @click.prevent="scrollToLink(link)"
              >
                {{ link.label }}
              </NavigationMenuLink>
            </RouterLink>
          </NavigationMenuItem>
        </NavigationMenuList>
      </NavigationMenu>

      <div class="w-auto md:min-w-40 flex justify-end items-center gap-3">
        <div v-if="isAuthenticated" ref="accountMenuRef" class="relative hidden md:block">
          <Button type="button" class="h-11 px-5 text-base font-semibold transition-colors" @click.stop="toggleAccountMenu">
            <span class="inline-flex items-center gap-2 whitespace-nowrap">
              <span class="max-w-44 truncate">{{ accountButtonLabel }}</span>
              <svg
                class="h-3.5 w-3.5 opacity-80 transition-transform duration-200"
                :class="{ 'rotate-180': accountMenuOpen }"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.4"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <polyline points="6 9 12 15 18 9" />
              </svg>
            </span>
          </Button>
          <div
            v-if="accountMenuOpen"
            class="absolute right-0 mt-2 w-52 rounded-md border bg-background p-1 text-foreground shadow-lg z-50"
          >
            <button
              type="button"
              :class="[
                'w-full rounded-sm px-3 py-2 text-left text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground',
                isDashboardActive ? 'text-accent' : ''
              ]"
              @click="goToDashboard"
            >
              Dashboard
            </button>
            <button
              type="button"
              class="w-full rounded-sm px-3 py-2 text-left text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
              @click="signOut"
            >
              Sign out
            </button>
          </div>
        </div>
        <Button data-testid="headerbtn" :class="['hidden md:block px-8 font-medium transition-colors']" @click="loginOpen = true" v-if="!isAuthenticated">
          Sign In
        </Button>
      </div>
    </div>
  </header>

  <AuthModal v-if="loginOpen" @close="loginOpen = false" @success="handleAuthSuccess"/>

  <Toast v-if="showToast" :message="toastMessage" @close="showToast = false"/>
</template>
