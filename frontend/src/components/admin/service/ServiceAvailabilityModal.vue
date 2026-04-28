<template>
  <ServiceAvailabilityEditModal
    v-if="activeView === 'editor'"
    :availability="selectedAvailability"
    :valid-from-policy="createValidFromPolicy"
    @back="closeEditor"
    @close="$emit('close')"
    @cancel="closeEditor"
    @save="saveAvailability"
  />

  <ModalShell
    v-else
    show-back
    @back="$emit('back')"
    @close="$emit('close')"
  >
    <ModalHeader
      title="Availability"
      description="Manage when this service can be booked."
    />

    <VersionsView
      :versions="resolvedVersions"
      create-label="Availability change"
      @create="openCreate"
    >
      <template #card="{ version, index }">
        <VersionCard
          :valid-from="version.valid_from"
          :valid-to="version.valid_to"
          :index="index"
          @view="openEdit(version)"
          @edit="openEdit(version)"
          @delete="deleteSelectedAvailability(version)"
        >
          <span
            class="inline-flex rounded-full px-4 py-1.5 text-sm font-semibold leading-none"
            :class="version.is_available ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
          >
            {{ version.is_available ? 'Available' : 'Unavailable' }}
          </span>
        </VersionCard>
      </template>
    </VersionsView>
  </ModalShell>

  <AffectedAppointmentsPreviewModal
    v-if="affectedPreview"
    :preview="affectedPreview"
    @cancel="closeAffectedPreview"
    @save="persistAvailability(pendingPayload, $event)"
  />
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import VersionCard from '@/components/admin/VersionCard.vue'
import VersionsView from '@/components/admin/VersionsView.vue'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'
import { useServiceAvailabilityConfigurations } from '@/composables/useServiceAvailabilityConfigurations'
import ServiceAvailabilityEditModal from './ServiceAvailabilityEditModal.vue'
import { addDays, maxDate, parseISODate, toISO } from '@utils/date'
import AffectedAppointmentsPreviewModal from '@/components/admin/AffectedAppointmentsPreviewModal.vue'
import { cancelAdminAppointment, previewServiceAvailabilityAffectedAppointments } from '@/api/index'
import { useToastStore } from '@/stores/ToastStore.js'

defineEmits(['back', 'close'])

const props = defineProps({
  service: {
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
  availability,
  fetchAvailability,
  saveAvailability: createAvailability,
  saveExistingAvailability,
  deleteAvailability,
} = useServiceAvailabilityConfigurations(props.service.id)

const activeView = ref('list')
const selectedAvailability = ref(null)
const affectedPreview = ref(null)
const pendingPayload = ref(null)
const toast = useToastStore()

const resolvedVersions = computed(() => props.versions ?? availability.value)
const createValidFromPolicy = computed(() => getCreateValidFromPolicy(resolvedVersions.value))

onMounted(async () => {
  if (props.versions !== null) return

  try {
    await fetchAvailability()
  } catch (error) {
    toast.showError('Failed to load data.')
  }
})

function openCreate() {
  activeView.value = 'editor'
  selectedAvailability.value = null
}

function openEdit(availabilityVersion) {
  activeView.value = 'editor'
  selectedAvailability.value = availabilityVersion
}

function closeEditor() {
  activeView.value = 'list'
  selectedAvailability.value = null
}

async function saveAvailability(payload) {
  try {
    const response = await previewServiceAvailabilityAffectedAppointments({
      ...payload,
      service_id: props.service.id,
      valid_from: payload.valid_from ?? selectedAvailability.value?.valid_from,
    })

    if (!response.data.affected_count) {
      await persistAvailability(payload)
      return
    }

    pendingPayload.value = payload
    affectedPreview.value = response.data
  } catch (error) {
    toast.showError('Failed to save changes.')
  }
}

async function persistAvailability(payload, cancellations = {}) {
  if (!payload) return

  if (selectedAvailability.value) {
    await saveExistingAvailability(selectedAvailability.value.id, payload)
  } else {
    await createAvailability(payload)
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

async function deleteSelectedAvailability(availabilityVersion) {
  try {
    await deleteAvailability(availabilityVersion.id)
    if (props.refreshSection) {
      await props.refreshSection()
    }
    toast.show('Changes saved successfully.')
  } catch (error) {
    toast.showError('Failed to save changes.')
  }
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
