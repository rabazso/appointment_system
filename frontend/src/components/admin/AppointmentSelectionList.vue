<template>
  <div class="flex h-full min-h-0 flex-col">
    <div v-if="appointments.length" class="mb-3 flex shrink-0 justify-end">
      <button
        type="button"
        class="text-xs font-semibold underline"
        :class="tone === 'conflict' ? 'text-red-700' : 'text-emerald-700'"
        @click="$emit('toggle-all')"
      >
        {{ allSelected ? 'Clear selection' : 'Select all' }}
      </button>
    </div>

    <div
      v-if="appointments.length"
      class="min-h-0 flex-1 space-y-2 overflow-y-auto pr-1"
      :class="listClass"
    >
      <label
        v-for="appointment in appointments"
        :key="appointment.id"
        class="flex cursor-pointer gap-3 rounded-lg border bg-white p-3"
        :class="tone === 'conflict' ? 'border-rose-100' : 'border-emerald-100'"
      >
        <input
          type="checkbox"
          :checked="selectedAppointmentIds.includes(appointment.id)"
          class="mt-1"
          @change="$emit('toggle', appointment.id)"
        />
        <div class="min-w-0 flex-1">
          <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-semibold text-slate-900">
            <span>{{ formatDateTimeRange(appointment) }}</span>
          </div>
          <p class="mt-1 text-xs">
            {{ appointment.customer }}</p>
          <p class="mt-1 text-xs text-slate-500">
            {{ serviceNames(appointment) }}
          </p>
          <p class="mt-1 text-xs font-medium text-slate-500">
            {{ appointment.total_duration }} min · {{ formatPrice(appointment.total_price) }}
          </p>
        </div>
      </label>
    </div>

    <p v-else class="rounded-lg bg-white p-3 text-sm text-slate-500">
      {{ emptyLabel }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

defineEmits(['toggle', 'toggle-all'])

const props = defineProps({
  appointments: {
    type: Array,
    default: () => [],
  },
  selectedAppointmentIds: {
    type: Array,
    default: () => [],
  },
  tone: {
    type: String,
    default: 'conflict',
  },
  emptyLabel: {
    type: String,
    default: 'No appointments.',
  },
  listClass: {
    type: String,
    default: 'max-h-64',
  },
})

const allSelected = computed(() => (
  props.appointments.length > 0 &&
  props.appointments.every((appointment) => props.selectedAppointmentIds.includes(appointment.id))
))

function serviceNames(appointment) {
  return appointment.services
    ?.map((service) => service.name)
    .filter(Boolean)
    .join(', ') || 'No services'
}

function formatDateTimeRange(appointment) {
  if (!appointment.start_datetime) return ''

  const start = new Date(appointment.start_datetime)
  const end = appointment.end_datetime ? new Date(appointment.end_datetime) : null

  return [
    formatDate(start),
    `${formatTime(start)}${end ? `-${formatTime(end)}` : ''}`,
  ].join(' ')
}

function formatDate(date) {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  return `${year}.${month}.${day}`
}

function formatTime(date) {
  return new Intl.DateTimeFormat('hu-HU', {
    hour: 'numeric',
    minute: '2-digit',
  }).format(date)
}

function formatPrice(value) {
  return new Intl.NumberFormat('hu-HU', {
    style: 'currency',
    currency: 'HUF',
    maximumFractionDigits: 0,
  }).format(value ?? 0)
}
</script>
