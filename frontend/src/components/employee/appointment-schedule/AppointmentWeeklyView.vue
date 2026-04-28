<script setup>
const props = defineProps({
  timeLabels: { type: Array, required: true },
  weeklyColumns: { type: Array, required: true },
  getSegmentStyle: { type: Function, required: true },
  getStatusStyles: { type: Function, required: true }
})

const emit = defineEmits(['open-day'])

function dayKey(date) {
  const d = new Date(date)
  return d.toISOString().split('T')[0]
}

function appointmentCount(column) {
  return column.segments.filter((segment) => segment.kind === 'appointment').length
}

function appointmentCountLabel(column) {
  const count = appointmentCount(column)
  return `${count} appointment${count === 1 ? '' : 's'}`
}
</script>

<template>
  <div class="space-y-4">
    <div class="space-y-2 lg:hidden">
      <button v-for="column in props.weeklyColumns" :key="dayKey(column.date)" type="button"
        class="flex w-full items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left transition hover:border-slate-300"
        @click="emit('open-day', column.date)"
      >
        <div class="flex min-w-0 items-center gap-3">
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
            {{ column.label }}
          </p>
          <p class="text-sm font-semibold text-slate-900">
            {{ column.date.getDate() }}
          </p>
        </div>

        <span class="text-xs font-medium text-slate-400">{{ appointmentCountLabel(column) }}</span>
      </button>
    </div>

    <div class="hidden gap-4 lg:grid lg:grid-cols-[72px_1fr]">
      <div class="hidden lg:flex lg:h-[70vh] lg:min-h-[720px] lg:flex-col">
        <div class="h-[73px] shrink-0"></div>
        <div class="flex min-h-0 flex-1 flex-col justify-between">
          <span v-for="label in props.timeLabels" :key="label"
            class="text-xs font-medium uppercase tracking-[0.22em] text-slate-400"
          >
            {{ label }}
          </span>
        </div>
      </div>

      <div class="hidden h-[70vh] min-h-[720px] grid-cols-7 gap-3 lg:grid">
        <div v-for="column in props.weeklyColumns" :key="column.date.toISOString()"
          class="flex min-h-0 cursor-pointer flex-col overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-100 transition hover:border-slate-300"
          @click="emit('open-day', column.date)"
        >
          <div class="border-b border-slate-200 bg-white px-3 py-3 text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">{{ column.label }}</p>
            <p class="mt-1 text-base font-semibold text-slate-900">{{ column.date.getDate() }}</p>
          </div>

          <div class="flex min-h-0 flex-1 flex-col gap-px p-2">
            <div v-for="segment in column.segments" :key="segment.id" class="min-h-0 rounded-[1rem]"
              :style="props.getSegmentStyle(segment.duration)"
              :class="segment.kind === 'appointment' ? props.getStatusStyles(segment.appointment.status) : 'bg-slate-100'"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
