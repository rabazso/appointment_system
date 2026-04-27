<template>
  <EmployeeServicesEditModal
    v-if="activeView === 'editor'"
    :services="selectedServices"
    :valid-from-policy="createValidFromPolicy"
    @back="closeEditor"
    @close="$emit('close')"
    @cancel="closeEditor"
    @save="saveServices"
  />

  <EmployeeServicesView
    v-else-if="activeView === 'view'"
    :services="selectedServices"
    @back="closeView"
    @close="$emit('close')"
    @edit="openEdit(selectedServices)"
    @delete="deleteSelectedServices(selectedServices)"
  />

  <ModalShell
    v-else
    show-back
    @back="$emit('back')"
    @close="$emit('close')"
  >
    <ModalHeader
      :title="activeTitle"
      :description="activeDescription"
    />

    <VersionsView
      :versions="services"
      create-label="Service change"
      @create="openCreate"
    >
      <template #card="{ version, index }">
        <VersionCard
          :valid-from="version.valid_from"
          :valid-to="version.valid_to"
          :index="index"
          @view="openView(version)"
          @edit="openEdit(version)"
          @delete="deleteSelectedServices(version)"
        >
          <p class="text-base font-semibold text-slate-900">{{ getServicesSummary(version) }}</p>
        </VersionCard>
      </template>
    </VersionsView>
  </ModalShell>

  <AffectedAppointmentsPreviewModal
    v-if="affectedPreview"
    :preview="affectedPreview"
    @cancel="closeAffectedPreview"
    @save="persistServices(pendingPayload, $event)"
  />
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import VersionCard from '@/components/admin/VersionCard.vue'
import VersionsView from '@/components/admin/VersionsView.vue'
import { useEmployeeServiceConfigurations } from '@/composables/useEmployeeServiceConfigurations'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'
import EmployeeServicesEditModal from './EmployeeServicesEditModal.vue'
import EmployeeServicesView from './EmployeeServicesView.vue'
import { addDays, maxDate, parseISODate, toISO } from '@utils/date'
import AffectedAppointmentsPreviewModal from '@/components/admin/AffectedAppointmentsPreviewModal.vue'
import { cancelAdminAppointment, previewEmployeeServicesAffectedAppointments } from '@/api/index'
import { useToastStore } from '@/stores/ToastStore.js'

defineEmits(['back', 'close'])

const props = defineProps({
  employee: {
    type: Object,
    required: true,
  },
})

const {
  services,
  fetchServices,
  saveService: createService,
  saveExistingService,
  deleteService,
} = useEmployeeServiceConfigurations(props.employee.id)

const activeView = ref('list')
const selectedServices = ref(null)
const affectedPreview = ref(null)
const pendingPayload = ref(null)
const toast = useToastStore()

const activeTitle = computed(() => {
  return 'Services'
})
const activeDescription = computed(() => {
  return 'Manage assigned services for this employee.'
})
const createValidFromPolicy = computed(() => getCreateValidFromPolicy(services.value))

onMounted(async () => {
  try {
    await fetchServices()
  } catch (error) {
    toast.showError('Failed to load data.')
  }
})

function openView(services) {
  activeView.value = 'view'
  selectedServices.value = services
}

function closeView() {
  activeView.value = 'list'
  selectedServices.value = null
}

function openCreate() {
  activeView.value = 'editor'
  selectedServices.value = null
}

function openEdit(services) {
  activeView.value = 'editor'
  selectedServices.value = services
}

function closeEditor() {
  activeView.value = 'list'
  selectedServices.value = null
}

async function deleteSelectedServices(services) {
  try {
    await deleteService(services.id)
    closeView()
    toast.show('Changes saved successfully.')
  } catch (error) {
    toast.showError('Failed to save changes.')
  }
}

async function saveServices(payload) {
  try {
    const response = await previewEmployeeServicesAffectedAppointments({
      ...payload,
      employee_id: props.employee.id,
      valid_from: payload.valid_from ?? selectedServices.value?.valid_from,
    })

    if (!response.data.affected_count) {
      await persistServices(payload)
      return
    }

    pendingPayload.value = payload
    affectedPreview.value = response.data
  } catch (error) {
    toast.showError('Failed to save changes.')
  }
}

async function persistServices(payload, cancellations = {}) {
  if (!payload) return

  if (selectedServices.value) {
    await saveExistingService(selectedServices.value.id, payload)
  } else {
    await createService(payload)
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

function getServicesSummary(services) {
  const count = services.services?.length ?? 0
  if (!count) return 'No services assigned'
  if (count === 1) return '1 service assigned'
  return `${count} services assigned`
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
