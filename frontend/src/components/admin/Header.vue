<template>
  <header
    class="mb-8 flex flex-col gap-4 rounded-2xl bg-white p-4 shadow-sm md:flex-row md:items-center md:justify-between md:p-8"
  >
    <div class="flex items-center gap-4">
      <button
        v-if="showMenu"
        type="button"
        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-black/10 bg-white md:hidden"
        @click="emit('menu-click')"
      >
        <Menu class="h-5 w-5" />
      </button>

      <div class="min-w-0">
        <h1 class="text-4xl font-semibold text-black">
          {{ title }}
        </h1>
        <p class="mt-1 text-xs text-gray-500">
          {{ description }}
        </p>
      </div>
    </div>

    <div v-if="showAction && (slots.actions || actionLabel)" class="flex justify-end">
      <slot name="actions">
        <Button @click="emit('action-click')">
          {{ actionLabel }}
        </Button>
      </slot>
    </div>
  </header>
</template>

<script setup>
import { Menu } from 'lucide-vue-next'
import Button from '@/components/admin/Button.vue'
import { useSlots } from 'vue'

defineProps({
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    required: true,
  },
  actionLabel: {
    type: String,
    default: '',
  },
  showMenu: {
    type: Boolean,
    default: true,
  },
  showAction: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['menu-click', 'action-click'])
const slots = useSlots()
</script>
