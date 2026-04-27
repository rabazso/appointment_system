<script setup>
import { computed, onMounted } from 'vue'

const props = defineProps({
  message: { type: String, required: true },
  duration: { type: Number, default: 3000 },
  variant: {
    type: String,
    default: 'success',
    validator: (value) => ['success', 'error'].includes(value),
  },
})

const emit = defineEmits(['close'])

onMounted(() => {
  setTimeout(() => emit('close'), props.duration)
})

const toastClass = computed(() => {
  if (props.variant === 'error') {
    return 'bg-red-600 text-white'
  }

  return 'bg-green-600 text-white'
})
</script>

<template>
  <div data-testid="toast"
    :class="[
           'fixed bottom-5 right-4 z-[100]
           px-4 py-3 rounded-lg shadow-lg
           animate-in fade-in slide-in-from-top-2',
           toastClass
         ]"
  >
    {{ message }}
  </div>
</template>
