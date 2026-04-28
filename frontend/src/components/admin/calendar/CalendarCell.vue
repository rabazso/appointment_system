<template>
  <div
    class="relative flex h-full flex-col overflow-hidden transition duration-200 border border-transparent hover:border-black hover:rounded-sm"
    :class="[
      backgroundClass,
      cellClass,
      {
        '!border-black rounded-sm': isToday,
        'opacity-75': !isInCurrentMonth,
      },
    ]"
    @click="emit('day-click')"
  >
    <div
      v-if="cellBackgroundClass || cellBackgroundStyle"
      class="absolute inset-x-0 bottom-0 transition-all duration-300"
      :class="cellBackgroundClass"
      :style="cellBackgroundStyle"
    ></div>

    <div
      v-if="cellOverlayClass"
      class="pointer-events-none absolute inset-0"
      :class="cellOverlayClass"
    ></div>

    <div class="relative z-1 flex shrink-0 items-start justify-end leading-none text-sm font-semibold text-black">
      <span class="px-0.5" :class="dayBadgeBackgroundClass">
        {{ dayNumber }}
      </span>
    </div>

    <div class="absolute inset-0 z-1 flex items-center justify-center">
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
  cellClass: String,
  cellBackgroundClass: String,
  cellOverlayClass: String,
  cellBackgroundStyle: {
    type: [Object, String],
    default: undefined,
  },
  backgroundClass: {
    type: String,
    default: 'bg-white',
  },
  dayBadgeBackgroundClass: {
    type: String,
    default: 'bg-white',
  },
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
