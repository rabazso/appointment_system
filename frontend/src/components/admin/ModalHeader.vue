<template>
  <div class="mb-6">
    <h2 class="text-2xl font-semibold text-black">{{ title }}</h2>
    <p v-if="description" class="text-sm text-slate-500">{{ description }}</p>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    default: '',
  },
})
</script>
