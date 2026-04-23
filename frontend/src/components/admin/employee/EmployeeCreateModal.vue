<template>
  <ModalShell content-class="max-w-sm" @close="$emit('close')">
    <ModalHeader
      title="New employee"
      description="Create an employee account with the minimum required details."
    />

    <div class="space-y-3">
      <div class="rounded-lg border border-black/10 bg-white px-3 py-2 transition focus-within:border-black">
        <input
          v-model="form.name"
          type="text"
          placeholder="Employee name"
          class="w-full bg-transparent text-sm outline-none"
        />
      </div>

      <div class="rounded-lg border border-black/10 bg-white px-3 py-2 transition focus-within:border-black">
        <input
          v-model="form.email"
          type="email"
          placeholder="Email address"
          class="w-full bg-transparent text-sm outline-none"
        />
      </div>

      <div class="rounded-lg border border-black/10 bg-white px-3 py-2 transition focus-within:border-black">
        <input
          v-model="form.phone"
          type="text"
          placeholder="Phone number"
          class="w-full bg-transparent text-sm outline-none"
        />
      </div>

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
          @click="$emit('save', { ...form })"
        >
          Save
        </button>
      </div>
    </div>
  </ModalShell>
</template>

<script setup>
import { reactive } from 'vue'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'

defineEmits(['close', 'save'])

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
</script>
