<template>
  <div class="space-y-3">
    <div>
      <label class="mr-4 text-sm">Valid from:</label>
      <input
        :value="modelValue"
        type="date"
        class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm outline-none transition focus:border-black"
        @input="$emit('update:modelValue', $event.target.value)"
      />
    </div>

    <div class="flex justify-end gap-3">
      <button
        type="button"
        class="inline-flex h-10 items-center justify-center rounded-xl border border-black/10 bg-white px-4 font-semibold text-slate-700 transition hover:bg-slate-50"
        @click="$emit('cancel')"
      >
        Cancel
      </button>
      <button
        type="button"
        class="inline-flex h-10 items-center justify-center rounded-xl bg-[#ff9838] px-6 text-[14px] font-semibold text-black shadow-[0_10px_24px_rgba(249,115,22,0.18)] transition hover:bg-[#ffab5c] disabled:cursor-not-allowed disabled:opacity-50"
        :disabled="disabled || !modelValue"
        @click="$emit('save')"
      >
        {{ saving ? 'Saving...' : 'Save' }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineEmits(['cancel', 'save', 'update:modelValue'])

defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  saving: {
    type: Boolean,
    default: false,
  },
})
</script>
