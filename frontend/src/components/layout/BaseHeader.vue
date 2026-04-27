<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ChevronDown, LayoutDashboard, LogOut, Menu } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet'
import {
  NavigationMenu,
  NavigationMenuLink,
  NavigationMenuList,
  NavigationMenuItem,
  navigationMenuTriggerStyle
} from '@/components/ui/navigation-menu'
import { CircleUserRound } from 'lucide-vue-next';

import AuthModal from '@/components/auth/AuthModal.vue'
import Toast from '@/components/ui/Toast.vue'
import { useAuthStore } from '@stores/AuthStore.js'
import { getCurrentUser } from '@/api'
import { useToastStore } from '@/stores/ToastStore.js'

const props = defineProps({ variant: String, required: false })
const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const globalToast = useToastStore()

const sheetOpen = ref(false)
const loginOpen = ref(false)
const toastMessage = ref('')
const showToast = ref(false)
const accountMenuOpen = ref(false)
const accountMenuRef = ref(null)

const isAuthenticated = computed(() => auth.isLoggedIn)
const isBarberId = computed(() => {
  const id = Number(auth.user_id)
  return Number.isInteger(id) && id >= 1 && id <= 5
})
const isBarberUser = computed(() => ['employee', 'barber', 'admin'].includes(auth.role) || isBarberId.value)
const accountButtonLabel = computed(() => auth.user_name || 'Account')
const dashboardPath = computed(() => (isBarberUser.value ? '/employee/dashboard' : '/yourAppointments'))
const isDashboardActive = computed(() => route.path === dashboardPath.value)

let bgcolor = ref(props.variant === 'background' ? 'bg-background' : 'bg-primary')
let textcolor = ref(props.variant === 'background' ? 'text-foreground' : 'text-primary-foreground')
if (props.variant === 'background') {
  bgcolor = ref('bg-background')
  textcolor = ref('text-foreground')
}

const title = import.meta.env.VITE_APP_NAME || 'MyApp'
const links = [
  { label: 'Services', to: '/', hash: '#services' },
  { label: 'Our Barbers', to: '/', hash: '#barbers' },
  { label: 'Gallery', to: '/', hash: '#gallery' },
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
    toastMessage.value = 'You have successfully signed out.'
    showToast.value = true
  } catch (error) {
    console.error(error)
    globalToast.showError('Failed to sign out.')
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
    router.push('/employee/dashboard')
    return
  }
  router.push('/yourAppointments')
}

async function hydrateAccountInfo() {
  if (!isAuthenticated.value) return
  try {
    const response = await getCurrentUser()
    if (response?.data?.name) {
      auth.setName(response.data.name)
    }
    if (response?.data?.role && !auth.role) {
      auth.setRole(response.data.role)
    }
  } catch (error) {
    console.error('Failed to hydrate account info', error)
    globalToast.showError('Failed to load account info.')
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
            <Menu class="h-6 w-6" />
          </button>
        </SheetTrigger>
        <SheetContent side="left" class="flex flex-col">
          
          <div class="grid gap-2 py-6 mt-8">
            
            <RouterLink
              v-for="link in links"
              :key="link.to"
              :to="link.to"
              :class="['flex w-full items-center py-2 text-lg font-semibold text-left indent-4 transition-colors duration-300 hover:text-accent']"
              @click.prevent="scrollToLink(link)"
            >
              {{ link.label }}
            </RouterLink>
          </div>
          <div v-if="isAuthenticated" ref="accountMenuRef" class="space-y-2">
            <Button
              variant="base"
              class="h-12 w-full justify-start rounded-xl px-4 text-left shadow-none [&_p]:w-full"
              @click.stop="toggleAccountMenu"
            >
              <span class="flex w-full items-center justify-between gap-2">
                <span class="inline-flex min-w-0 items-center gap-3">
                  <CircleUserRound class="size-8 text-foreground" />
                </span>
              </span>
            </Button>
            <div
              v-if="accountMenuOpen"
              class="overflow-hidden rounded-xl border border-border bg-background text-foreground shadow-sm"
            >
              <div class="border-b border-border px-4 py-3">
                <p class="truncate text-sm font-semibold text-foreground">{{ accountButtonLabel }}</p>
              </div>
              <div class="p-1.5">
                <Button
                  variant="ghost"
                  class="h-10 w-full justify-start rounded-lg px-3 text-sm font-medium"
                  :class="isDashboardActive ? 'bg-accent text-accent-foreground' : ''"
                  @click="goToDashboard"
                >
                  <span class="inline-flex items-center gap-2">
                    <LayoutDashboard class="h-4 w-4" />
                    <span>Dashboard</span>
                  </span>
                </Button>
                <Button
                  variant="ghost"
                  class="mt-1 h-10 w-full justify-start rounded-lg px-3 text-sm font-medium text-destructive hover:bg-destructive/10 hover:text-destructive"
                  @click="signOut"
                >
                  <span class="inline-flex items-center gap-2">
                    <LogOut class="h-4 w-4" />
                    <span>Sign out</span>
                  </span>
                </Button>
              </div>
            </div>
          </div>
          <Button variant="base" as-child @click="[loginOpen = true, sheetOpen = false]" v-if="!isAuthenticated">
            Sign In
          </Button>
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
          <Button
            variant="base"
            class="h-11 rounded-xl px-4 shadow-none"
            @click.stop="toggleAccountMenu"
          >
            <span class="inline-flex items-center gap-3 whitespace-nowrap">
              <CircleUserRound class="size-8 text-primary-foreground" />
            </span>
          </Button>
          <div
            v-if="accountMenuOpen"
            class="absolute right-0 top-full z-50 mt-2 w-64 overflow-hidden rounded-xl border border-border bg-background text-foreground shadow-lg"
          >
            <div class="border-b border-border px-4 py-3">
              <p class="truncate text-sm font-semibold text-foreground">{{ accountButtonLabel }}</p>
            </div>
            <div class="p-1.5">
              <Button
                variant="ghost"
                class="h-10 w-full justify-start rounded-lg px-3 text-sm font-medium"
                :class="isDashboardActive ? 'bg-accent text-accent-foreground' : ''"
                @click="goToDashboard"
              >
                <span class="inline-flex items-center gap-2">
                  <LayoutDashboard class="h-4 w-4" />
                  <span>Dashboard</span>
                </span>
              </Button>
              <Button
                variant="ghost"
                class="mt-1 h-10 w-full justify-start rounded-lg px-3 text-sm font-medium text-destructive hover:bg-destructive/10 hover:text-destructive"
                @click="signOut"
              >
                <span class="inline-flex items-center gap-2">
                  <LogOut class="h-4 w-4" />
                  <span>Sign out</span>
                </span>
              </Button>
            </div>
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
