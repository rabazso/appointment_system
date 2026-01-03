<script setup>
import {
  NavigationMenu,
  NavigationMenuLink,
  NavigationMenuList,
  NavigationMenuItem,
  navigationMenuTriggerStyle
} from '@components/ui/navigation-menu'
import { Sheet, SheetContent, SheetTrigger } from '@components/ui/sheet'
import {Button} from '@components/ui/button'

const title = import.meta.env.VITE_APP_NAME

const links = [
  {
    label: 'Services',
    to: '/#services'
  },
  {
    label: 'Our Barbers',
    to: '/#barbers'
  },
  {
    label: 'Contact',
    to: '/contact'
  }
]
const scroll =(id)=> {
  const element = document.querySelector(id)
  if(id === '#hero'){
    scrollTo(top)
    return
  }
  if(element){
    element.scrollIntoView()
  }
}
</script>

<template>
  <header id="header" class="sticky top-0 bg-primary text-primary-foreground">
    <div class="flex justify-between p-4 flex-wrap">
      <div class="w-40">
        <RouterLink to="/" class="flex items-center space-x-3" @click="scroll('#hero')">
          <div className="w-8 h-8 bg-accent rounded-lg flex items-center justify-center">
            <span className="font-bold text-primary text-lg">✂</span>
          </div>
          <span className="text-xl self-center font-bold">{{ title }}</span>
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
            <RouterLink
              v-for="link of links"
              :key="link.to"
              :to="link.to"
              class="flex w-full items-center py-2 text-lg font-semibold"
            >
              {{ link.label }}
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
                :class="navigationMenuTriggerStyle()"
                @click="scroll(link.to)"
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
