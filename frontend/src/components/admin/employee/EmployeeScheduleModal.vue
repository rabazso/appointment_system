<template>
  <EmployeeScheduleEditModal
    v-if="activeView === 'editor'"
    :schedule="selectedSchedule"
    :valid-from-policy="createValidFromPolicy"
    @back="closeEditor"
    @close="$emit('close')"
    @cancel="closeEditor"
    @save="saveSchedule"
  />

  <EmployeeScheduleView
    v-else-if="activeView === 'view'"
    :schedule="selectedSchedule"
    @back="closeView"
    @close="$emit('close')"
    @edit="openEdit(selectedSchedule)"
    @delete="deleteSelectedSchedule(selectedSchedule)"
  />

  <ModalShell
    v-else
    :showBack="true"
    @back="$emit('back')"
    @close="$emit('close')"
  >
    <ModalHeader
      :title="activeTitle"
      :description="activeDescription"
    />

    <VersionsView
      :versions="resolvedVersions"
      create-label="Schedule change"
      @create="openCreate"
    >
      <template #card="{ version, index }">
        <VersionCard
          :valid-from="version.valid_from"
          :valid-to="version.valid_to"
          :index="index"
          @view="openView(version)"
          @edit="openEdit(version)"
          @delete="deleteSelectedSchedule(version)"
        >
          <p class="text-base font-semibold text-slate-900">{{ getScheduleSummary(version) }}</p>
        </VersionCard>
      </template>
    </VersionsView>
  </ModalShell>

  <AffectedAppointmentsPreviewModal
    v-if="affectedPreview"
    :preview="affectedPreview"
    @cancel="closeAffectedPreview"
    @save="persistSchedule(pendingPayload, $event)"
  />
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import VersionCard from '@/components/admin/VersionCard.vue'
import VersionsView from '@/components/admin/VersionsView.vue'
import { useEmployeeScheduleConfigurations } from '@/composables/useEmployeeScheduleConfigurations'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'
import EmployeeScheduleEditModal from './EmployeeScheduleEditModal.vue'
import EmployeeScheduleView from './EmployeeScheduleView.vue'
import { addDays, maxDate, parseISODate, toISO } from '@utils/date'
import AffectedAppointmentsPreviewModal from '@/components/admin/AffectedAppointmentsPreviewModal.vue'
import { cancelAdminAppointment, previewEmployeeScheduleAffectedAppointments } from '@/api/index'
import { useToastStore } from '@/stores/ToastStore.js'

defineEmits(['back', 'close'])

const props = defineProps({
  employee: {
    type: Object,
    required: true,
  },
  versions: {
    type: Array,
    default: null,
  },
  refreshSection: {
    type: Function,
    default: null,
  },
})

const {
  schedules,
  fetchSchedules,
  saveSchedule: createSchedule,
  saveExistingSchedule,
  deleteSchedule,
} = useEmployeeScheduleConfigurations(props.employee.id)

const activeView = ref('list')
const selectedSchedule = ref(null)
const affectedPreview = ref(null)
const pendingPayload = ref(null)
const toast = useToastStore()

const activeTitle = computed(() => {
  return 'Schedule'
})
const activeDescription = computed(() => {
  return 'Review schedule changes and working hours.'
})
const resolvedVersions = computed(() => props.versions ?? schedules.value)
const createValidFromPolicy = computed(() => getCreateValidFromPolicy(resolvedVersions.value))

onMounted(async () => {
  if (props.versions !== null) return

  try {
    await fetchSchedules()
  } catch (error) {
    toast.showError('Failed to load data.')
  }
})

function openView(schedule) {
  activeView.value = 'view'
  selectedSchedule.value = schedule
}

function closeView() {
  activeView.value = 'list'
  selectedSchedule.value = null
}

async function deleteSelectedSchedule(schedule) {
  try {
    await deleteSchedule(schedule.id)
    if (props.refreshSection) {
      await props.refreshSection()
    }
    closeView()
    toast.show('Changes saved successfully.')
  } catch (error) {
    toast.showError('Failed to save changes.')
  }
}

function openCreate() {
  activeView.value = 'editor'
  selectedSchedule.value = null
}

function openEdit(schedule) {
  activeView.value = 'editor'
  selectedSchedule.value = schedule
}

function closeEditor() {
  activeView.value = 'list'
  selectedSchedule.value = null
}

async function saveSchedule(payload) {
  try {
    const response = await previewEmployeeScheduleAffectedAppointments({
      ...payload,
      employee_id: props.employee.id,
      valid_from: payload.valid_from ?? selectedSchedule.value?.valid_from,
    })

    if (!response.data.affected_count) {
      await persistSchedule(payload)
      return
    }

    pendingPayload.value = payload
    affectedPreview.value = response.data
  } catch (error) {
    toast.showError('Failed to save changes.')
  }
}

async function persistSchedule(payload, cancellations = {}) {
  if (!payload) return

  if (selectedSchedule.value) {
    await saveExistingSchedule(selectedSchedule.value.id, payload)
  } else {
    await createSchedule(payload)
  }

  if (props.refreshSection) {
    await props.refreshSection()
  }

  await cancelPendingAppointments(cancellations)
  closeAffectedPreview()
  closeEditor()
  toast.show('Changes saved successfully.')
}

async function cancelPendingAppointments({ appointment_ids: appointmentIds = [], cancellation_reason: reason = '' } = {}) {
  if (!appointmentIds.length) return

  await Promise.all(
    appointmentIds.map((appointmentId) =>
      cancelAdminAppointment(appointmentId, { cancellation_reason: reason }),
    ),
  )
}

function closeAffectedPreview() {
  affectedPreview.value = null
  pendingPayload.value = null
}

function getScheduleSummary(schedule) {
  const count = schedule.weeklyHours.filter((day) => day.isOpen).length
  if (!count) return 'No working days'
  if (count === 1) return '1 working day'
  return `${count} working days`
}

function getCreateValidFromPolicy(versions) {
  const tomorrow = addDays(new Date(), 1)
  const latestValidFrom = versions
    .map((version) => version.valid_from)
    .filter(Boolean)
    .sort()
    .at(-1)

  const min = latestValidFrom
    ? maxDate(tomorrow, addDays(parseISODate(latestValidFrom), 1))
    : tomorrow

  return {
    editable: true,
    min: toISO(min),
    max: null,
  }
}

</script>
