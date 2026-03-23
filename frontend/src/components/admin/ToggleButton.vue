<template>
  <button type="button" @click="$emit('update:modelValue', !modelValue)"
    class="relative h-6 w-12 rounded-full transition-colors duration-300 ease-in-out"
    :class="modelValue ? 'bg-black' : 'bg-slate-200'">
    <span
      class="absolute h-5 w-5 top-1/2 -translate-y-1/2 rounded-full bg-white shadow transition-all duration-100 ease-linear"
      :class="modelValue ? 'left-6.5' : 'left-0.5'" />
  </button>
</template>

<script setup>
defineProps({
  modelValue: Boolean
})

defineEmits(['update:modelValue'])
</script>