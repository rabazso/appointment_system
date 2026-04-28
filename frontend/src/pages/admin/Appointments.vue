<template>
  <PageLayout
    role="admin"
    title="Appointments"
    description="Browse and filter bookings across your shop."
  >

      <div class="mb-4 flex justify-end xl:hidden">
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-black/20"
          @click="mobileFiltersOpen = true"
        >
          <Calendar class="h-4 w-4" />
          Filters
        </button>
      </div>

      <div class="flex w-full flex-col gap-6 xl:grid xl:grid-cols-[minmax(0,1fr)_20rem] xl:items-start">
        <section class="order-1 min-w-0 rounded-2xl bg-white p-5 shadow-lg">
          <div class="flex flex-col gap-4">
            <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
              <article
                v-for="stat in stats"
                :key="stat.label"
                class="rounded-2xl border border-black/10 bg-slate-50 px-4 py-4"
              >
                <p class="text-sm font-medium text-slate-500">{{ stat.label }}</p>
                <p class="mt-2 text-3xl font-semibold text-slate-950">{{ stat.value }}</p>
              </article>
            </div>

            <div class="space-y-3">
              <div
                v-if="loading"
                class="rounded-2xl border border-black/10 bg-white px-4 py-8 text-center text-sm text-slate-500"
              >
                Loading appointments...
              </div>

              <div
                v-else-if="appointments.length === 0"
                class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500"
              >
                No appointments match these filters.
              </div>

              <AppointmentCard
                v-for="appointment in appointments"
                :key="appointment.id"
                :appointment="appointment"
                @open="openAppointmentModal(appointment)"
              />
            </div>
          </div>
        </section>

        <aside class="order-2 hidden w-full shrink-0 xl:sticky xl:top-0 xl:block">
          <AppointmentsFilters
            :model="filters"
            :services="services"
            :employees="employees"
            :status-options="statusOptions"
            @reset="resetFilters"
            @close="mobileFiltersOpen = false"
          />
        </aside>
      </div>

      <div v-if="mobileFiltersOpen" class="fixed inset-0 z-40 xl:hidden" @click.self="mobileFiltersOpen = false">
        <div class="absolute inset-0 bg-black/50" />
        <aside class="absolute right-0 top-0 overflow-y-auto p-4">
          <AppointmentsFilters
            :model="filters"
            :services="services"
            :employees="employees"
            :status-options="statusOptions"
            @reset="resetFilters"
            @close="mobileFiltersOpen = false"
          />
        </aside>
      </div>

    <AppointmentDetailsModal
      v-if="selectedAppointment"
      :appointment="selectedAppointment"
      @close="closeAppointmentModal"
      @cancel="runAction('cancel', $event)"
      @complete="runAction('complete')"
      @no-show="runAction('no-show')"
    />
  </PageLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Calendar } from 'lucide-vue-next'
import PageLayout from '@/components/PageLayout.vue'
import AppointmentCard from '@/components/admin/appointments/AppointmentCard.vue'
import AppointmentDetailsModal from '@/components/admin/appointments/AppointmentDetailsModal.vue'
import AppointmentsFilters from '@/components/admin/appointments/AppointmentsFilters.vue'
import {
  cancelAdminAppointment,
  completeAdminAppointment,
  noShowAdminAppointment,
} from '@/api/index'
import { useAdminAppointments } from '@/composables/useAdminAppointments'
import { useToastStore } from '@/stores/ToastStore.js'

const mobileFiltersOpen = ref(false)
const selectedAppointmentId = ref(null)
const toast = useToastStore()

const adminAppointments = useAdminAppointments()
const {
  appointments,
  employees,
  filters,
  loadAppointments,
  resetFilters,
  stats,
  statusOptions,
  services,
  loading,
} = adminAppointments

const selectedAppointment = computed(
  () => appointments.value.find((appointment) => appointment.id === selectedAppointmentId.value) || null,
)

function openAppointmentModal(appointment) {
  selectedAppointmentId.value = appointment.id
}

function closeAppointmentModal() {
  selectedAppointmentId.value = null
}

async function runAction(action, reason = '') {
  if (!selectedAppointment.value) return

  const appointmentId = selectedAppointment.value.id

  try {
    if (action === 'cancel') {
      await cancelAdminAppointment(appointmentId, {
        cancellation_reason: reason || 'Cancelled from admin dashboard',
      })
    }

    if (action === 'complete') {
      await completeAdminAppointment(appointmentId)
    }

    if (action === 'no-show') {
      await noShowAdminAppointment(appointmentId)
    }

    selectedAppointmentId.value = null
    await loadAppointments()
    toast.show('Changes saved successfully.')
  } catch (error) {
    toast.showError('Failed to save changes.')
  }
}
</script>
