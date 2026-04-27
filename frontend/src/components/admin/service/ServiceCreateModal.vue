<template>
  <ModalShell content-class="max-w-sm" @close="$emit('close')">
    <ModalHeader
      title="New service"
      description="Create a new service with its basic details."
    />

    <div class="space-y-3">
      <div
        class="rounded-lg border bg-white px-3 py-2 transition focus-within:border-black"
        :class="fieldError('name') ? 'border-red-500' : 'border-black/10'"
      >
        <input
          v-model="form.name"
          type="text"
          placeholder="Service name"
          class="w-full bg-transparent text-sm outline-none"
        />
      </div>
      <p v-if="fieldError('name')" class="text-xs text-red-500">
        {{ fieldError('name') }}
      </p>

      <div
        class="h-28 rounded-lg border bg-white p-2 transition focus-within:border-black"
        :class="fieldError('description') ? 'border-red-500' : 'border-black/10'"
      >
        <textarea
          v-model="form.description"
          placeholder="Service description"
          class="h-full w-full resize-none bg-transparent text-sm outline-none"
        />
      </div>
      <p v-if="fieldError('description')" class="text-xs text-red-500">
        {{ fieldError('description') }}
      </p>

      <div class="flex justify-end gap-3 pt-3">
        <button
          type="button"
          class="inline-flex h-10 items-center justify-center rounded-xl border border-black/10 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
          @click="$emit('close')"
        >
          Cancel
        </button>
        <button
          type="button"
          class="inline-flex h-10 items-center justify-center rounded-xl bg-[#ff9838] px-6 text-sm font-semibold text-black shadow-[0_10px_24px_rgba(249,115,22,0.18)] transition hover:bg-[#ffab5c] disabled:cursor-not-allowed disabled:opacity-50"
          :disabled="saving"
          @click="handleSave"
        >
          Save
        </button>
      </div>
    </div>
  </ModalShell>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'
import { useToastStore } from '@/stores/ToastStore.js'

const emit = defineEmits(['close', 'save'])

defineProps({
  saving: {
    type: Boolean,
    default: false,
  },
})

const form = reactive({
  name: '',
  description: '',
})

const submitted = ref(false)
const errors = computed(() => getErrors())
const toast = useToastStore()

function handleSave() {
  submitted.value = true
  if (Object.keys(errors.value).length) {
    toast.showError('Failed to save changes.')
    return
  }
  emit('save', { ...form })
}

function fieldError(field) {
  return submitted.value ? errors.value[field] : null
}

function getErrors() {
  const nextErrors = {}

  if (!form.name?.trim()) {
    nextErrors.name = 'Required'
  }

  if (!form.description?.trim()) {
    nextErrors.description = 'Required'
  }

  return nextErrors
}
</script>
