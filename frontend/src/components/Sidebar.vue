<template>
  <div
    v-if="isOpen"
    :class="['fixed inset-0 z-2 bg-black/50', overlayHiddenClass]"
    @click="$emit('close')"
  />

  <aside
    :class="[
      'fixed top-0 left-0 z-2 flex h-screen w-64 shrink-0 flex-col border-r border-black/10 bg-white transition-transform duration-300',
      isOpen ? 'translate-x-0' : '-translate-x-full',
      stickyClass,
    ]"
  >
    <div :class="['flex justify-end p-4', closeHiddenClass]">
      <button type="button" @click="$emit('close')">
        <X class="h-6 w-6" />
      </button>
    </div>

    <div v-if="role === 'admin'" class="mx-8 mb-8 py-4 text-center md:mt-8 md:mb-8">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-gray-500">Admin</p>
      <p class="mt-2 text-lg font-semibold text-black">Interface</p>
    </div>

    <div v-else class="mx-8 mb-8 py-4 text-center md:mt-8 md:mb-8">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-gray-500">Employee</p>
      <p class="mt-2 text-lg font-semibold text-black">Interface</p>
    </div>

    <nav class="flex-1 overflow-auto px-4">
      <template v-if="role === 'admin'">
        <div class="space-y-2">
          <router-link
            v-for="item in adminMenuItems"
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
      </template>

      <div v-else class="space-y-2">
        <router-link
          v-for="item in employeeMenuItems"
          :key="item.to"
          :to="item.to"
          class="flex w-full items-center gap-3 rounded-md border border-transparent px-3 py-2 text-left transition hover:border-black"
          active-class="bg-secondary shadow-sm"
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
import { computed } from 'vue'
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
  Eye,
  Settings2,
  UserRound,
} from 'lucide-vue-next'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  role: {
    type: String,
    default: 'employee',
  },
  desktopBreakpoint: {
    type: String,
    default: 'xl',
  },
})

const emit = defineEmits(['close'])

const router = useRouter()
const auth = useAuthStore()

const overlayHiddenClass = computed(() =>
  props.desktopBreakpoint === 'lg' ? 'lg:hidden' : 'xl:hidden',
)
const closeHiddenClass = computed(() =>
  props.desktopBreakpoint === 'lg' ? 'lg:hidden' : 'xl:hidden',
)
const stickyClass = computed(() =>
  props.desktopBreakpoint === 'lg' ? 'lg:sticky lg:translate-x-0' : 'xl:sticky xl:translate-x-0',
)

const adminMenuItems = [
  { label: 'Services', to: '/admin/services', icon: Scissors },
  { label: 'Employees', to: '/admin/employees', icon: User },
  { label: 'Time offs', to: '/admin/time-offs', icon: Plane },
  { label: 'Appointments', to: '/admin/appointments', icon: Calendar },
  { label: 'Reviews', to: '/admin/reviews', icon: Star },
  { label: 'Schedule', to: '/admin/schedule', icon: Clock },
  { label: 'Shop Profile', to: '/admin/shop-profile', icon: SquareUserRound },
]

const employeeMenuItems = [
  { label: 'Appointments', icon: Calendar, to: '/employee/appointments' },
  { label: 'Reviews', icon: Eye, to: '/employee/reviews' },
  { label: 'My Configuration', icon: Settings2, to: '/employee/configuration' },
  { label: 'Profile', icon: UserRound, to: '/employee/profile' },
  { label: 'Time Off', icon: Plane, to: '/employee/time-off' },
]

async function handleSignOut() {
  emit('close')
  await auth.logout(props.role === 'admin' ? 'admin' : 'employee')
  router.push(props.role === 'admin' ? '/admin/login' : '/employee/login')
}
</script>
