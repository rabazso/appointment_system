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
      :versions="bookingRuleVersions"
      create-label="Schedule change"
      @create="openCreate"
    >
      <template #card="{ version, index }">
        <article class="rounded-xl border border-slate-200 bg-white p-4">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1 space-y-4">
              <div class="grid gap-2 sm:grid-cols-2">
                <div class="rounded-lg bg-slate-50 px-3 py-2">
                  <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Slot interval</p>
                  <p class="mt-1 text-base font-semibold text-slate-900">
                    {{ version.booking_interval_minutes }} min
                  </p>
                </div>

                <div class="rounded-lg bg-slate-50 px-3 py-2">
                  <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Booking window</p>
                  <p class="mt-1 text-base font-semibold text-slate-900">
                    {{ version.booking_window_days }} days
                  </p>
                </div>
              </div>

              <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-700">
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
                @click="deleteSelectedBookingRules(version)"
              >
                <Trash class="h-4 w-4" />
              </button>
            </div>
          </div>
        </article>
      </template>
    </VersionsView>
  </ModalShell>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { Pencil, Trash } from 'lucide-vue-next'
import VersionsView from '@/components/admin/VersionsView.vue'
import { useEmployeeBookingRuleConfigurations } from '@/composables/useEmployeeBookingRuleConfigurations'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'
import EmployeeBookingRulesEditModal from './EmployeeBookingRulesEditModal.vue'
import { addDays, maxDate, parseISODate, toISO } from '@utils/date'

defineEmits(['back', 'close'])

const props = defineProps({
  employee: {
    type: Object,
    required: true,
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

const isEditorOpen = computed(() => creatingBookingRules.value || editingBookingRules.value !== null)
const activeTitle = computed(() => {
  return 'Booking rules'
})
const activeDescription = computed(() => {
  return 'Manage booking limits and appointment rules.'
})
const createValidFromPolicy = computed(() => getCreateValidFromPolicy(bookingRuleVersions.value))

onMounted(fetchBookingRules)

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
  if (editingBookingRules.value) {
    await saveExistingBookingRules(editingBookingRules.value.id, payload)
  } else {
    await createBookingRules(payload)
  }

  closeEditor()
}

async function deleteSelectedBookingRules(bookingRules) {
  await deleteBookingRules(bookingRules.id)
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
