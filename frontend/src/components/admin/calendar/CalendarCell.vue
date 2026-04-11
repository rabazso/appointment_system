<template>
  <div
    class="relative h-full bg-white transition duration-200 border border-transparent hover:border-black hover:rounded-sm"
  >
    <div class="absolute right-0 top-0 leading-none text-sm font-semibold text-black">
      {{ dayNumber }}
    </div>

    <div class="absolute inset-0 flex items-center justify-center">
      <span
        v-if="dotContent"
        class="mx-auto h-1 w-1 rounded-full bg-black sm:hidden"
      ></span>

      <span
        v-if="content"
        class="hidden sm:inline-flex w-fit"
        :class="contentClasses"
      >
        {{ content }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { parseISODate } from '@/utils/date'

const props = defineProps({
  content: String,
  contentClass: String,
  dotContent: Boolean,
  isInCurrentMonth: Boolean,
  date: String,
})

const dayNumber = computed(() => {
  return parseISODate(props.date).getDate()
})

const contentClasses = computed(() => {
  if (!props.isInCurrentMonth) return ''
  if (props.contentClass) return props.contentClass
  return ''
})
</script>
