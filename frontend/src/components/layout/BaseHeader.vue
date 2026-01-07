<script setup>
import { ref, computed, nextTick } from 'vue'
import { useRouter } from 'vue-router'

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

const props = defineProps({
  variant: { type: String, required: false }
})

let bgcolor = ref('bg-primary')
let textcolor = ref('text-primary-foreground')
if (props.variant === 'background') {
  bgcolor = ref('bg-background')
  textcolor = ref('text-foreground')
}

const sheetOpen = ref(false)
const loginOpen = ref(false)
const toastMessage = ref('')
const showToast = ref(false)

const token = ref(localStorage.getItem('token'))
const isAuthenticated = computed(() => token.value != null)

const router = useRouter()

const title = import.meta.env.VITE_APP_NAME || 'MyApp'

const links = [
  { label: 'Services', to: '/', hash: '#services' },
  { label: 'Our Barbers', to: '/', hash: '#barbers' },
  { label: 'Contact', to: '/contact' }
]

function scrollToLink(link) {
  if (!link) return
  sheetOpen.value = false
  if (link.hash) {
    if (router.currentRoute.value.path !== link.to) {
      router.push(link.to).then(() => nextTick(() => scrollToHash(link.hash)))
    } else {
      scrollToHash(link.hash)
    }
  } else {
    router.push(link.to)
  }
}

function scrollToHash(hash) {
  const el = document.querySelector(hash)
  if (el) el.scrollIntoView({ behavior: 'smooth' })
}

function handleAuthSuccess(message) {
  loginOpen.value = false
  toastMessage.value = message
  showToast.value = true
}
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
            <span class="sr-only">Navigation Menu</span>
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
              class="flex w-full items-center py-2 text-lg font-semibold text-left indent-1.5"
              @click="scrollToLink(link)"
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
                :class="[bgcolor, textcolor, navigationMenuTriggerStyle()]"
                @click.prevent="scrollToLink(link)"
              >
                {{ link.label }}
              </NavigationMenuLink>
            </RouterLink>
          </NavigationMenuItem>
        </NavigationMenuList>
      </NavigationMenu>

      <div class="w-40 flex justify-end items-center space-x-3">
        <Button
          class="hidden md:block px-8 bg-transparent hover:bg-transparent font-medium"
          @click="loginOpen = true"
        >
          {{ isAuthenticated ? 'Sign Out' : 'Sign In' }}
        </Button>
      </div>
    </div>
  </header>

  <AuthModal
    v-if="loginOpen"
    @close="loginOpen = false"
    @success="handleAuthSuccess"
  />

  <Toast
    v-if="showToast"
    :message="toastMessage"
    @close="showToast = false"
  />
</template>
