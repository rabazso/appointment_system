<template>
  <button :type="type"
    class="inline-flex items-center justify-center whitespace-nowrap rounded-md bg-secondary px-4 py-2 text-base font-semibold text-primary transition duration-100 hover:bg-secondary-100 shadow-sm hover:shadow-lg active:translate-y-px"
    v-bind="$attrs">
    <slot />
  </button>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'button'
  }
})
</script>