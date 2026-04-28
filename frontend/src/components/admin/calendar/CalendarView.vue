
<template>
    <div
      class="flex h-full flex-1 flex-col overflow-hidden rounded-2xl border border-black/10"
      :class="backgroundClass"
    >
      <div class="grid shrink-0 grid-cols-7 gap-px border-b bg-black/10">
        <div
          v-for="day in WEEKDAYS"
          :key="day"
          class="flex h-10 items-center justify-center text-sm font-semibold uppercase tracking-wide text-black"
          :class="backgroundClass"
        >
          {{ day.label.slice(0, 3) }}
        </div>
      </div>

      <div
        class="grid min-h-0 flex-1 grid-cols-7 grid-rows-[repeat(6,minmax(0,1fr))] gap-px bg-black/10"
      >
        <CalendarCell
          v-for="day in calendarDays"
          :key="day.dateISO"
          :date="day.dateISO"
          :content="cellMap[day.dateISO]?.content"
          :contentClass="cellMap[day.dateISO]?.contentClass"
          :dotContent="cellMap[day.dateISO]?.dotContent"
          :dotContentClass="cellMap[day.dateISO]?.dotContentClass"
          :cellClass="cellMap[day.dateISO]?.cellClass"
          :cellBackgroundClass="cellMap[day.dateISO]?.cellBackgroundClass"
          :cellBackgroundStyle="cellMap[day.dateISO]?.cellBackgroundStyle"
          :cellOverlayClass="cellMap[day.dateISO]?.cellOverlayClass"
          :background-class="backgroundClass"
          :day-badge-background-class="dayBadgeBackgroundClass"
          :isPast="day.isPast"
          :isToday="day.isToday"
          :isInCurrentMonth="day.isInCurrentMonth"
          @day-click="emit('day-click', day.dateISO)"
        />
      </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import CalendarCell from './CalendarCell.vue'
import { getDay, toISO } from '@/utils/date'
import { WEEKDAYS } from '@/data/calenderData'

const props = defineProps({
  month: {
    type: Date,
    required: true
  },
  cellMap: {
    type: Object,
    default: () => ({})
  },
  backgroundClass: {
    type: String,
    default: 'bg-white'
  },
  dayBadgeBackgroundClass: {
    type: String,
    default: 'bg-transparent'
  }
})

const emit = defineEmits(['day-click'])

const calendarDays = computed(() => {
  const monthStart = new Date(props.month)
  monthStart.setDate(1)

  const weekday = getDay(monthStart)
  const daysToGoBackToMonday = weekday - 1

  const startDay = new Date(monthStart)
  startDay.setDate(startDay.getDate() - daysToGoBackToMonday)

  const totalCells = 42
  const days = []
  const todayISO = toISO(new Date())
  for (let i = 0; i < totalCells; i += 1) {
    const day = new Date(startDay)
    day.setDate(startDay.getDate() + i)
    const dateISO = toISO(day)
    days.push({
      dateISO,
      isInCurrentMonth: day.getMonth() === monthStart.getMonth(),
      isPast: dateISO < todayISO,
      isToday: dateISO === todayISO,
    })
  }
  return days
})
</script>
