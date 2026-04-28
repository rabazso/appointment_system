<template>
  <ModalShell content-class="max-w-sm" @close="$emit('close')">
    <ModalHeader :title="title" :description="description" />

    <div class="space-y-4">
      <p class="text-sm leading-6 text-slate-600">
        {{ questionPrefix }}
        <span class="font-semibold text-slate-900">{{ targetName }}</span>?
      </p>

      <div class="flex justify-end gap-3">
        <button
          type="button"
          class="inline-flex h-10 items-center justify-center rounded-xl border border-black/10 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
          @click="$emit('close')"
        >
          Cancel
        </button>
        <button
          type="button"
          class="inline-flex h-10 items-center justify-center rounded-xl bg-[#ff9838] px-4 text-sm font-semibold text-black shadow-[0_10px_24px_rgba(249,115,22,0.18)] transition hover:bg-[#ffab5c]"
          @click="$emit('confirm')"
        >
          Confirm
        </button>
      </div>
    </div>
  </ModalShell>
</template>

<script setup>
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'

defineEmits(['close', 'confirm'])

defineProps({
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    required: true,
  },
  questionPrefix: {
    type: String,
    default: 'Are you sure you want to delete ',
  },
  targetName: {
    type: String,
    required: true,
  },
})
</script>
