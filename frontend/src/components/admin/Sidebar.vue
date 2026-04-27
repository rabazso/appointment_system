<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-2 bg-black/50 lg:hidden"
    @click="$emit('close')"
  />

  <aside
    :class="[
      'fixed top-0 left-0 z-2 flex h-screen w-64 shrink-0 flex-col border-r border-black/10 bg-white transition-transform duration-300',
      isOpen ? 'translate-x-0' : '-translate-x-full',
      'lg:sticky lg:translate-x-0',
    ]"
  >
    <div class="flex justify-end p-4 lg:hidden">
      <button @click="$emit('close')">
        <X class="h-6 w-6" />
      </button>
    </div>

    <div class="mx-8 mb-8 rounded-lg border border-black py-4 text-center md:mt-8 md:mb-8">
      <p class="text-base font-light tracking-[0.3em]">LOGO</p>
    </div>

    <nav class="flex-1 overflow-auto px-4">
      <div class="space-y-2">
        <router-link
          v-for="item in menuItems.slice(0, 2)"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 rounded-md border border-transparent px-3 py-2 transition hover:border-black"
          active-class="bg-secondary shadow-sm hover:border-transparent hover:shadow-lg"
          @click="$emit('close')"
        >
          <component :is="item.icon" class="h-5 w-5" />
          <span class="text-base font-medium">{{ item.label }}</span>
        </router-link>
      </div>

      <hr class="my-6 border-black/10">

      <div class="space-y-2">
        <router-link
          v-for="item in menuItems.slice(2, 5)"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 rounded-md border border-transparent px-3 py-2 transition hover:border-black"
          active-class="bg-secondary shadow-sm hover:border-transparent hover:shadow-lg"
          @click="$emit('close')"
        >
          <component :is="item.icon" class="h-5 w-5" />
          <span class="text-base font-medium">{{ item.label }}</span>
        </router-link>
      </div>

      <hr class="my-6 border-black/10">

    <div class="space-y-2">
      <router-link
        v-for="item in menuItems.slice(5,7)"
        :key="item.to"
        :to="item.to"
        class="flex items-center gap-3 rounded-md border border-transparent px-3 py-2 transition hover:border-black"
        active-class="bg-secondary shadow-sm hover:border-transparent hover:shadow-lg"
        @click="$emit('close')"
        >
          <component :is="item.icon" class="h-5 w-5" />
          <span class="text-base font-medium">{{ item.label }}</span>
        </router-link>
      </div>

    </nav>

    <div class="border-t border-black/10 p-4">
      <button
        type="button"
        class="flex w-full items-center gap-3 rounded-md border border-transparent px-3 py-2 text-left text-red-600 transition hover:border-red-200 hover:bg-red-50"
        @click="handleSignOut"
      >
        <LogOut class="h-5 w-5" />
        <span class="text-base font-medium">Sign out</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@stores/AuthStore.js'
import {
  Calendar,
  Plane,
  LogOut,
  Scissors,
  User,
  SquareUserRound,
  X,
  Clock,
  Star,
} from 'lucide-vue-next'

defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close'])
const router = useRouter()
const auth = useAuthStore()

const menuItems = [
  { id: 1, label: 'Services', to: '/admin/services', icon: Scissors },
  { id: 2, label: 'Employees', to: '/admin/employees', icon: User },
  { id: 3, label: 'Time offs', to: '/admin/time-offs', icon: Plane },
  { id: 4, label: 'Appointments', to: '/admin/appointments', icon: Calendar },
  { id: 5, label: 'Reviews', to: '/admin/reviews', icon: Star },
  { id: 6, label: 'Schedule', to: '/admin/schedule', icon: Clock },
  { id: 7, label: 'Shop Profile', to: '/admin/shop-profile', icon: SquareUserRound },
]

async function handleSignOut() {
  emit('close')
  await auth.logout()
  router.push('/admin/login')
}
</script>
