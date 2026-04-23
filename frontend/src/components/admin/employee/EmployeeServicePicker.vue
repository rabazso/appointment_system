<template>
  <div class="space-y-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
      <div class="mb-4">
        <p class="text-sm font-semibold text-slate-900">Add services</p>
        <p class="mt-1 text-xs text-slate-500">Select one or more services to add to this employee.</p>
      </div>

      <div v-if="availableServiceOptions.length" class="grid grid-cols-2 gap-2 md:grid-cols-3">
        <button
          v-for="service in availableServiceOptions"
          :key="service.name"
          type="button"
          class="flex min-h-[84px] flex-col items-start justify-between rounded-xl border px-3 py-3 text-left text-sm font-medium transition"
          :class="isServiceAlreadyAdded(service.name)
            ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400'
            : pendingServices.includes(service.name)
              ? 'border-black bg-white text-slate-900 shadow-sm'
              : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50'"
          :disabled="isServiceAlreadyAdded(service.name)"
          @click="$emit('toggle-service', service.name)"
        >
          <span class="block w-full truncate text-sm font-semibold">
            {{ service.name }}
          </span>
          <span class="flex w-full items-center justify-end">
            <span
              v-if="isServiceAlreadyAdded(service.name)"
              class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400"
            >
              Added
            </span>
            <span
              v-else-if="pendingServices.includes(service.name)"
              class="inline-flex h-6 min-w-6 items-center justify-center rounded-full border border-black bg-white px-2 text-sm font-semibold text-black"
            >
              -
            </span>
            <span
              v-else
              class="inline-flex h-6 min-w-6 items-center justify-center rounded-full border border-slate-200 px-2 text-sm font-semibold text-slate-500"
            >
              +
            </span>
          </span>
        </button>
      </div>
      <div
        v-else
        class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-4 py-6 text-center text-sm text-slate-500"
      >
        All available services are already added.
      </div>

      <div class="mt-4 flex justify-end gap-2">
        <button
          type="button"
          class="inline-flex h-10 items-center justify-center rounded-xl border border-black/10 bg-white px-4 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50"
          @click="$emit('back')"
        >
          Cancel
        </button>
        <button
          type="button"
          class="inline-flex h-10 items-center justify-center rounded-xl bg-[#ff9838] px-4 text-sm font-semibold text-black shadow-[0_10px_24px_rgba(249,115,22,0.18)] transition hover:bg-[#ffab5c] disabled:cursor-not-allowed disabled:opacity-50"
          :disabled="!pendingServices.length"
          @click="$emit('add-services')"
        >
          Add services
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineEmits(['back', 'toggle-service', 'add-services'])

defineProps({
  availableServiceOptions: {
    type: Array,
    required: true,
  },
  pendingServices: {
    type: Array,
    required: true,
  },
  isServiceAlreadyAdded: {
    type: Function,
    required: true,
  },
})
</script>
