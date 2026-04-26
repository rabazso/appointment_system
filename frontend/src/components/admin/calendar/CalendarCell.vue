<template>
  <div
    class="relative flex h-full flex-col overflow-hidden bg-white transition duration-200 border border-transparent hover:border-black hover:rounded-sm"
    :class="{
      '!border-black rounded-sm': isToday,
      'opacity-75': !isInCurrentMonth,
    }"
    @click="emit('day-click')"
  >
    <div class="relative z-10 flex shrink-0 items-start justify-end leading-none text-sm font-semibold text-black">
      <span class="bg-white px-0.5">
        {{ dayNumber }}
      </span>
    </div>

    <div class="absolute inset-0 flex items-center justify-center">
      <span
        v-if="dotContent"
        class="mx-auto h-1.5 w-1.5 rounded-full bg-black leading-none sm:hidden"
        :class="dotContentClasses"
      ></span>

      <span
        v-if="content"
        class="hidden max-h-full max-w-full leading-none p-1.5 whitespace-pre-line sm:inline-flex"
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
  dotContentClass: String,
  isPast: Boolean,
  isToday: Boolean,
  isInCurrentMonth: Boolean,
  date: String,
})

const emit = defineEmits(['day-click'])

const dayNumber = computed(() => {
  return parseISODate(props.date).getDate()
})

const contentClasses = computed(() => {
  if (props.contentClass) return props.contentClass
  return ''
})

const dotContentClasses = computed(() => {
  if (props.dotContentClass) return props.dotContentClass
  return 'bg-black'
})
</script>
