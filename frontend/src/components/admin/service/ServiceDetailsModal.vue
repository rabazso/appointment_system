<template>
  <ModalShell
    show-back
    @back="$emit('back')"
    @close="$emit('close')"
  >
    <ModalHeader
      title="Service details"
      description="Update service name and description."
    />

    <div class="space-y-3">
      <div class="rounded-lg border border-black/10 bg-white px-3 py-2 transition focus-within:border-black">
        <input
          v-model="form.name"
          type="text"
          placeholder="Service name"
          class="w-full bg-transparent text-sm outline-none"
        />
      </div>

      <div class="h-28 rounded-lg border border-black/10 bg-white p-2 transition focus-within:border-black">
        <textarea
          v-model="form.description"
          placeholder="Service description"
          class="h-full w-full resize-none bg-transparent text-sm outline-none"
        />
      </div>

      <div class="flex justify-end gap-3 pt-3">
        <button
          type="button"
          class="inline-flex h-10 items-center justify-center rounded-xl border border-black/10 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
          @click="$emit('back')"
        >
          Cancel
        </button>
        <button
          type="button"
          class="inline-flex h-10 items-center justify-center rounded-xl bg-[#ff9838] px-6 text-sm font-semibold text-black shadow-[0_10px_24px_rgba(249,115,22,0.18)] transition hover:bg-[#ffab5c] disabled:cursor-not-allowed disabled:opacity-50"
          :disabled="saving"
          @click="saveService"
        >
          Save
        </button>
      </div>
    </div>
  </ModalShell>
</template>

<script setup>
import { ref, watch } from 'vue'
import { patchService } from '@/api/index'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'

const emit = defineEmits(['back', 'close'])

const props = defineProps({
  service: {
    type: Object,
    required: true,
  },
})

const saving = ref(false)
const form = ref(getDefaultForm(props.service))

watch(
  () => props.service,
  (service) => {
    form.value = getDefaultForm(service)
  },
)

function getDefaultForm(service) {
  return {
    name: service?.name ?? '',
    description: service?.description ?? '',
  }
}

async function saveService() {
  saving.value = true

  try {
    await patchService(props.service.id, form.value)
    emit('back')
  } finally {
    saving.value = false
  }
}
</script>
