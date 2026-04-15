<script setup>
import { computed, ref, watch } from 'vue'
import { CalendarDays, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { toISO } from '@/utils/date'

const props = defineProps({
  appointments: {
    type: Array,
    required: true,
  },
  selectedAppointmentIds: {
    type: Array,
    default: () => [],
  },
})

defineEmits([
  'cancel-appointment',
  'complete-appointment',
  'toggle-appointment-selection',
  'clear-appointment-selection',
  'cancel-selected-appointments',
])

const STATUS_OPTIONS = [
  { value: 'all', label: 'All statuses' },
  { value: 'confirmed', label: 'Confirmed' },
  { value: 'pending', label: 'Pending' },
  { value: 'completed', label: 'Completed' },
  { value: 'cancelled', label: 'Cancelled' },
  { value: 'no_show', label: 'No show' },
]

const viewMode = ref('daily')
const statusFilter = ref('all')
const selectedDate = ref(toISO(new Date()))

const normalizedAppointments = computed(() =>
  props.appointments
    .map((appointment) => {
      const startDate = new Date(appointment?.start_datetime)
      if (!Number.isFinite(startDate.getTime())) {
        return null
      }

      return {
        ...appointment,
        _startDate: startDate,
        _dateKey: toISO(startDate),
      }
    })
    .filter(Boolean)
    .sort((left, right) => left._startDate.getTime() - right._startDate.getTime()),
)

watch(normalizedAppointments, (appointments) => {
  if (!appointments.length) {
    selectedDate.value = toISO(new Date())
    return
  }

  const hasCurrentDate = appointments.some((appointment) => appointment._dateKey === selectedDate.value)
  if (hasCurrentDate) {
    return
  }

  const todayKey = toISO(new Date())
  const todaysAppointment = appointments.find((appointment) => appointment._dateKey === todayKey)

  selectedDate.value = todaysAppointment?._dateKey || appointments[0]._dateKey
}, { immediate: true })

const filteredAppointments = computed(() => {
  if (statusFilter.value === 'all') {
    return normalizedAppointments.value
  }

  return normalizedAppointments.value.filter((appointment) => appointment.status === statusFilter.value)
})

const visibleAppointmentCount = computed(() => filteredAppointments.value.length)

const rangeLabel = computed(() => {
  const [year, month, day] = selectedDate.value.split('-').map(Number)
  return new Date(year, month - 1, day).toLocaleDateString('en-US', {
    weekday: 'long',
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
})

const shiftRange = (amount) => {
  const [year, month, day] = selectedDate.value.split('-').map(Number)
  const baseDate = new Date(year, month - 1, day)
  baseDate.setDate(baseDate.getDate() + (viewMode.value === 'daily' ? amount : amount * 7))
  selectedDate.value = toISO(baseDate)
}

const jumpToToday = () => {
  selectedDate.value = toISO(new Date())
}
</script>

<template>
  <section class="rounded-lg bg-white shadow-sm">
    <div class="border-b border-black/10 p-4 md:p-6">
      <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex flex-col gap-3 md:flex-row md:flex-wrap md:items-center">
          <div class="flex items-center gap-2">
            <button
              type="button"
              class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-600 transition hover:border-black"
              @click="shiftRange(-1)"
            >
              <ChevronLeft class="h-4 w-4" />
            </button>

            <label class="flex items-center gap-3 rounded-lg border border-black/10 bg-slate-50 px-4 py-2 text-sm font-medium text-gray-700">
              <CalendarDays class="h-4 w-4 text-gray-500" />
              <input
                v-model="selectedDate"
                type="date"
                class="bg-transparent outline-none"
              >
            </label>

            <button
              type="button"
              class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-600 transition hover:border-black"
              @click="shiftRange(1)"
            >
              <ChevronRight class="h-4 w-4" />
            </button>
          </div>

          <button
            type="button"
            class="rounded-lg border border-black/10 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-black"
            @click="jumpToToday"
          >
            Today
          </button>

          <label class="flex items-center gap-3 rounded-lg border border-black/10 bg-white px-4 py-2 text-sm font-medium text-gray-700">
            <span>Status</span>
            <select
              v-model="statusFilter"
              class="bg-transparent pr-6 outline-none"
            >
              <option
                v-for="option in STATUS_OPTIONS"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
          </label>
        </div>

        <div class="inline-flex rounded-lg border border-black/10 bg-slate-100 p-1">
          <button
            type="button"
            class="rounded-md px-4 py-2 text-sm font-medium transition"
            :class="viewMode === 'daily' ? 'bg-white text-black shadow-sm' : 'text-gray-500'"
            @click="viewMode = 'daily'"
          >
            Daily
          </button>
          <button
            type="button"
            class="rounded-md px-4 py-2 text-sm font-medium transition"
            :class="viewMode === 'weekly' ? 'bg-white text-black shadow-sm' : 'text-gray-500'"
            @click="viewMode = 'weekly'"
          >
            Weekly
          </button>
        </div>
      </div>

      <div class="mt-4">
        <p class="text-sm font-semibold text-black">{{ rangeLabel }}</p>
        <p class="text-xs text-gray-500">
          {{ visibleAppointmentCount }} appointment{{ visibleAppointmentCount === 1 ? '' : 's' }} available for this filter set
        </p>
      </div>
    </div>

    <div class="p-6">
      <div class="rounded-lg border border-dashed border-black/10 bg-slate-50 px-4 py-10 text-center text-sm text-gray-500">
        The filtered appointment workspace will render here.
      </div>
    </div>
  </section>
</template>
