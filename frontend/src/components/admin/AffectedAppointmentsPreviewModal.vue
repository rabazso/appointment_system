<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-4"
    @click.self="$emit('cancel')"
  >
    <div class="relative flex w-full max-w-sm flex-col overflow-hidden rounded-2xl bg-white p-5">
      <button
        type="button"
        class="absolute right-2 top-2"
        @click="$emit('cancel')"
      >
        <X class="h-8 w-8" />
      </button>

      <div class="shrink-0 pt-8">
        <ModalHeader
          title="Affected appointments"
          description="Here you can cancel appointments that are affected within your change"
        />
      </div>

      <div class="flex-1 flex h-full flex-col gap-2 overflow-hidden">
        <section
          class="rounded-xl border border-red-100 bg-rose-50"
        >
          <button
            type="button"
            class="flex w-full items-center justify-between gap-3 p-3 text-left"
            @click="toggleOpenSection('conflicts')"
          >
            <div>
              <p class="text-sm font-semibold text-red-900">Conflicting appointments ({{ localPreview.conflict_preview.length }})</p>
              <p class="text-xs text-red-700">These appointments do not fit the new setup.</p>
            </div>
            <ChevronDown
              class="h-4 w-4 text-red-700 transition-transform"
              :class="isOpen('conflicts') ? 'rotate-180' : ''"
            />
          </button>

          <div v-if="isOpen('conflicts')" class="border-t border-red-100 p-3">
            <AppointmentSelectionList
              :appointments="localPreview.conflict_preview"
              :selected-appointment-ids="selectedAppointmentIds"
              tone="conflict"
              empty-label="No conflicting appointments."
              @toggle="toggleAppointment"
              @toggle-all="toggleSection(appointmentIds(localPreview.conflict_preview))"
            />
          </div>
        </section>

        <section
          class="rounded-xl border border-emerald-100 bg-emerald-50"
        >
          <button
            type="button"
            class="flex w-full items-center justify-between gap-3 p-3 text-left"
            @click="toggleOpenSection('can_remain')"
          >
            <div>
              <p class="text-sm font-semibold text-emerald-900">Can remain ({{ localPreview.non_conflict_preview.length }})</p>
              <p class="text-xs text-emerald-700">These are affected, but not conflicting.</p>
            </div>
            <ChevronDown
              class="h-4 w-4 text-emerald-700 transition-transform"
              :class="isOpen('can_remain') ? 'rotate-180' : ''"
            />
          </button>

          <div v-if="isOpen('can_remain')" class="border-t border-emerald-100 p-3">
            <AppointmentSelectionList
              :appointments="localPreview.non_conflict_preview"
              :selected-appointment-ids="selectedAppointmentIds"
              tone=""
              empty-label="No appointments can remain unchanged."
              @toggle="toggleAppointment"
              @toggle-all="toggleSection(appointmentIds(localPreview.non_conflict_preview))"
            />
          </div>
        </section>
      </div>

      <div class="shrink-0 border-slate-200 pt-4">
        <div class="ml-auto flex w-fit items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-2">
          <span class="text-xs ">Selected appointments:</span>
          <span class="text-sm font-semibold ">{{ selectedAppointmentIds.length }}</span>
        </div>

        <div class="mt-4">
          <label class="text-xs font-semibold tracking-wider text-gray-500 leading-none">Reason (required)</label>
          <textarea
            v-model="cancellationReason"
            rows="3"
            :maxlength="MAX_CANCELLATION_REASON_LENGTH"
            placeholder="Cancellation reason"
            class="w-full rounded-lg border border-slate-200 bg-white p-2 text-sm outline-none transition focus:border-black"
          />
          <p class="mt-1 text-xs text-slate-500">
            {{ cancellationReasonLength }} / {{ MAX_CANCELLATION_REASON_LENGTH }} characters
          </p>
        </div>
        <div class="mt-3 flex justify-end gap-3">
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
            @click="$emit('cancel')"
          >
            Cancel
          </button>
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-xl bg-secondary px-4 py-2 text-sm font-semibold text-black shadow-[0_10px_24px_rgba(249,115,22,0.18)] transition hover:bg-[#ffab5c] disabled:cursor-not-allowed disabled:opacity-50"
            @click="emitSave"
          >
            Save
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { ChevronDown, X } from 'lucide-vue-next'
import AppointmentSelectionList from '@/components/admin/AppointmentSelectionList.vue'
import ModalHeader from '@/components/admin/ModalHeader.vue'

const emit = defineEmits(['cancel', 'save'])

const props = defineProps({
  preview: {
    type: Object,
    required: true,
  },
})

const localPreview = ref(clonePreview(props.preview))
const selectedAppointmentIds = ref([])
const openSection = ref('conflicts')
const cancellationReason = ref('Schedule change made this appointment unavailable.')
const MAX_CANCELLATION_REASON_LENGTH = 250
const cancellationReasonLength = computed(() => cancellationReason.value.trim().length)

watch(
  () => props.preview,
  (preview) => {
    localPreview.value = clonePreview(preview)
    selectedAppointmentIds.value = []
    openSection.value = 'conflicts'
  },
)

function toggleSection(appointmentIds) {
  const selected = new Set(selectedAppointmentIds.value)
  const allSelected = appointmentIds.every((appointmentId) => selected.has(appointmentId))

  if (allSelected) {
    appointmentIds.forEach((appointmentId) => selected.delete(appointmentId))
  } else {
    appointmentIds.forEach((appointmentId) => selected.add(appointmentId))
  }

  selectedAppointmentIds.value = [...selected]
}

function toggleAppointment(appointmentId) {
  const selected = new Set(selectedAppointmentIds.value)
  if (selected.has(appointmentId)) selected.delete(appointmentId)
  else selected.add(appointmentId)
  selectedAppointmentIds.value = [...selected]
}

function isOpen(section) {
  return openSection.value === section
}

function toggleOpenSection(section) {
  openSection.value = openSection.value === section ? null : section
}

function emitSave() {
  emit('save', {
    appointment_ids: [...selectedAppointmentIds.value],
    cancellation_reason: cancellationReason.value.trim(),
  })
}

function appointmentIds(appointments) {
  return appointments.map((appointment) => appointment.id)
}

function clonePreview(preview) {
  return {
    ...preview,
    conflict_preview: [...(preview.conflict_preview ?? [])],
    non_conflict_preview: [...(preview.non_conflict_preview ?? [])],
  }
}

</script>
