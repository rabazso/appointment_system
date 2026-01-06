<script setup>
import {
  NavigationMenu,
  NavigationMenuLink,
  NavigationMenuList,
  NavigationMenuItem,
  navigationMenuTriggerStyle
} from '@components/ui/navigation-menu'
import { Sheet, SheetContent, SheetTrigger } from '@components/ui/sheet'
import { Button } from '@components/ui/button'
import { ref, nextTick } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  variant: { type: String, required: false }
})

let bgcolor = ref('bg-primary')
let textcolor = ref('text-primary-foreground')
if (props.variant === 'background') {
  bgcolor = ref('bg-background')
  textcolor = ref('text-foreground')
}

const title = import.meta.env.VITE_APP_NAME

const links = [
  { 
    label: 'Services', 
    to: '/', 
    hash: '#services'
  },
  { 
    label: 'Our Barbers', 
    to: '/', 
    hash: '#barbers'
  },
  { 
    label: 'Contact', 
    to: '/contact'
  }
]

const router = useRouter()

const scrollToLink = async (link) => {
  if (!link) return

  if (link.hash) {
    if (router.currentRoute.value.path !== link.to) {
      await router.push(link.to)
      await nextTick()
    }
    const element = document.querySelector(link.hash)
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' })
    }
  } else {
    router.push(link.to)
  }
}
</script>

<template>
  <header id="header" class="sticky top-0 z-50" :class="bgcolor, textcolor">
    <div class="flex justify-between p-4 flex-wrap items-center">
      <div class="w-40">
        <RouterLink to="/" class="flex items-center space-x-3" @click.prevent="scrollToLink({ to: '/', hash: '#hero' })">
          <div class="w-8 h-8 bg-accent rounded-lg flex items-center justify-center">
            <span class="font-bold text-primary text-lg">✂</span>
          </div>
          <span class="text-xl self-center font-bold">{{ title }}</span>
        </RouterLink>
      </div>
      <Sheet>
        <SheetTrigger asChild>
          <button variant="outline" size="icon" class="lg:hidden">
            <svg 
              class="h-6 w-6" 
              xmlns="http://www.w3.org/2000/svg" 
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none" 
              stroke="currentColor" 
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <line x1="4" x2="20" y1="12" y2="12" />
              <line x1="4" x2="20" y1="6" y2="6" />
              <line x1="4" x2="20" y1="18" y2="18" />
            </svg>
            <span class="sr-only">Navigációs menü</span>
          </button>
        </SheetTrigger>
        <SheetContent side="left">
          <RouterLink to="/" class="mr-6 hidden lg:flex">
            <span class="sr-only">{{ title }}</span>
          </RouterLink>
          <div class="grid gap-2 py-6">
            <RouterLink>
              <Button
                v-for="link in links"
                :key="link.label"
                class="flex w-full items-center py-2 text-lg font-semibold text-left"
                @click="scrollToLink(link)"
              >
                {{ link.label }}
              </Button>
            </RouterLink>
          </div>
        </SheetContent>
      </Sheet>
      <NavigationMenu class="flex hidden lg:block">
        <NavigationMenuList>
          <NavigationMenuItem v-for="link of links" :key="link.to">
            <RouterLink v-slot="{ isActive, href, navigate }" :to="link.to" custom>
              <NavigationMenuLink
                :active="isActive"
                :href
                :class="bgcolor, textcolor, navigationMenuTriggerStyle()"
                @click.prevent="scrollToLink(link)"
              >
                {{ link.label }}
              </NavigationMenuLink>
            </RouterLink>
          </NavigationMenuItem>
        </NavigationMenuList>
      </NavigationMenu>
      <div class="w-40 flex justify-end items-center space-x-3">
          <Button as-child class="hidden md:block px-8" to="/booking">
            Book now
          </Button>
      </div>
    </div>
  </header>
</template>
