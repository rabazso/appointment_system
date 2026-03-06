<template>
  <aside class="w-64 h-screen sticky top-0 bg-white border-r border-black/10 flex flex-col shrink-0">

    <div class="m-8 py-4 rounded-lg border border-black text-center">
      <p class="text-base font-light tracking-[0.3em]">LOGO</p>
    </div>

    <nav class="flex-1 px-4 overflow-auto">
      <div class="space-y-2">
        <router-link v-for="item in menuItems.slice(0, 3)" :key="item.to" :to="item.to"
          class="flex items-center gap-3 px-3 py-2 rounded-md border border-transparent transition hover:border-black"
          active-class="bg-secondary shadow-sm hover:border-transparent hover:shadow-lg">
          <component :is="item.icon" class="w-5 h-5" />
          <span class="text-base font-medium">{{ item.label }}</span>
        </router-link>
      </div>

      <hr class="my-6 border-black/10">

      <div class="space-y-2">
        <router-link v-for="item in menuItems.slice(3, 5)" :key="item.to" :to="item.to"
          class="flex items-center gap-3 px-3 py-2 rounded-md border border-transparent transition hover:border-black"
          active-class="bg-secondary shadow-sm hover:border-transparent hover:shadow-lg">
          <component :is="item.icon" class="w-5 h-5" />
          <span class="text-base medium">{{ item.label }}</span>
        </router-link>
      </div>

      <hr class="my-6 border-black/10">

      <router-link to="/settings"
        class="flex items-center gap-3 px-3 py-2 rounded-md border border-transparent transition hover:border-black"
        active-class="bg-secondary shadow-sm hover:border-transparent hover:shadow-lg">
        <Settings class="w-5 h-5" />
        <span class="text-base medium">Settings</span>
      </router-link>
    </nav>
  </aside>
</template>

<script setup>
import {
  Calendar,
  Scissors,
  User,
  Clock,
  Settings,

} from 'lucide-vue-next'

const menuItems = [
  { id: 1, label: 'Schedule', to: '/admin/schedule', icon: Calendar },
  { id: 2, label: 'Services', to: '/admin/services', icon: Scissors },
  { id: 3, label: 'Employees', to: '/admin/employees', icon: User },
  { id: 4, label: 'Time offs', to: '/admin/time-offs', icon: Clock },
  { id: 5, label: 'Appointments', to: '/admin/appointments', icon: Calendar },
]
</script>
