<script setup>
import Toast from '@/components/ui/Toast.vue'
import { useToastStore } from '@/stores/ToastStore.js'

const toast = useToastStore()
</script>

<template>
  <RouterView />
  <Toast
    v-if="toast.visible"
    :key="toast.id"
    :message="toast.message"
    :duration="toast.duration"
    :variant="toast.variant"
    @close="toast.hide()"
  />
</template>
