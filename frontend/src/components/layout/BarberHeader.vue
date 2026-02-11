<script setup>
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { NavigationMenu, NavigationMenuList, NavigationMenuItem } from '@/components/ui/navigation-menu'

const emit = defineEmits(['navigate'])

const adminLinks = [
  { label: 'Appointments', id: 'appointments' },
  { label: 'Profile', id: 'profile' },
  { label: 'Time Off', id: 'time-off' }
]
</script>

<template>
  <header class="bg-background border-b sticky top-0 z-50">
    <div class="flex justify-between p-4 items-center">
      <div class="font-bold text-xl">Barber Admin</div>
      
      <NavigationMenu>
        <NavigationMenuList class="flex space-x-4">
          <NavigationMenuItem v-for="link in adminLinks" :key="link.id">
            <button 
              @click="emit('navigate', link.id)"
              class="px-3 py-2 text-sm font-medium hover:text-primary transition-colors"
            >
              {{ link.label }}
            </button>
          </NavigationMenuItem>
        </NavigationMenuList>
      </NavigationMenu>

      <Button variant="outline">Sign Out</Button>
    </div>
  </header>
</template>