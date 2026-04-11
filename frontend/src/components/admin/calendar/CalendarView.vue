
<template>
    <div
      class="flex h-full min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-black/10 bg-white"
    >
      <div class="grid shrink-0 grid-cols-7 gap-px border-b bg-black/10">
        <div
          v-for="day in weekDays"
          :key="day"
          class="flex h-10 items-center justify-center bg-white text-sm font-semibold uppercase tracking-wide text-black"
        >
          {{ day }}
        </div>
      </div>

      <div
        class="grid h-full min-h-0 flex-1 grid-cols-7 gap-px grid-rows-6 bg-black/10"
      >
        <CalendarCell
          v-for="day in calendarDays"
          :key="day.dateISO"
          :date="day.dateISO"
          :content="cellMap[day.dateISO]?.content"
          :contentClass="cellMap[day.dateISO]?.contentClass"
          :dotContent="cellMap[day.dateISO]?.dotContent"
          :isInCurrentMonth="day.isInCurrentMonth"
        />
      </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import CalendarCell from './CalendarCell.vue'
import { getDay, toISO } from '@/utils/date'
import { weekDays } from '@/data/calenderData'

const props = defineProps({
  month: {
    type: Date,
    required: true
  },
  cellMap: {
    type: Object,
    default: () => ({})
  }
})

const calendarDays = computed(() => {
  const monthStart = new Date(props.month)
  monthStart.setDate(1)

  const weekday = getDay(monthStart)
  const daysToGoBackToMonday = weekday - 1

  const startDay = new Date(monthStart)
  startDay.setDate(startDay.getDate() - daysToGoBackToMonday)

  const totalCells = 42
  const days = []
  for (let i = 0; i < totalCells; i += 1) {
    const day = new Date(startDay)
    day.setDate(startDay.getDate() + i)
    days.push({
      dateISO: toISO(day),
      isInCurrentMonth: day.getMonth() === monthStart.getMonth()
    })
  }
  return days
})
</script>
