<template>
  <ConfirmDeleteModal
    title="Delete service"
    description="This will remove the service and its availability history."
    :target-name="service.name"
    @close="$emit('close')"
    @confirm="$emit('confirm')"
  />
</template>

<script setup>
import ConfirmDeleteModal from '@/components/admin/ConfirmDeleteModal.vue'

defineEmits(['close', 'confirm'])

defineProps({
  service: {
    type: Object,
    required: true,
  },
})
</script>
