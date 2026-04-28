<template>
  <div class="space-y-3">
    <div v-if="!dateDisabled">
      <label class="mr-4 text-sm">Valid from:</label>
      <PopoverRoot v-model:open="isDateOpen">
        <PopoverTrigger as-child>
          <button
            type="button"
            class="mt-1 flex min-w-[220px] items-center justify-between rounded-lg border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black [font-variant-numeric:tabular-nums]"
          >
            <span>{{ modelValue || 'YYYY-MM-DD' }}</span>
            <CalendarIcon class="h-4 w-4 text-slate-500" />
          </button>
        </PopoverTrigger>
        <PopoverPortal>
          <PopoverContent
            side="bottom"
            align="start"
            :side-offset="6"
            :collision-padding="12"
            position-strategy="fixed"
            class="z-[90] w-auto rounded-xl border border-slate-200 bg-white p-2 shadow-lg"
          >
            <Calendar
              :model-value="calendarValue(modelValue)"
              layout="month-and-year"
              class="rounded-md"
              :min-value="calendarValue(min)"
              :max-value="calendarValue(max)"
              @update:model-value="setModelDate"
            />
          </PopoverContent>
        </PopoverPortal>
      </PopoverRoot>
    </div>

    <div class="flex justify-end gap-3">
      <button
        type="button"
        class="inline-flex h-10 items-center justify-center rounded-xl border border-black/10 bg-white px-4 font-semibold text-slate-700 transition hover:bg-slate-50"
        @click="emit('cancel')"
      >
        Cancel
      </button>
      <button
        type="button"
        class="inline-flex h-10 items-center justify-center rounded-xl bg-[#ff9838] px-6 text-[14px] font-semibold text-black shadow-[0_10px_24px_rgba(249,115,22,0.18)] transition hover:bg-[#ffab5c] disabled:cursor-not-allowed disabled:opacity-50"
        :disabled="disabled || !modelValue"
        @click="emit('save')"
      >
        {{ saving ? 'Saving...' : 'Save' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { parseDate } from '@internationalized/date'
import { Calendar as CalendarIcon } from 'lucide-vue-next'
import { ref } from 'vue'
import { PopoverContent, PopoverPortal, PopoverRoot, PopoverTrigger } from 'reka-ui'
import { Calendar } from '@/components/ui/calendar'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  saving: {
    type: Boolean,
    default: false,
  },
  dateDisabled: {
    type: Boolean,
    default: false,
  },
  min: {
    type: String,
    default: null,
  },
  max: {
    type: String,
    default: null,
  },
})

const emit = defineEmits(['cancel', 'save', 'update:modelValue'])
const isDateOpen = ref(false)

function calendarValue(value) {
  if (!value) return undefined

  try {
    return parseDate(value)
  } catch {
    return undefined
  }
}

function setModelDate(value) {
  emit('update:modelValue', value?.toString?.() || '')
  isDateOpen.value = false
}
</script>
