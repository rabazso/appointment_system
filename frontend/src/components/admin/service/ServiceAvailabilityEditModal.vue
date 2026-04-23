<template>
  <ModalShell
    show-back
    @back="$emit('back')"
    @close="$emit('close')"
  >
    <ModalHeader
      :title="title"
      description="Update whether this service can be booked."
    />

    <div class="space-y-4">
      <div class="rounded-lg border border-black/10 p-1">
        <div class="grid grid-cols-2 gap-1">
          <button
            type="button"
            class="flex h-10 items-center justify-center rounded-md text-sm font-semibold transition"
            :class="form.is_available ? 'bg-emerald-100 text-emerald-900' : 'text-slate-500 hover:bg-slate-50'"
            @click="form.is_available = true"
          >
            Available
          </button>
          <button
            type="button"
            class="flex h-10 items-center justify-center rounded-md text-sm font-semibold transition"
            :class="!form.is_available ? 'bg-rose-100 text-rose-900' : 'text-slate-500 hover:bg-slate-50'"
            @click="form.is_available = false"
          >
            Unavailable
          </button>
        </div>
      </div>
    </div>

    <template #footer>
      <EditModalFooter
        v-model="form.valid_from"
        @cancel="$emit('cancel')"
        @save="$emit('save', { ...form })"
      />
    </template>
  </ModalShell>
</template>

<script setup>
import { computed, reactive, watch } from 'vue'
import EditModalFooter from '@/components/admin/EditModalFooter.vue'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'

defineEmits(['back', 'cancel', 'close', 'save'])

const props = defineProps({
  availability: {
    type: Object,
    default: null,
  },
})

const form = reactive(createForm(props.availability))
const title = computed(() => (props.availability ? 'Edit availability change' : 'Availability change'))

watch(
  () => props.availability,
  (availability) => {
    Object.assign(form, createForm(availability))
  },
)

function createForm(availability) {
  return {
    is_available: availability?.is_available ?? true,
    valid_from: toInputDate(availability?.valid_from) || toInputDate(new Date().toISOString()),
    valid_to: availability?.valid_to ?? null,
  }
}

function toInputDate(value) {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value).slice(0, 10)
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}
</script>
