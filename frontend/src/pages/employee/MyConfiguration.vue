<template>
  <PageLayout
    current-section="configuration"
    title="My Configuration"
    description="View your active and upcoming booking configurations."
  >
    <div class="mx-auto flex w-full min-h-0 flex-1 items-center justify-center">
      <div class="grid w-full max-w-5xl gap-4 sm:grid-cols-2">
        <button
          type="button"
          class="min-h-[180px] rounded-2xl border border-black/10 bg-white p-6 text-left shadow-sm transition hover:border-black hover:bg-slate-50"
          @click="openVersionViewer('services')"
        >
          <div class="mb-2 flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-black/10 bg-slate-50 text-slate-700">
              <Scissors class="h-5 w-5" />
            </span>
            <h2 class="text-xl font-semibold text-black">Services</h2>
          </div>
          <p class="mt-1 text-sm text-slate-500">Assigned services, prices, and durations.</p>
        </button>

        <button
          type="button"
          class="min-h-[180px] rounded-2xl border border-black/10 bg-white p-6 text-left shadow-sm transition hover:border-black hover:bg-slate-50"
          @click="openVersionViewer('schedule')"
        >
          <div class="mb-2 flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-black/10 bg-slate-50 text-slate-700">
              <CalendarRange class="h-5 w-5" />
            </span>
            <h2 class="text-xl font-semibold text-black">Schedule</h2>
          </div>
          <p class="mt-1 text-sm text-slate-500">Weekly working hours and breaks.</p>
        </button>

        <button
          type="button"
          class="min-h-[180px] rounded-2xl border border-black/10 bg-white p-6 text-left shadow-sm transition hover:border-black hover:bg-slate-50"
          @click="openVersionViewer('availability')"
        >
          <div class="mb-2 flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-black/10 bg-slate-50 text-slate-700">
              <Clock3 class="h-5 w-5" />
            </span>
            <h2 class="text-xl font-semibold text-black">Availability</h2>
          </div>
          <p class="mt-1 text-sm text-slate-500">Whether you are bookable in each version.</p>
        </button>

        <button
          type="button"
          class="min-h-[180px] rounded-2xl border border-black/10 bg-white p-6 text-left shadow-sm transition hover:border-black hover:bg-slate-50"
          @click="openVersionViewer('bookingRules')"
        >
          <div class="mb-2 flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-black/10 bg-slate-50 text-slate-700">
              <Settings2 class="h-5 w-5" />
            </span>
            <h2 class="text-xl font-semibold text-black">Booking Rules</h2>
          </div>
          <p class="mt-1 text-sm text-slate-500">Booking window and slot interval versions.</p>
        </button>
      </div>
    </div>

    <ModalShell
      v-if="activeVersionViewer"
      show-back
      @back="handleVersionViewerBack"
      @close="activeVersionViewer = null"
    >
      <template v-if="versionViewerMode === 'list'">
        <ModalHeader
          :title="versionViewerTitle"
          :description="versionViewerDescription"
        />

        <VersionsView :versions="versionViewerItems" :show-create="false">
          <template #card="{ version, index }">
            <VersionCard
              :valid-from="version.valid_from"
              :valid-to="version.valid_to"
              :index="index"
              :show-actions="false"
              @view="openVersionDetails(version)"
            >
              <template v-if="activeVersionViewer === 'services'">
                <p class="text-base font-semibold text-slate-900">{{ getServicesSummary(version) }}</p>
              </template>
              <template v-else-if="activeVersionViewer === 'schedule'">
                <p class="text-base font-semibold text-slate-900">{{ getScheduleSummary(version) }}</p>
              </template>
              <template v-else-if="activeVersionViewer === 'availability'">
                <span
                  class="inline-flex rounded-full px-4 py-1.5 text-sm font-semibold leading-none"
                  :class="version.is_available ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                >
                  {{ version.is_available ? 'Available' : 'Unavailable' }}
                </span>
              </template>
              <template v-else>
                <div class="gap-1 flex flex-row flex-wrap">
                  <div>
                    <p class="mx-auto text-base font-semibold text-slate-800">
                      Booking slot interval: <span>{{ version.booking_interval_minutes }} min</span>
                    </p>
                  </div>
                  <div>
                    <p class="mx-auto text-base font-semibold text-slate-800">
                      Booking window: <span>{{ version.booking_window_days }} days</span>
                    </p>
                  </div>
                </div>
              </template>
            </VersionCard>
          </template>
        </VersionsView>
      </template>

      <template v-else-if="versionViewerMode === 'details' && activeVersionViewer === 'services' && selectedVersionFromViewer">
        <ModalHeader
          title="Service details"
          description="Review the selected assigned services."
        />
        <VersionDetailActions
          :valid-from="selectedVersionFromViewer.valid_from"
          :valid-to="selectedVersionFromViewer.valid_to"
          :show-actions="false"
        />
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
          <template v-if="selectedVersionFromViewer.services?.length">
            <div
              v-for="(service, index) in selectedVersionFromViewer.services"
              :key="service.name"
              class="grid grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-4 px-5 py-4"
              :class="index !== selectedVersionFromViewer.services.length - 1 ? 'border-b border-slate-100' : ''"
            >
              <span class="text-sm">{{ service.name }}</span>
              <span class="text-sm font-semibold">{{ service.price }} Ft</span>
              <span class="text-sm font-semibold">{{ service.duration }} min</span>
            </div>
          </template>
          <div v-else class="px-4 py-6 text-center text-sm text-slate-500">
            No services assigned.
          </div>
        </div>
      </template>

      <template v-else-if="versionViewerMode === 'details' && activeVersionViewer === 'schedule' && selectedVersionFromViewer">
        <ModalHeader
          title="Schedule details"
          description="Review the selected weekly schedule."
        />
        <VersionDetailActions
          :valid-from="selectedVersionFromViewer.valid_from"
          :valid-to="selectedVersionFromViewer.valid_to"
          :show-actions="false"
        />
        <div class="space-y-4">
          <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div
              v-for="(day, index) in weekDayOptions"
              :key="day.key"
              class="px-5 py-4"
              :class="index !== weekDayOptions.length - 1 ? 'border-b border-slate-100' : ''"
            >
              <div class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-4">
                <span class="text-sm font-medium text-slate-700">{{ day.fullLabel }}</span>
                <span class="text-sm font-semibold">
                  {{
                    selectedVersionFromViewer?.weeklyHours?.[day.weekday]?.isOpen
                      ? `${selectedVersionFromViewer.weeklyHours[day.weekday].start} - ${selectedVersionFromViewer.weeklyHours[day.weekday].end}`
                      : 'Off'
                  }}
                </span>
              </div>
              <div
                v-if="selectedVersionFromViewer?.weeklyHours?.[day.weekday]?.isOpen && getBreaksForWeekday(selectedVersionFromViewer, day.weekday).length"
                class="mt-2 flex flex-wrap items-center gap-2 rounded-lg px-3 py-1.5"
              >
                <span class="text-sm">Breaks:</span>
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="(breakItem, breakIndex) in getBreaksForWeekday(selectedVersionFromViewer, day.weekday)"
                    :key="`${breakItem.weekday}-${breakIndex}`"
                    class="inline-flex items-center rounded-md border border-black/10 bg-white px-2 py-0.5 text-xs font-semibold"
                  >
                    {{ breakItem.start }} - {{ breakItem.end }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </ModalShell>

  </PageLayout>
</template>

<script setup>
import { CalendarRange, Clock3, Scissors, Settings2 } from 'lucide-vue-next'
import { computed, onMounted, ref } from 'vue'
import PageLayout from '@/components/employee/PageLayout.vue'
import VersionCard from '@/components/admin/VersionCard.vue'
import VersionsView from '@/components/admin/VersionsView.vue'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'
import VersionDetailActions from '@/components/admin/employee/VersionDetailActions.vue'
import { WEEKDAYS } from '@/data/calenderData'
import {
  getEmployeeOwnConfigurationAvailability,
  getEmployeeOwnConfigurationBookingRules,
  getEmployeeOwnConfigurationSchedules,
  getEmployeeOwnConfigurationServices,
} from '@/api/index'
import { useToastStore } from '@/stores/ToastStore.js'

const serviceVersions = ref([])
const scheduleVersions = ref([])
const availabilityVersions = ref([])
const bookingRuleVersions = ref([])

const activeVersionViewer = ref(null)
const versionViewerMode = ref('list')
const selectedVersionFromViewer = ref(null)

const toast = useToastStore()

onMounted(async () => {
  try {
    const [servicesResponse, schedulesResponse, availabilityResponse, bookingRulesResponse] = await Promise.all([
      getEmployeeOwnConfigurationServices(),
      getEmployeeOwnConfigurationSchedules(),
      getEmployeeOwnConfigurationAvailability(),
      getEmployeeOwnConfigurationBookingRules(),
    ])

    serviceVersions.value = sortLikeAdmin(unwrapCollection(servicesResponse.data))
    scheduleVersions.value = sortLikeAdmin(unwrapCollection(schedulesResponse.data))
    availabilityVersions.value = sortLikeAdmin(unwrapCollection(availabilityResponse.data))
    bookingRuleVersions.value = sortLikeAdmin(unwrapCollection(bookingRulesResponse.data))
  } catch {
    toast.showError('Failed to load data.')
  }
})

function unwrapCollection(payload) {
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload)) return payload
  return []
}

function sortLikeAdmin(versions) {
  const today = new Date().toISOString().slice(0, 10)

  return [...versions].sort((a, b) => {
    const aFrom = String(a?.valid_from || '')
    const bFrom = String(b?.valid_from || '')

    const aCurrentRank = aFrom <= today ? 0 : 1
    const bCurrentRank = bFrom <= today ? 0 : 1
    if (aCurrentRank !== bCurrentRank) return aCurrentRank - bCurrentRank

    return aFrom.localeCompare(bFrom)
  })
}

function openVersionViewer(type) {
  activeVersionViewer.value = type
  versionViewerMode.value = 'list'
  selectedVersionFromViewer.value = null
}

function openVersionDetails(version) {
  if (activeVersionViewer.value === 'services') {
    selectedVersionFromViewer.value = version
    versionViewerMode.value = 'details'
  } else if (activeVersionViewer.value === 'schedule') {
    selectedVersionFromViewer.value = version
    versionViewerMode.value = 'details'
  } else if (activeVersionViewer.value === 'availability') {
    return
  } else if (activeVersionViewer.value === 'bookingRules') {
    return
  }
}

function handleVersionViewerBack() {
  if (versionViewerMode.value === 'details') {
    versionViewerMode.value = 'list'
    selectedVersionFromViewer.value = null
    return
  }

  activeVersionViewer.value = null
}

const versionViewerTitle = computed(() => {
  if (activeVersionViewer.value === 'services') return 'Services'
  if (activeVersionViewer.value === 'schedule') return 'Schedule'
  if (activeVersionViewer.value === 'availability') return 'Availability'
  return 'Booking rules'
})

const versionViewerDescription = computed(() => {
  if (activeVersionViewer.value === 'services') return 'Choose a service version to review.'
  if (activeVersionViewer.value === 'schedule') return 'Choose a schedule version to review.'
  if (activeVersionViewer.value === 'availability') return 'Choose an availability version to review.'
  return 'Choose a booking rule version to review.'
})

const versionViewerItems = computed(() => {
  if (activeVersionViewer.value === 'services') return serviceVersions.value
  if (activeVersionViewer.value === 'schedule') return scheduleVersions.value
  if (activeVersionViewer.value === 'availability') return availabilityVersions.value
  if (activeVersionViewer.value === 'bookingRules') return bookingRuleVersions.value
  return []
})

const weekDayOptions = WEEKDAYS.map((day) => ({
  key: day.id,
  fullLabel: day.label,
  weekday: day.id,
}))

function getBreaksForWeekday(scheduleVersion, weekday) {
  return scheduleVersion?.breaks?.filter((breakEntry) => breakEntry.weekday === weekday) ?? []
}

function getServicesSummary(services) {
  const count = services.services?.length ?? 0
  if (!count) return 'No services assigned'
  if (count === 1) return '1 service assigned'
  return `${count} services assigned`
}

function getScheduleSummary(schedule) {
  const count = schedule.weeklyHours?.filter((day) => day.isOpen).length ?? 0
  if (!count) return 'No working days'
  if (count === 1) return '1 working day'
  return `${count} working days`
}
</script>
