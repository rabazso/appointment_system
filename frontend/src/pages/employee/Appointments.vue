<script setup>
import { computed, onMounted, ref } from 'vue'
import AppointmentSchedule from '@/components/employee/AppointmentSchedule.vue'
import AppointmentCancelModal from '@/components/employee/appointment-schedule/AppointmentCancelModal.vue'
import PageLayout from '@/components/PageLayout.vue'
import {
  cancelEmployeeAppointment,
  completeEmployeeAppointment,
  getEmployeeAppointments,
  markEmployeeAppointmentNoShow,
} from '@/api/index.js'
import { useToastStore } from '@/stores/ToastStore.js'

const cancelModalOpen = ref(false)
const cancellingAppointment = ref(false)
const cancelAppointmentId = ref(null)
const cancellationReason = ref('')
const appointments = ref([])
const workingHours = ref([])
const toast = useToastStore()

const CANCELLABLE_STATUSES = ['pending', 'confirmed']
const MAX_CANCELLATION_REASON_LENGTH = 250

const loadData = async () => {
  try {
    const appointmentsResponse = await getEmployeeAppointments({
      statuses: ['pending', 'confirmed', 'completed', 'no_show', 'cancelled'],
    })
    workingHours.value = appointmentsResponse.data.working_hours
    appointments.value = appointmentsResponse.data.data
  } catch {
    appointments.value = []
    workingHours.value = []
    toast.showError('Failed to load data.')
  }
}

const appointmentToCancel = computed(() =>
  appointments.value.find((appointment) => appointment.id === cancelAppointmentId.value) || null,
)

const getAppointmentTimestamp = (appointment) => new Date(appointment.start_datetime).getTime()

const trimmedCancellationReason = computed(() => cancellationReason.value.trim())
const cancellationReasonLength = computed(() => trimmedCancellationReason.value.length)

const openCancelModal = (appointmentId) => {
  const appointment = appointments.value.find((item) => item.id === appointmentId)
  if (!appointment || !CANCELLABLE_STATUSES.includes(appointment.status)) {
    return
  }

  const timestamp = getAppointmentTimestamp(appointment)
  if (!Number.isFinite(timestamp) || timestamp <= Date.now()) {
    toast.showError('Appointment can only be cancelled before it starts.')
    return
  }

  cancelAppointmentId.value = appointmentId
  cancellationReason.value = ''
  cancelModalOpen.value = true
}

const closeCancelModal = () => {
  cancelModalOpen.value = false
  cancelAppointmentId.value = null
  cancellationReason.value = ''
}

const formatAppointmentTime = (appointment) => {
  const timestamp = getAppointmentTimestamp(appointment)
  if (!Number.isFinite(timestamp)) {
    return '--:--'
  }

  return new Date(timestamp).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  })
}

const confirmCancelAppointment = async () => {
  const appointmentId = cancelAppointmentId.value
  if (!appointmentId || cancellingAppointment.value) {
    return
  }

  cancellingAppointment.value = true

  try {
    const reason = trimmedCancellationReason.value
    await cancelEmployeeAppointment(appointmentId, { cancellation_reason: reason })

    const appointment = appointments.value.find((x) => x.id === appointmentId)
    if (appointment) {
      appointment.status = 'cancelled'
      appointment.cancellation_reason = reason
      appointment.cancelled_by = 'employee'
    }

    closeCancelModal()
    toast.show('Changes saved successfully.')
  } catch {
    toast.showError('Failed to save changes.')
  } finally {
    cancellingAppointment.value = false
  }
}

const onCompleteAppointment = async (appointmentId) => {
  try {
    await completeEmployeeAppointment(appointmentId)
    const appointment = appointments.value.find((x) => x.id === appointmentId)
    if (appointment) {
      appointment.status = 'completed'
    }
    toast.show('Changes saved successfully.')
  } catch {
    toast.showError('Failed to save changes.')
  }
}

const onMarkNoShow = async (appointmentId) => {
  try {
    await markEmployeeAppointmentNoShow(appointmentId)
    const appointment = appointments.value.find((x) => x.id === appointmentId)
    if (appointment) {
      appointment.status = 'no_show'
    }
    toast.show('Changes saved successfully.')
  } catch {
    toast.showError('Failed to save changes.')
  }
}

onMounted(() => {
  loadData()
})
</script>

<template>
  <PageLayout
    role="employee"
    title="Your Appointments"
    description="Manage your appointments and daily workflow."
  >
    <div class="flex min-h-0 flex-1 flex-col gap-6">
      <AppointmentSchedule
        :appointments="appointments"
        :working-hours="workingHours"
        @cancel-appointment="openCancelModal"
        @complete-appointment="onCompleteAppointment"
        @mark-no-show="onMarkNoShow"
      />

      <AppointmentCancelModal
        :open="cancelModalOpen"
        :appointment="appointmentToCancel"
        :reason="cancellationReason"
        :reason-length="cancellationReasonLength"
        :max-reason-length="MAX_CANCELLATION_REASON_LENGTH"
        :close-disabled="cancellingAppointment"
        :format-appointment-time="formatAppointmentTime"
        @update:reason="cancellationReason = $event"
        @close="closeCancelModal"
        @submit="confirmCancelAppointment"
      />
    </div>
  </PageLayout>
</template>
