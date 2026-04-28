<template>
  <EmployeeBookingRulesEditModal
    v-if="isEditorOpen"
    :booking-rules="editingBookingRules"
    :valid-from-policy="createValidFromPolicy"
    @back="closeEditor"
    @close="$emit('close')"
    @cancel="closeEditor"
    @save="saveSelectedBookingRules"
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
      :versions="resolvedVersions"
      create-label="Schedule change"
      @create="openCreate"
    >
      <template #card="{ version, index }">
        <VersionCard
          :valid-from="version.valid_from"
          :valid-to="version.valid_to"
          :index="index"
          @view="openEdit(version)"
          @edit="openEdit(version)"
          @delete="deleteSelectedBookingRules(version)"
        >
          <div class="gap-1 flex flex-row flex-wrap">
            <div class="">
              <p class="mx-auto text-base font-semibold text-slate-800">
                Booking slot interval: <span class="">{{ version.booking_interval_minutes }} min</span>
              </p>
            </div>

            <div class="">
              <p class="mx-auto text-base font-semibold text-slate-800">
                Booking window: <span class="">{{ version.booking_window_days }} days</span>
              </p>
            </div>
          </div>
        </VersionCard>
      </template>
    </VersionsView>
  </ModalShell>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import VersionCard from '@/components/admin/VersionCard.vue'
import VersionsView from '@/components/admin/VersionsView.vue'
import { useEmployeeBookingRuleConfigurations } from '@/composables/useEmployeeBookingRuleConfigurations'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'
import EmployeeBookingRulesEditModal from './EmployeeBookingRulesEditModal.vue'
import { addDays, maxDate, parseISODate, toISO } from '@utils/date'
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
  bookingRules: bookingRuleVersions,
  fetchBookingRules,
  saveBookingRules: createBookingRules,
  saveExistingBookingRules,
  deleteBookingRules,
} = useEmployeeBookingRuleConfigurations(props.employee.id)

const creatingBookingRules = ref(false)
const editingBookingRules = ref(null)
const toast = useToastStore()

const isEditorOpen = computed(() => creatingBookingRules.value || editingBookingRules.value !== null)
const activeTitle = computed(() => {
  return 'Booking rules'
})
const activeDescription = computed(() => {
  return 'Manage booking limits and appointment rules.'
})
const resolvedVersions = computed(() => props.versions ?? bookingRuleVersions.value)
const createValidFromPolicy = computed(() => getCreateValidFromPolicy(resolvedVersions.value))

onMounted(async () => {
  if (props.versions !== null) return

  try {
    await fetchBookingRules()
  } catch (error) {
    toast.showError('Failed to load data.')
  }
})

function openCreate() {
  creatingBookingRules.value = true
  editingBookingRules.value = null
}

function openEdit(bookingRules) {
  creatingBookingRules.value = false
  editingBookingRules.value = bookingRules
}

function closeEditor() {
  creatingBookingRules.value = false
  editingBookingRules.value = null
}

async function saveSelectedBookingRules(payload) {
  try {
    if (editingBookingRules.value) {
      await saveExistingBookingRules(editingBookingRules.value.id, payload)
    } else {
      await createBookingRules(payload)
    }

    if (props.refreshSection) {
      await props.refreshSection()
    }

    closeEditor()
    toast.show('Changes saved successfully.')
  } catch (error) {
    toast.showError('Failed to save changes.')
  }
}

async function deleteSelectedBookingRules(bookingRules) {
  try {
    await deleteBookingRules(bookingRules.id)
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
