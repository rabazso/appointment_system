<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 px-4 py-6"
    @click.self="$emit('close')"
  >
    <div
      class="relative flex max-h-[calc(100vh-3rem)] w-full max-w-[500px] flex-col overflow-hidden rounded-2xl bg-white p-4"
    >
      <button
        v-if="showBack"
        type="button"
        class="absolute left-2 top-2"
        @click="$emit('back')"
      >
        <ChevronLeft class="h-8 w-8" />
      </button>

      <button
        type="button"
        class="absolute right-2 top-2"
        @click="$emit('close')"
      >
        <X class="h-8 w-8" />
      </button>

      <div class="min-h-0 flex-1 overflow-y-auto p-1 pt-8">
        <slot />
      </div>

      <div
        v-if="$slots.footer"
        class="shrink-0 border-t border-slate-200 bg-white px-1 pt-4"
      >
        <slot name="footer" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ChevronLeft, X } from 'lucide-vue-next'

defineEmits(['back', 'close'])

defineProps({
  showBack: {
    type: Boolean,
    default: false,
  },
})
</script>
