<script setup>
import { onMounted } from 'vue'

const props = defineProps({
  message: { type: String, required: true },
  duration: { type: Number, default: 3000 },
})

const emit = defineEmits(['close'])

onMounted(() => {
  setTimeout(() => emit('close'), props.duration)
})
</script>

<template>
  <div data-testid="toast"
    class="fixed top-15 right-4 z-[100]
           bg-green-600 text-white
           px-4 py-3 rounded-lg shadow-lg
           animate-in fade-in slide-in-from-top-2"
  >
    {{ message }}
  </div>
</template>
