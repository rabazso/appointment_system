<template>
  <div
    class="fixed inset-0 z-[110] flex items-center justify-center overflow-y-auto bg-black/50 px-4 py-6"
    @click.self="closeFromBackdrop"
  >
    <div
      class="relative flex max-h-[calc(100vh-3rem)] w-full flex-col overflow-hidden rounded-2xl bg-white p-4"
      :class="contentClass"
    >
      <Button type="button" variant="ghost" size="icon-lg"
        class="absolute right-2 top-2 rounded-full text-slate-600 shadow-none hover:rounded-full hover:bg-slate-100 hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-50"
        :disabled="closeDisabled"
        @click="emit('close')"
      >
        <X class="h-8 w-8" />
      </Button>

      <div class="min-h-0 flex-1 overflow-y-auto p-1 pt-8">
        <div v-if="title || description" class="mb-6">
          <h2 v-if="title" class="text-2xl font-semibold text-black">{{ title }}</h2>
        </div>

        <slot />
      </div>

      <div
        v-if="$slots.footer"
        class="shrink-0 border-t border-slate-200 bg-white px-1 mt-4"
      >
        <slot name="footer" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { X } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

const emit = defineEmits(['close'])

const props = defineProps({
  title: {
    type: String,
    default: '',
  },
  description: {
    type: String,
    default: '',
  },
  contentClass: {
    type: String,
    default: 'max-w-lg',
  },
  closeOnBackdrop: {
    type: Boolean,
    default: false,
  },
  closeDisabled: {
    type: Boolean,
    default: false,
  },
})

function closeFromBackdrop() {
  if (props.closeOnBackdrop) {
    emit('close')
  }
}
</script>
