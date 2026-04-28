<template>
  <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3">
    <div class="text-sm text-slate-500">
      <span class="font-medium text-slate-700">Valid from:</span>
      {{ validFrom || 'Not set' }}
      <span class="mx-2 text-slate-300">|</span>
      <span class="font-medium text-slate-700">Valid to:</span>
      {{ validTo || 'Open-ended' }}
    </div>

    <div v-if="showActions" class="flex gap-2">
      <button
        type="button"
        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-500 transition hover:bg-slate-50"
        @click="$emit('edit')"
      >
        <Pencil class="h-4 w-4" />
      </button>
      <button
        type="button"
        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-500 transition hover:bg-slate-50"
        @click="$emit('delete')"
      >
        <Trash class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { Pencil, Trash } from 'lucide-vue-next'

defineEmits(['edit', 'delete'])

defineProps({
  validFrom: {
    type: String,
    default: null,
  },
  validTo: {
    type: String,
    default: null,
  },
  showActions: {
    type: Boolean,
    default: true,
  },
})
</script>
