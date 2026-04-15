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

const selectedAppointmentIdSet = computed(() => new Set(props.selectedAppointmentIds))

const normalizedAppointments = computed(() =>
  props.appointments
    .map((appointment) => {
      const startDate = new Date(appointment?.start_datetime)
      if (!Number.isFinite(startDate.getTime())) {
        return null
      }

      const endDate = appointment?.end_datetime ? new Date(appointment.end_datetime) : null

      return {
        ...appointment,
        _startDate: startDate,
        _endDate: endDate && Number.isFinite(endDate.getTime()) ? endDate : null,
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

const dailyAppointments = computed(() =>
  filteredAppointments.value.filter((appointment) => appointment._dateKey === selectedDate.value),
)

const visibleAppointmentCount = computed(() => dailyAppointments.value.length)

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

const isAppointmentCancellable = (status) => ['pending', 'confirmed'].includes(status)

const formatPrice = (value) => {
  const amount = Number(value)
  if (!Number.isFinite(amount)) {
    return value || '-'
  }

  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: amount % 1 === 0 ? 0 : 2,
  }).format(amount)
}

const formatContact = (appointment) => appointment.phone || appointment.email || 'No contact provided'

const formatTime = (date) => date?.toLocaleTimeString('en-US', {
  hour: '2-digit',
  minute: '2-digit',
  hour12: false,
}) || '--:--'

const formatSecondaryTime = (date) => date?.toLocaleTimeString('en-US', {
  hour: '2-digit',
  minute: '2-digit',
  hour12: true,
}) || ''

const getStatusText = (status) => ({
  confirmed: 'Confirmed',
  pending: 'Pending',
  completed: 'Completed',
  cancelled: 'Cancelled',
  no_show: 'No show',
}[status] || status)

const getStatusClass = (status) => ({
  confirmed: 'bg-green-100 text-green-800',
  pending: 'bg-amber-100 text-amber-800',
  completed: 'bg-slate-200 text-slate-800',
  cancelled: 'bg-red-100 text-red-700',
  no_show: 'bg-rose-100 text-rose-700',
}[status] || 'bg-slate-100 text-slate-700')
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
      <div
        v-if="selectedAppointmentIds.length > 0"
        class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-red-200 bg-red-50 p-3"
      >
        <p class="text-sm font-medium text-red-800">
          {{ selectedAppointmentIds.length }} appointment{{ selectedAppointmentIds.length === 1 ? '' : 's' }} selected
        </p>
        <div class="flex items-center gap-2">
          <button
            type="button"
            class="rounded-md border border-red-300 px-3 py-1.5 text-xs font-medium text-red-800 transition hover:bg-red-100"
            @click="$emit('clear-appointment-selection')"
          >
            Clear
          </button>
          <button
            type="button"
            class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-red-700"
            @click="$emit('cancel-selected-appointments')"
          >
            Cancel Selected
          </button>
        </div>
      </div>

      <template v-if="viewMode === 'daily'">
        <div v-if="dailyAppointments.length === 0" class="rounded-lg border border-dashed border-black/10 bg-slate-50 px-4 py-10 text-center text-sm text-gray-500">
          No appointments match this day and status filter.
        </div>

        <div v-else class="overflow-hidden rounded-lg border border-black/10">
          <div class="hidden bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 md:grid md:grid-cols-[120px,minmax(0,2fr),minmax(0,1.4fr),110px,130px,130px] md:gap-4">
            <span>Time</span>
            <span>Guest</span>
            <span>Service</span>
            <span>Price</span>
            <span>Status</span>
            <span>Actions</span>
          </div>

          <div
            v-for="appointment in dailyAppointments"
            :key="appointment.id"
            class="border-t border-black/10 px-4 py-4 first:border-t-0 md:grid md:grid-cols-[120px,minmax(0,2fr),minmax(0,1.4fr),110px,130px,130px] md:items-center md:gap-4"
          >
            <div class="flex items-start gap-3">
              <label v-if="isAppointmentCancellable(appointment.status)" class="mt-1 inline-flex items-center">
                <input
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500"
                  :checked="selectedAppointmentIdSet.has(appointment.id)"
                  @change="$emit('toggle-appointment-selection', appointment.id)"
                >
              </label>
              <div>
                <p class="text-2xl font-semibold text-black">{{ formatTime(appointment._startDate) }}</p>
                <p class="text-xs text-gray-500">
                  {{ formatSecondaryTime(appointment._endDate || appointment._startDate) }}
                </p>
              </div>
            </div>

            <div class="mt-4 flex items-center gap-3 md:mt-0">
              <div class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700">
                {{ appointment.client?.charAt(0) || '?' }}
              </div>
              <div class="min-w-0">
                <p class="truncate font-semibold text-black">{{ appointment.client }}</p>
                <p class="truncate text-sm text-gray-500">{{ formatContact(appointment) }}</p>
              </div>
            </div>

            <div class="mt-4 md:mt-0">
              <span class="inline-flex rounded-md bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700">
                {{ appointment.service || 'Service' }}
              </span>
            </div>

            <div class="mt-4 text-sm font-semibold text-black md:mt-0">
              {{ formatPrice(appointment.price) }}
            </div>

            <div class="mt-4 md:mt-0">
              <span :class="['inline-flex rounded-full px-3 py-1 text-xs font-medium', getStatusClass(appointment.status)]">
                {{ getStatusText(appointment.status) }}
              </span>
            </div>

            <div class="mt-4 flex items-center gap-2 md:mt-0">
              <button
                v-if="appointment.status === 'confirmed'"
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-emerald-200 text-emerald-700 transition hover:bg-emerald-50"
                title="Complete appointment"
                @click="$emit('complete-appointment', appointment.id)"
              >
                <Check class="h-4 w-4" />
              </button>
              <button
                v-if="isAppointmentCancellable(appointment.status)"
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-red-200 text-red-600 transition hover:bg-red-50"
                title="Cancel appointment"
                @click="$emit('cancel-appointment', appointment.id)"
              >
                <X class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </template>

      <div v-else class="rounded-lg border border-dashed border-black/10 bg-slate-50 px-4 py-10 text-center text-sm text-gray-500">
        Weekly appointments view will land in the next pass.
      </div>
    </div>
  </section>
</template>
