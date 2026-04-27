<template>
  <EmployeeAvailabilityEditModal
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
      :title="activeTitle"
      :description="activeDescription"
    />

    <VersionsView
      :versions="availabilityVersions"
      create-label="Schedule Change"
      @create="openCreate"
    >
      <template #card="{ version, index }">
        <article class="rounded-xl border border-slate-200 bg-white p-4">
          <div class="flex items-start justify-between gap-4">
            <div>
              <span
                class="inline-flex rounded-full px-4 py-1.5 text-sm font-semibold leading-none"
                :class="version.is_available ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
              >
                {{ version.is_available ? 'Available' : 'Unavailable' }}
              </span>

              <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-700">
                <span class="text-slate-900">
                  <span class="font-medium">{{ index === 0 ? 'Valid since:' : 'Valid from:' }}</span>
                  {{ version.valid_from }}
                </span>
                <span v-if="version.valid_to">
                  <span class="font-medium">{{ index === 0 ? 'Valid until:' : 'Valid to:' }}</span>
                  {{ version.valid_to }}
                </span>
              </div>
            </div>

            <div class="flex shrink-0 gap-2">
              <button
                type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-500 transition hover:bg-slate-50"
                @click="openEdit(version)"
              >
                <Pencil class="h-4 w-4" />
              </button>
              <button
                v-if="index !== 0"
                type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-500 transition hover:bg-slate-50"
                @click="deleteSelectedAvailability(version)"
              >
                <Trash class="h-4 w-4" />
              </button>
            </div>
          </div>
        </article>
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
import { Pencil, Trash } from 'lucide-vue-next'
import VersionsView from '@/components/admin/VersionsView.vue'
import { useEmployeeAvailabilityConfigurations } from '@/composables/useEmployeeAvailabilityConfigurations'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'
import EmployeeAvailabilityEditModal from './EmployeeAvailabilityEditModal.vue'
import { addDays, maxDate, parseISODate, toISO } from '@utils/date'
import AffectedAppointmentsPreviewModal from '@/components/admin/AffectedAppointmentsPreviewModal.vue'
import { cancelAdminAppointment, previewEmployeeAvailabilityAffectedAppointments } from '@/api/index'
import { useToastStore } from '@/stores/ToastStore.js'

defineEmits(['back', 'close'])

const props = defineProps({
  employee: {
    type: Object,
    required: true,
  },
})

const {
  availability: availabilityVersions,
  fetchAvailability,
  saveAvailability: createAvailability,
  saveExistingAvailability,
  deleteAvailability,
} = useEmployeeAvailabilityConfigurations(props.employee.id)

const activeView = ref('list')
const selectedAvailability = ref(null)
const affectedPreview = ref(null)
const pendingPayload = ref(null)
const toast = useToastStore()

const activeTitle = computed(() => {
  return 'Availability'
})
const activeDescription = computed(() => {
  return 'Manage when this employee can be booked.'
})
const createValidFromPolicy = computed(() => getCreateValidFromPolicy(availabilityVersions.value))

onMounted(async () => {
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

function openEdit(availability) {
  activeView.value = 'editor'
  selectedAvailability.value = availability
}

function closeEditor() {
  activeView.value = 'list'
  selectedAvailability.value = null
}

async function saveAvailability(payload) {
  try {
    const response = await previewEmployeeAvailabilityAffectedAppointments({
      ...payload,
      employee_id: props.employee.id,
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

async function deleteSelectedAvailability(availability) {
  try {
    await deleteAvailability(availability.id)
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
