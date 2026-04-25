<template>
  <ModalShell content-class="max-w-sm" @close="$emit('close')">
    <ModalHeader
      title="New employee"
      description="Create an employee account with the minimum required details."
    />

    <div class="space-y-3">
      <div
        class="rounded-lg border bg-white px-3 py-2 transition focus-within:border-black"
        :class="fieldError('name') ? 'border-red-500' : 'border-black/10'"
      >
        <input
          v-model="form.name"
          type="text"
          placeholder="Employee name"
          class="w-full bg-transparent text-sm outline-none"
        />
      </div>
      <p v-if="fieldError('name')" class="text-xs text-red-500">
        {{ fieldError('name') }}
      </p>

      <div
        class="rounded-lg border bg-white px-3 py-2 transition focus-within:border-black"
        :class="fieldError('email') ? 'border-red-500' : 'border-black/10'"
      >
        <input
          v-model="form.email"
          type="email"
          placeholder="Email address"
          class="w-full bg-transparent text-sm outline-none"
        />
      </div>
      <p v-if="fieldError('email')" class="text-xs text-red-500">
        {{ fieldError('email') }}
      </p>

      <div
        class="rounded-lg border bg-white px-3 py-2 transition focus-within:border-black"
        :class="fieldError('phone') ? 'border-red-500' : 'border-black/10'"
      >
        <input
          v-model="form.phone"
          type="text"
          placeholder="Phone number"
          class="w-full bg-transparent text-sm outline-none"
        />
      </div>
      <p v-if="fieldError('phone')" class="text-xs text-red-500">
        {{ fieldError('phone') }}
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

const emit = defineEmits(['close', 'save'])

defineProps({
  saving: {
    type: Boolean,
    default: false,
  },
})

const form = reactive({
  name: '',
  email: '',
  phone: '',
})

const submitted = ref(false)
const errors = computed(() => getErrors())

function handleSave() {
  submitted.value = true
  if (Object.keys(errors.value).length) return
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

  if (!form.email?.trim()) {
    nextErrors.email = 'Required'
  } else if (!form.email?.trim().includes('@')) {
    nextErrors.email = 'Invalid email'
  }

  if (!form.phone?.trim()) {
    nextErrors.phone = 'Required'
  }

  return nextErrors
}
</script>
