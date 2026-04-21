
<template>
  <div class="flex h-dvh overflow-hidden bg-slate-100">
    <Sidebar :isOpen="sidebarOpen" @close="sidebarOpen = false" />

    <main class="flex min-h-0 flex-1 flex-col overflow-hidden p-4">
      <Header
        title="Schedule"
        description="Manage your shop closures and special open days"
        action-label="+ add special day"
        @menu-click="sidebarOpen = true"
        @action-click="openAddSpecialDayModal"
      />

      <div class="mx-auto mb-4 flex rounded-2xl bg-white p-1 shadow-sm 2xl:hidden">
        <button
          type="button"
          class="rounded-md px-4 py-2 font-semibold transition"
          :class="viewMode === 'calendar' ? 'bg-secondary' : 'bg-white'"
          @click="viewMode = 'calendar'"
        >
          Calendar
        </button>
        <button
          type="button"
          class="rounded-md px-4 py-2 font-semibold transition"
          :class="viewMode === 'weekly' ? 'bg-secondary' : 'bg-white'"
          @click="viewMode = 'weekly'"
        >
          Weekly schedule
        </button>
      </div>

      <div class="flex min-h-0 w-full flex-1 rounded-2xl flex-col gap-4 2xl:flex-row 2xl:overflow-hidden">
        <div
          :class="[viewMode === 'calendar' ? 'flex' : 'hidden', '2xl:flex']"
          class="min-h-0 w-full flex-1 flex-col rounded-2xl bg-white p-4"
        >
          <div class="mb-4 flex flex-wrap items-center gap-2">
            <button
              type="button"
              class="inline-flex h-12 w-12 items-center outline-none justify-center rounded-2xl border border-black/10 bg-white text-gray-500 shadow-sm transition hover:border-black"
              @click="goPrevMonth"
            >
              <ChevronLeft class="h-4 w-4" />
            </button>

            <div class="inline-flex min-h-11 min-w-[140px] items-center justify-center rounded-2xl border border-black/10 bg-white px-4 font-semibold shadow-sm">
              {{ monthLabel }}
            </div>

            <button
              type="button"
              class="inline-flex h-11 w-11 outline-none items-center justify-center rounded-2xl border border-black/10 text-gray-500 bg-white shadow-sm transition hover:border-black"
              @click="goNextMonth"
            >
              <ChevronRight class="h-4 w-4" />
            </button>

          </div>

          <div class="flex min-h-0 flex-1">
            <CalendarView
              :month="displayMonth"
              :cellMap="calendarDayMap"
              @day-click="openSpecialDayModalForDate"
            />
          </div>
        </div>

        <aside
          :class="[viewMode === 'weekly' ? 'flex' : 'hidden', '2xl:flex']"
          class="mx-auto w-full h-full flex-col overflow-y-auto rounded-2xl bg-white p-4 pb-4 2xl:mx-0 2xl:w-7/20"
        >
          <div class="mb-4">
            <h2 class="text-xl font-semibold">Weekly schedule</h2>
            <p class="mt-1 text-xs text-gray-500">Default opening hours</p>
          </div>

          <div class="flex flex-1 flex-col gap-3">
              <div
                v-for="day in openingHours"
                :key="day.weekday"
                class="flex flex-1 flex-wrap items-center gap-x-3 gap-y-2 rounded-2xl border border-black/10 px-4 py-3"
              >
                <div class="flex shrink-0 items-center gap-3">
                  <ToggleButton v-model="day.isOpen" />
                  <span class="text-black">{{ WEEKDAYS[day.weekday].label }}</span>
                </div>

                <div
                  v-if="day.isOpen"
                  class="ml-auto flex shrink-0 items-center justify-end gap-2"
                >
                  <input
                    v-model="day.openTime"
                    type="time"
                    class="rounded-lg border border-black/10 p-1 text-sm [font-variant-numeric:tabular-nums]"
                  />
                  <span class="text-center text-gray-500">-</span>
                  <input
                    v-model="day.closeTime"
                    type="time"
                    class="rounded-lg border border-black/10 p-1 text-sm [font-variant-numeric:tabular-nums]"
                  />
                </div>

                <span v-else class="ml-auto text-sm">Closed</span>
              </div>
              <div v-if="hasOpeningHoursChanges" class="flex justify-end">
                <Button @click="saveOpeningHours">
                  Save
                </Button>
              </div>
            </div>
        </aside>
      </div>
    </main>
  </div>
  <SpecialDayModal
    v-if="showSpecialDayModal"
    :day="selectedDay"
    :special-days="specialDays"
    :mode="specialDayModalMode"
    @close="closeSpecialDayModal"
    @save="saveSpecialDay"
  />
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import Header from '@/components/admin/Header.vue'
import Sidebar from '@/components/admin/Sidebar.vue'
import Button from '@/components/admin/Button.vue'
import ToggleButton from '@/components/admin/ToggleButton.vue'
import CalendarView from '@/components/admin/calendar/CalendarView.vue'
import SpecialDayModal from '@/components/admin/SpecialDayModal.vue'
import { INITIAL_WEEKLY_SCHEDULE } from '@/data/calenderData'
import { shiftMonth, toISO, formatYearMonth } from '@/utils/date'
import { useShopSchedule } from '@/composables/useShopSchedule'

const sidebarOpen = ref(false)
const viewMode = ref('calendar')

const displayMonth = ref(new Date())
const monthLabel = computed(() =>
  displayMonth.value.toLocaleDateString('en-US', {
    month: 'long',
    year: 'numeric',
  }),
)

const WEEKDAYS = [
  { label: 'Sunday' },
  { label: 'Monday' },
  { label: 'Tuesday' },
  { label: 'Wednesday' },
  { label: 'Thursday' },
  { label: 'Friday' },
  { label: 'Saturday' },
]

const openingHours = ref(INITIAL_WEEKLY_SCHEDULE.map((day, weekday) => ({ ...day, weekday })))
const savedOpeningHoursSnapshot = ref(normalizeOpeningHours(openingHours.value))
const specialDays = ref([])
const showSpecialDayModal = ref(false)
const selectedDay = ref(null)
const specialDayModalMode = ref('create')
const {
  fetchSpecialDays,
  saveSpecialDays,
  fetchOpeningHours,
  saveOpeningHours: persistOpeningHours,
} = useShopSchedule()

const hasOpeningHoursChanges = computed(() => {
  return normalizeOpeningHours(openingHours.value) !== savedOpeningHoursSnapshot.value
})

const calendarDayMap = computed(() => {
  const contentMap = {}
  const monthStart = new Date(displayMonth.value.getFullYear(), displayMonth.value.getMonth(), 1)
  const monthEnd = new Date(displayMonth.value.getFullYear(), displayMonth.value.getMonth() + 1, 0)

  let current = new Date(monthStart)
  while (current <= monthEnd) {
    const iso = toISO(current)
    const specialDay = specialDays.value.find((day) => day.dateISO === iso) ?? null
    const hasSpecialDay = Boolean(specialDay)
    const isOpen = Boolean(specialDay?.openTime && specialDay?.closeTime)
    const timeRange = `${specialDay?.openTime?.slice(0, 5)} - ${specialDay?.closeTime?.slice(0, 5)}`

    contentMap[iso] = {
      content: hasSpecialDay
        ? isOpen
          ? specialDay.name
            ? `${specialDay.name}\n${timeRange}`
            : timeRange
          : specialDay.name || 'closed'
        : '',
      contentClass: hasSpecialDay
        ? isOpen
          ? 'rounded-lg text-center text-sm p-1 bg-emerald-100 text-emerald-900'
          : 'rounded-lg text-center text-sm p-1 bg-rose-100 text-rose-900'
        : '',
        dotContent: hasSpecialDay,
      dotContentClass: hasSpecialDay
        ? isOpen
          ? 'bg-emerald-300'
          : 'bg-rose-300'
        : ''
    }

    current.setDate(current.getDate() + 1)
  }

  return contentMap
})

function goPrevMonth() {
  displayMonth.value = shiftMonth(displayMonth.value, -1)
}

function goNextMonth() {
  displayMonth.value = shiftMonth(displayMonth.value, 1)
}

function openAddSpecialDayModal() {
  specialDayModalMode.value = 'create'
  selectedDay.value = null
  showSpecialDayModal.value = true
}

function openSpecialDayModalForDate(dateISO) {
  const specialDay = specialDays.value.find((day) => day.dateISO === dateISO)

  specialDayModalMode.value = specialDay ? 'edit' : 'create'
  selectedDay.value = specialDay ?? { dateISO }
  showSpecialDayModal.value = true
}

function closeSpecialDayModal() {
  showSpecialDayModal.value = false
  selectedDay.value = null
}

async function saveSpecialDay(payload) {
  await saveSpecialDays(payload, specialDays.value)
  await loadSpecialDays()
  closeSpecialDayModal()
}

async function loadSpecialDays() {
  specialDays.value = await fetchSpecialDays(formatYearMonth(displayMonth.value))
}

async function loadOpeningHours() {
  openingHours.value = await fetchOpeningHours()
  savedOpeningHoursSnapshot.value = normalizeOpeningHours(openingHours.value)
}

async function saveOpeningHours() {
  await persistOpeningHours(openingHours.value)
  await loadOpeningHours()
}

function normalizeOpeningHours(days) {
  return JSON.stringify(days.map((day) => ({
    weekday: day.weekday,
    isOpen: day.isOpen,
    openTime: day.openTime,
    closeTime: day.closeTime,
  })))
}

watch(monthLabel, loadSpecialDays)

onMounted(() => {
  loadSpecialDays()
  loadOpeningHours()
})
</script>
