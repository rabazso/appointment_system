<script setup>
import { computed, nextTick, ref } from 'vue'
import AppointmentCancelledView from '@/components/employee/appointment-schedule/AppointmentCancelledView.vue'
import AppointmentDailyView from '@/components/employee/appointment-schedule/AppointmentDailyView.vue'
import AppointmentDetailModal from '@/components/employee/appointment-schedule/AppointmentDetailModal.vue'
import AppointmentMonthlyView from '@/components/employee/appointment-schedule/AppointmentMonthlyView.vue'
import AppointmentScheduleHeader from '@/components/employee/appointment-schedule/AppointmentScheduleHeader.vue'
import AppointmentWeeklyView from '@/components/employee/appointment-schedule/AppointmentWeeklyView.vue'

const props = defineProps({
  appointments: { type: Array, default: () => [] },
  workingHours: { type: Array, default: () => [] },
})

const emit = defineEmits(['cancel-appointment', 'complete-appointment', 'mark-no-show'])

const viewMode = ref('daily')
const cancelledOrderBy = ref('newest')
const selectedDate = ref(new Date())
const detailAppointment = ref(null)
const scheduleRoot = ref(null)

const isMonthlyView = computed(() => viewMode.value === 'monthly')
const monthlySummary = {}

const dayTitleFormatter = new Intl.DateTimeFormat('en-US', { weekday: 'long', month: 'long', day: 'numeric' })
const monthTitleFormatter = new Intl.DateTimeFormat('en-US', { month: 'long', year: 'numeric' })
const weekdayFormatter = new Intl.DateTimeFormat('en-US', { weekday: 'short' })

const workingHoursByWeekday = computed(() => {
  const days = {}

  props.workingHours.forEach((day) => {
    days[day.weekday] = {
      start: timeToMinutes(day.start_time),
      end: timeToMinutes(day.end_time),
    }
  })

  return days
})

const appointments = computed(() => {
  const list = []

  props.appointments.forEach((appointment) => {
    list.push({
      ...appointment,
      start: new Date(appointment.start_datetime),
      end: new Date(appointment.end_datetime),
    })
  })

  return list
})

const activeAppointments = computed(() =>
  appointments.value.filter((appointment) => appointment.status !== 'cancelled'),
)

const cancelledAppointments = computed(() =>
  appointments.value
    .filter((appointment) => appointment.status === 'cancelled')
    .sort((a, b) =>
      cancelledOrderBy.value === 'oldest'
        ? a.start - b.start
        : b.start - a.start,
    ),
)

const selectedDayWindow = computed(() => getWorkingWindow(selectedDate.value))

const selectedDayWorkingHoursNotice = computed(() =>
  selectedDayWindow.value ? '' : 'No valid working hours are configured for this day.',
)

const dailyAppointments = computed(() =>
  activeAppointments.value
    .filter((appointment) => isSameDay(appointment.start, selectedDate.value))
    .sort((a, b) => a.start - b.start),
)

const dailySegments = computed(() => {
  const window = selectedDayWindow.value
  if (!window) return []

  const segments = []
  let cursor = window.start

  dailyAppointments.value.forEach((appointment) => {
    const start = clamp(toMinutes(appointment.start), window.start, window.end)
    const end = clamp(toMinutes(appointment.end), window.start, window.end)

    if (start > cursor) {
      segments.push({ kind: 'empty', id: `empty-${cursor}`, start: cursor, end: start })
    }

    segments.push({ kind: 'appointment', id: appointment.id, start, end, appointment })
    cursor = end
  })

  if (cursor < window.end) {
    segments.push({ kind: 'empty', id: `empty-end-${cursor}`, start: cursor, end: window.end })
  }

  if (!segments.length) {
    segments.push({ kind: 'empty', id: 'empty-full', start: window.start, end: window.end })
  }

  return segments
})

const weekDates = computed(() => {
  const monday = getWeekMonday(selectedDate.value)

  return Array.from({ length: 7 }, (_, index) => {
    const date = new Date(monday)
    date.setDate(monday.getDate() + index)
    return date
  })
})

const weeklyColumns = computed(() =>
  weekDates.value.map((date) => {
    const window = getWorkingWindow(date)
    const segments = []

    if (window) {
      let cursor = window.start

      activeAppointments.value
        .filter((appointment) => isSameDay(appointment.start, date))
        .sort((a, b) => a.start - b.start)
        .forEach((appointment) => {
          const start = clamp(toMinutes(appointment.start), window.start, window.end)
          const end = clamp(toMinutes(appointment.end), window.start, window.end)

          if (start > cursor) {
            segments.push({ kind: 'empty', id: `empty-${appointment.id}-${cursor}`, duration: start - cursor })
          }

          segments.push({ kind: 'appointment', id: appointment.id, duration: end - start, appointment })
          cursor = end
        })

      if (cursor < window.end) {
        segments.push({ kind: 'empty', id: `empty-end-${cursor}`, duration: window.end - cursor })
      }

      if (!segments.length) {
        segments.push({ kind: 'empty', id: 'empty-full-day', duration: window.end - window.start })
      }
    }

    return { date, label: weekdayFormatter.format(date), segments }
  }),
)

const monthlyCalendar = computed(() => {
  const firstCell = getWeekMonday(new Date(selectedDate.value.getFullYear(), selectedDate.value.getMonth(), 1))

  return Array.from({ length: 42 }, (_, index) => {
    const date = new Date(firstCell)
    date.setDate(firstCell.getDate() + index)

    const window = getWorkingWindow(date)
    let bookedMinutes = 0

    if (window) {
      activeAppointments.value
        .filter((appointment) => isSameDay(appointment.start, date))
        .forEach((appointment) => {
          const start = clamp(toMinutes(appointment.start), window.start, window.end)
          const end = clamp(toMinutes(appointment.end), window.start, window.end)
          bookedMinutes += end - start
        })
    }

    return {
      key: date.toISOString(),
      date,
      label: `${weekdayFormatter.format(date)} ${date.getDate()}`,
      dayNumber: String(date.getDate()),
      occupancy: window ? Math.min(100, Math.round((bookedMinutes / (window.end - window.start)) * 100)) : 0,
      isWorkingDay: Boolean(window),
      inCurrentMonth: date.getMonth() === selectedDate.value.getMonth(),
    }
  })
})

const timeLabels = computed(() => {
  const labels = []
  const window = selectedDayWindow.value
  if (!window) return labels

  for (let minute = window.start; minute <= window.end; minute += 60) {
    labels.push(formatMinutes(minute))
  }

  return labels
})

const titleText = computed(() => {
  if (viewMode.value === 'cancelled') return 'Cancelled Appointments'
  if (viewMode.value === 'monthly') return monthTitleFormatter.format(selectedDate.value)
  if (viewMode.value === 'weekly') {
    const start = weekDates.value[0]
    const end = weekDates.value[6]
    return `${start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${end.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`
  }

  return dayTitleFormatter.format(selectedDate.value)
})

function getWorkingWindow(date) {
  const hours = workingHoursByWeekday.value[date.getDay()]
  if (!hours || hours.start === null || hours.end === null) return null
  return hours
}

function getWeekMonday(date) {
  const monday = new Date(date)
  const weekday = monday.getDay()
  monday.setDate(monday.getDate() + (weekday === 0 ? -6 : 1 - weekday))
  monday.setHours(0, 0, 0, 0)
  return monday
}

function isSameDay(left, right) {
  return left.getFullYear() === right.getFullYear()
    && left.getMonth() === right.getMonth()
    && left.getDate() === right.getDate()
}

function timeToMinutes(value) {
  if (!value) return null
  const [hours, minutes] = value.split(':').map(Number)
  return (hours * 60) + minutes
}

function toMinutes(date) {
  return date.getHours() * 60 + date.getMinutes()
}

function clamp(value, start, end) {
  return Math.min(end, Math.max(start, value))
}

function formatMinutes(minutes) {
  return `${String(Math.floor(minutes / 60)).padStart(2, '0')}:${String(minutes % 60).padStart(2, '0')}`
}

function getSegmentStyle(duration) {
  return { flexGrow: duration, flexBasis: '0', minHeight: '0' }
}

function getStatusStylesWeekly(status) {
  if (status === 'pending') return 'bg-gray-200 text-gray-800 border border-gray-400/60'
  if (status === 'confirmed') return 'border-black/30 border bg-white'
  if (status === 'completed') return 'bg-emerald-100 text-emerald-900 border border-emerald-600/60'
  if (status === 'no_show') return 'bg-rose-100 text-rose-900 border border-rose-500/50'
  return 'bg-slate-100 border border-slate-200'
}

function getStatusStylesDaily(status) {
  if (status === 'pending') return 'bg-gray-200 text-gray-800'
  if (status === 'confirmed') return 'border-black/10 border'
  if (status === 'completed') return 'bg-emerald-100 text-emerald-900'
  if (status === 'no_show') return 'bg-rose-100 text-rose-900'
  return 'bg-slate-100 text-slate-700'
}

function getMonthlyFillStyle(occupancy) {
  if (occupancy <= 33) return 'bg-emerald-400/70'
  if (occupancy <= 66) return 'bg-amber-300/70'
  return 'bg-rose-400/70'
}

function goToday() {
  selectedDate.value = new Date()
}

function goPrev() {
  if (viewMode.value === 'monthly') {
    selectedDate.value = new Date(selectedDate.value.getFullYear(), selectedDate.value.getMonth() - 1, 1)
    return
  }

  const date = new Date(selectedDate.value)
  date.setDate(date.getDate() - (viewMode.value === 'weekly' ? 7 : 1))
  selectedDate.value = date
}

function goNext() {
  if (viewMode.value === 'monthly') {
    selectedDate.value = new Date(selectedDate.value.getFullYear(), selectedDate.value.getMonth() + 1, 1)
    return
  }

  const date = new Date(selectedDate.value)
  date.setDate(date.getDate() + (viewMode.value === 'weekly' ? 7 : 1))
  selectedDate.value = date
}

async function openDailyForDate(date) {
  selectedDate.value = new Date(date)
  viewMode.value = 'daily'

  await nextTick()
  scheduleRoot.value.scrollIntoView({ block: 'start' })
}

function isAppointmentCancellable(status) {
  return status === 'pending' || status === 'confirmed'
}

function getStatusLabel(status) {
  if (status === 'pending') return 'Pending'
  if (status === 'confirmed') return 'Confirmed'
  if (status === 'completed') return 'Completed'
  if (status === 'no_show') return 'No-show'
  return 'Cancelled'
}

function formatAppointmentDate(date) {
  return date.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' })
}

function formatAppointmentTime(date) {
  return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false })
}

function openAppointmentDetails(appointment) {
  detailAppointment.value = appointment
}

function closeAppointmentDetails() {
  detailAppointment.value = null
}

function completeFromModal(appointmentId) {
  emit('complete-appointment', appointmentId)
  closeAppointmentDetails()
}

function cancelFromModal(appointmentId) {
  emit('cancel-appointment', appointmentId)
  closeAppointmentDetails()
}

function markNoShowFromModal(appointmentId) {
  emit('mark-no-show', appointmentId)
  closeAppointmentDetails()
}
</script>

<template>
  <section
    ref="scheduleRoot"
    class="flex flex-col rounded-2xl border border-black/5 md:rounded-[2rem]"
    :class="viewMode === 'monthly' ? 'min-h-0 flex-1 overflow-hidden' : 'h-auto overflow-visible'"
  >
    <AppointmentScheduleHeader
      class="rounded-t-2xl md:rounded-t-[2rem]"
      :view-mode="viewMode"
      :title-text="titleText"
      :order-by="cancelledOrderBy"
      @update:view-mode="viewMode = $event"
      @update:order-by="cancelledOrderBy = $event"
      @prev="goPrev"
      @today="goToday"
      @next="goNext"
    />

    <div
      class="rounded-b-2xl md:rounded-b-[2rem]"
      :class="isMonthlyView ? 'flex min-h-0 flex-1 flex-col gap-5 bg-white p-3 sm:p-5 md:p-6' : 'bg-white p-3 sm:p-5 md:p-6'"
    >
      <div v-if="viewMode === 'daily' && selectedDayWindow">
        <AppointmentDailyView
          :booked-count="dailyAppointments.length"
          :segments="dailySegments"
          :get-status-styles="getStatusStylesDaily"
          :format-minutes="formatMinutes"
          :format-appointment-time="formatAppointmentTime"
          @open-details="openAppointmentDetails"
        />
      </div>

      <div v-else-if="viewMode === 'daily'" class="rounded-[1.5rem] px-5 py-10 text-center">
        <p class="mt-3 text-base font-medium text-slate-700">
          {{ selectedDayWorkingHoursNotice }}
        </p>
      </div>

      <div v-else-if="viewMode === 'cancelled'">
        <AppointmentCancelledView
          :appointments="cancelledAppointments"
          :format-appointment-date="formatAppointmentDate"
        />
      </div>

      <div v-else-if="viewMode === 'weekly'">
        <AppointmentWeeklyView
          :time-labels="timeLabels"
          :weekly-columns="weeklyColumns"
          :get-segment-style="getSegmentStyle"
          :get-status-styles="getStatusStylesWeekly"
          @open-day="openDailyForDate"
        />
      </div>

      <div v-else class="flex min-h-0 flex-1 flex-col">
        <AppointmentMonthlyView
          :monthly-summary="monthlySummary"
          :monthly-calendar="monthlyCalendar"
          :get-monthly-fill-style="getMonthlyFillStyle"
          @open-day="openDailyForDate"
        />
      </div>

      <div>
        <AppointmentDetailModal
          :appointment="detailAppointment"
          :format-appointment-date="formatAppointmentDate"
          :format-appointment-time="formatAppointmentTime"
          :get-status-label="getStatusLabel"
          :is-appointment-cancellable="isAppointmentCancellable"
          @close="closeAppointmentDetails"
          @complete="completeFromModal"
          @cancel="cancelFromModal"
          @mark-no-show="markNoShowFromModal"
        />
      </div>
    </div>
  </section>
</template>
