<template>
  <ModalShell content-class="max-w-sm" @close="$emit('close')">
    <ModalHeader
      title="Delete employee"
      description="This will also remove the employee account and all related configuration records."
    />

    <div class="space-y-4">
      <p class=" leading-6 text-center">
        Are you sure you want to delete <br>
        <span class="font-semibold">{{ employee.name }}</span>?
      </p>

      <div class="flex justify-end gap-3">
        <button
          type="button"
          class="inline-flex py-2 items-center justify-center rounded-xl border border-black/10 bg-white px-4 text-sm font-medium"
          @click="$emit('close')"
        >
          Cancel
        </button>
        <button
          type="button"
          class="inline-flex py-2 items-center justify-center rounded-xl bg-secondary px-4 text-sm font-medium text-black"
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
  employee: {
    type: Object,
    required: true,
  },
})
</script>
