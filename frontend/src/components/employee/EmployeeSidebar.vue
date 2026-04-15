<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-1 bg-black/50 md:hidden"
    @click="$emit('close')"
  />

  <aside
    :class="[
      'fixed top-0 left-0 z-2 flex h-screen w-64 shrink-0 flex-col border-r border-black/10 bg-white transition-transform duration-300',
      isOpen ? 'translate-x-0' : '-translate-x-full',
      'lg:sticky lg:translate-x-0'
    ]"
  >
    <div class="flex justify-end p-4 md:hidden">
      <button type="button" @click="$emit('close')">
        <X class="h-6 w-6" />
      </button>
    </div>

    <div class="mx-8 mb-8 py-4 text-center md:mt-8 md:mb-8">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-gray-500">Employee</p>
      <p class="mt-2 text-lg font-semibold text-black">Dashboard</p>
    </div>

    <nav class="flex-1 overflow-auto px-4">
      <div class="space-y-2">
        <button
          v-for="item in menuItems"
          :key="item.id"
          type="button"
          :class="[
            'flex w-full items-center gap-3 rounded-md border px-3 py-2 text-left transition',
            currentSection === item.id
              ? 'border-transparent bg-secondary shadow-sm'
              : 'border-transparent hover:border-black'
          ]"
          @click="handleSelect(item.id)"
        >
          <component :is="item.icon" class="h-5 w-5" />
          <span class="text-base font-medium">{{ item.label }}</span>
        </button>
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
import { Calendar, Clock3, LogOut, UserRound, X } from 'lucide-vue-next'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  currentSection: {
    type: String,
    default: 'appointments',
  },
})

const emit = defineEmits(['close', 'select-section'])
const router = useRouter()
const auth = useAuthStore()

const menuItems = [
  { id: 'appointments', label: 'Appointments', icon: Calendar },
  { id: 'profile', label: 'Profile', icon: UserRound },
  { id: 'time-off', label: 'Time Off', icon: Clock3 },
]

function handleSelect(section) {
  emit('select-section', section)
  emit('close')
}

async function handleSignOut() {
  emit('close')
  await auth.logout()
  router.push('/employee/login')
}
</script>
