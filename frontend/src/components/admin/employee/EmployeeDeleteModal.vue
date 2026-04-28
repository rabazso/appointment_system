<template>
  <ConfirmDeleteModal
    title="Delete employee"
    description="This will also remove the employee account and all related configuration records."
    :target-name="employee.name"
    @close="$emit('close')"
    @confirm="$emit('confirm')"
  />
</template>

<script setup>
import ConfirmDeleteModal from '@/components/admin/ConfirmDeleteModal.vue'

defineEmits(['close', 'confirm'])

defineProps({
  employee: {
    type: Object,
    required: true,
  },
})
</script>
