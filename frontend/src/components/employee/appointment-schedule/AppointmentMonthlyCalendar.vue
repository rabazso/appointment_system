<script setup>
import { computed } from 'vue'
import CalendarView from '@/components/admin/calendar/CalendarView.vue'
import { parseISODate, toISO } from '@/utils/date'

const props = defineProps({
  monthlyCalendar: { type: Array, required: true },
  getMonthlyFillStyle: { type: Function, required: true }
})

const emit = defineEmits(['open-day'])

const calendarMonth = computed(() => {
  const targetDay = props.monthlyCalendar.find((day) => day.inCurrentMonth) ?? props.monthlyCalendar[0]

  return new Date(targetDay.date.getFullYear(), targetDay.date.getMonth(), 1)
})

const calendarDayMap = computed(() => {
  const contentMap = {}

  props.monthlyCalendar.forEach((day) => {
    const occupancy = Math.min(100, Math.max(0, Number(day.occupancy) || 0))
    const isActiveDay = day.inCurrentMonth && day.isWorkingDay
    const isoDate = toISO(day.date)

    contentMap[isoDate] = {
      cellClass: isActiveDay ? 'cursor-pointer' : 'opacity-75',
      cellBackgroundClass: isActiveDay ? props.getMonthlyFillStyle(occupancy) : '',
      cellBackgroundStyle: isActiveDay ? { height: `${occupancy}%` } : undefined,
      cellOverlayClass: '',
    }
  })

  return contentMap
})

const daysByIso = computed(() => {
  const byIso = {}
  props.monthlyCalendar.forEach((day) => {
    byIso[toISO(day.date)] = day
  })
  return byIso
})

function handleDayClick(dateISO) {
  const day = daysByIso.value[dateISO]
  if (!day.inCurrentMonth || !day.isWorkingDay) {
    return
  }

  const clickedDate = parseISODate(dateISO)
  emit('open-day', clickedDate)
}
</script>

<template>
  <div class="mx-auto flex h-full min-h-0 w-full flex-1 overflow-hidden">
    <CalendarView
      :month="calendarMonth"
      :cell-map="calendarDayMap"
      @day-click="handleDayClick"
    />
  </div>
</template>
