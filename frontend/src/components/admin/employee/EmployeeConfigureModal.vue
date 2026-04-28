<template>
  <ModalShell
    v-if="activeSection === 'menu'"
    @close="$emit('close')"
  >
    <div class="mb-5 flex items-start gap-4">
      <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-slate-100">
        <img
          v-if="employee.profile_image?.preview_url"
          :src="employee.profile_image.preview_url"
          :alt="employee.name"
          class="h-full w-full object-cover"
        />
        <div
          v-else
          class="flex h-full w-full items-center justify-center bg-slate-100"
        >
          <User class="h-7 w-7 text-slate-500" />
        </div>
      </div>

      <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="min-w-0">
            <h2 class="text-2xl font-semibold tracking-tight text-slate-950">{{ employee.name }}</h2>
            <p class="mt-1 text-sm text-slate-500">Choose what you want to configure for this employee.</p>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <div
              class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold"
              :class="employee.is_available ? 'bg-emerald-100 text-emerald-900' : 'bg-rose-100 text-rose-900'"
            >
              {{ employee.is_available ? 'Available' : 'Unavailable' }}
            </div>

            <div class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold">
              ⭐ {{ formatRating(employee.rating) }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="space-y-3">
      <button
        v-for="item in menuItems"
        :key="item.section"
        type="button"
        class="flex w-full items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-4 text-left transition hover:bg-slate-50"
        :disabled="isOpeningSection !== null"
        @click="openSection(item.section)"
      >
        <div class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600">
          <component
            :is="item.icon"
            v-if="item.icon"
            class="h-6 w-6"
          />
        </div>
        <div>
          <p class="text-lg font-semibold text-slate-900">{{ item.title }}</p>
          <p class="mt-1 text-sm text-slate-500">{{ item.description }}</p>
        </div>
      </button>
    </div>
  </ModalShell>

  <EmployeeAvailabilityModal
      v-else-if="activeSection === 'availability'"
      :employee="employee"
      :versions="availabilityVersions"
      :refresh-section="() => fetchSection('availability')"
      @back="showMenu"
      @close="$emit('close')"
    />

    <EmployeeScheduleModal
      v-else-if="activeSection === 'schedule'"
      :employee="employee"
      :versions="scheduleVersions"
      :refresh-section="() => fetchSection('schedule')"
      @back="showMenu"
      @close="$emit('close')"
    />

    <EmployeeServicesModal
      v-else-if="activeSection === 'services'"
      :employee="employee"
      :versions="serviceVersions"
      :refresh-section="() => fetchSection('services')"
      @back="showMenu"
      @close="$emit('close')"
    />

    <EmployeeBookingRulesModal
      v-else-if="activeSection === 'bookingRules'"
      :employee="employee"
      :versions="bookingRuleVersions"
      :refresh-section="() => fetchSection('bookingRules')"
      @back="showMenu"
      @close="$emit('close')"
    />
</template>

<script setup>
import { ref } from 'vue'
import { CalendarCog, CircleCheck, Clock, Scissors, User } from 'lucide-vue-next'
import ModalShell from '@/components/admin/ModalShell.vue'
import EmployeeAvailabilityModal from './EmployeeAvailabilityModal.vue'
import EmployeeBookingRulesModal from './EmployeeBookingRulesModal.vue'
import EmployeeScheduleModal from './EmployeeScheduleModal.vue'
import EmployeeServicesModal from './EmployeeServicesModal.vue'
import { useEmployeeAvailabilityConfigurations } from '@/composables/useEmployeeAvailabilityConfigurations'
import { useEmployeeBookingRuleConfigurations } from '@/composables/useEmployeeBookingRuleConfigurations'
import { useEmployeeScheduleConfigurations } from '@/composables/useEmployeeScheduleConfigurations'
import { useEmployeeServiceConfigurations } from '@/composables/useEmployeeServiceConfigurations'
import { useToastStore } from '@/stores/ToastStore.js'

defineEmits(['close'])

const props = defineProps({
  employee: {
    type: Object,
    required: true,
  },
})

const activeSection = ref('menu')
const isOpeningSection = ref(null)
const toast = useToastStore()
const { availability: availabilityVersions, fetchAvailability } = useEmployeeAvailabilityConfigurations(props.employee.id)
const { schedules: scheduleVersions, fetchSchedules } = useEmployeeScheduleConfigurations(props.employee.id)
const { services: serviceVersions, fetchServices } = useEmployeeServiceConfigurations(props.employee.id)
const { bookingRules: bookingRuleVersions, fetchBookingRules } = useEmployeeBookingRuleConfigurations(props.employee.id)

const menuItems = [
  {
    section: 'availability',
    title: 'Availability',
    description: 'Manage when this employee can be booked.',
    icon: CircleCheck,
  },
  {
    section: 'schedule',
    title: 'Schedule',
    description: 'Review schedule changes and working hours.',
    icon: Clock,
  },
  {
    section: 'services',
    title: 'Services',
    description: 'Manage assigned services for this employee.',
    icon: Scissors,
  },
  {
    section: 'bookingRules',
    title: 'Booking rules',
    description: 'Manage booking limits and appointment rules.',
    icon: CalendarCog,
  },
]

function showMenu() {
  activeSection.value = 'menu'
}

async function openSection(section) {
  if (isOpeningSection.value) return

  isOpeningSection.value = section
  try {
    await fetchSection(section)
    activeSection.value = section
  } catch (error) {
    toast.showError('Failed to load data.')
  } finally {
    isOpeningSection.value = null
  }
}

async function fetchSection(section) {
  if (section === 'availability') {
    await fetchAvailability()
    return
  }

  if (section === 'schedule') {
    await fetchSchedules()
    return
  }

  if (section === 'services') {
    await fetchServices()
    return
  }

  await fetchBookingRules()
}

function formatRating(rating) {
  const value = Number(rating)
  return Number.isFinite(value) ? value.toFixed(1) : '0.0'
}
</script>
