<template>
  <div class="flex h-dvh overflow-hidden bg-slate-100">
    <Sidebar :isOpen="sidebarOpen" @close="sidebarOpen = false" />

    <main class="flex-1 w-full overflow-y-auto p-8">
      <Header title="Schedule" description="Manage your shop closures and special open days"
        action-label="+ add holiday" @menu-click="sidebarOpen = true" />

      <div class="mx-auto flex w-7xl flex-col gap-4 xl:flex-row">
        <div class="flex-1 rounded-2xl bg-white p-4 shadow-sm">
          <FullCalendar :options="calendarOptions" />
        </div>
        <aside class="flex w-2/5 flex-col rounded-2xl bg-white p-4 shadow-sm">
          <div class="mb-4">
            <h2 class="text-xl font-semibold text-black">Weekly schedule</h2>
            <p class="mt-1 text-xs text-gray-500">Default opening hours</p>
          </div>

          <div class="flex justify-around flex-1 flex-col gap-2">
            <div v-for="day in orderedSchedule" :key="day.day"
              class="flex h-14 flex justify-between items-center gap-2 rounded-lg border border-black/10 px-4 py-4">
              <div class="flex items-center gap-4 min-w-0">
                <ToggleButton v-model="day.isOpen" />

                <span class="text-black">
                  {{ day.day }}
                </span>

              </div>
              <div v-if="day.isOpen" class="flex items-center justify-end gap-1">
                <input v-model="day.openTime" type="time" class="p-1 rounded-lg border border-black/10 text-xs" />

                <span class="text-center text-gray-500">-</span>

                <input v-model="day.closeTime" type="time" class="p-1 rounded-lg border border-black/10 text-xs" />
              </div>

              <span v-else class="text-sm">
                Closed
              </span>

            </div>

            <div class="flex justify-end">
              <Button class="mt-2">
                Save
              </Button>
            </div>
          </div>
        </aside>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'

import Header from '@/components/admin/Header.vue'
import Sidebar from '@/components/admin/Sidebar.vue'
import Button from '@/components/admin/Button.vue'
import ToggleButton from '@/components/admin/ToggleButton.vue'
import { INITIAL_HOLIDAYS, INITIAL_WEEKLY_SCHEDULE } from '@/data/calenderData'
import { toISO } from '@/utils/date'

const sidebarOpen = ref(false)
const currentMonth = ref(new Date())

const holidays = ref(INITIAL_HOLIDAYS.map((item) => ({ ...item })))
const schedule = ref(INITIAL_WEEKLY_SCHEDULE.map((item) => ({ ...item })))
const orderedSchedule = computed(() => [...schedule.value.slice(1), schedule.value[0]])


const holidaysByDate = computed(() => {
  const result = {}

  for (const holiday of holidays.value) {
    result[holiday.dateISO] = holiday
  }

  return result
})

function getDates(start, end) {
  const dates = []

  while (start <= end) {
    dates.push(new Date(start))
    start.setDate(start.getDate() + 1)
  }
  return dates
}

function getWeeklyScheduleForDate(date) {
  return schedule.value[date.getDay()]
}

const calendarEvents = computed(() => {
  const monthDate = currentMonth.value
  const monthStart = new Date(monthDate.getFullYear(), monthDate.getMonth(), 1)
  const monthEnd = new Date(monthDate.getFullYear(), monthDate.getMonth() + 1, 0)

  const dates = getDates(monthStart, monthEnd)

  return dates.map((date) => {
    const iso = toISO(date)
    const holiday = holidaysByDate.value[iso] || null
    const weeklySchedule = getWeeklyScheduleForDate(date)

    if (holiday) {
      if (holiday.status === 'open') {
        return {
          id: iso,
          title: `${holiday.openTime} - ${holiday.closeTime}`,
          start: iso,
          allDay: true,
          backgroundColor: '#dcf5df',
          borderColor: '#dcf5df',
          textColor: '#13652b',
        }
      }

      return {
        id: iso,
        title: 'Closed',
        start: iso,
        allDay: true,
        backgroundColor: '#f8d8d8',
        borderColor: '#f8d8d8',
        textColor: '#8f1d1d',
      }
    }

    if (weeklySchedule.isOpen) {
      return {
        id: iso,
        title: `${weeklySchedule.openTime} - ${weeklySchedule.closeTime}`,
        start: iso,
        allDay: true,
        backgroundColor: 'transparent',
        borderColor: 'transparent',
        textColor: '#000000',
      }
    }

    return {
      id: iso,
      title: 'Closed',
      start: iso,
      allDay: true,
      backgroundColor: 'transparent',
      borderColor: 'transparent',
      textColor: '#000000',
    }
  })
})

function handleDateClick(info) { }

function handleDatesSet(info) {
  currentMonth.value = new Date(info.view.currentStart)
}

const calendarOptions = computed(() => ({
  plugins: [dayGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  firstDay: 1,
  fixedWeekCount: true,
  events: calendarEvents.value,
  dateClick: handleDateClick,
  datesSet: handleDatesSet,
  headerToolbar: {
    left: 'prev',
    center: 'title',
    right: 'next',
  },
}))

</script>
