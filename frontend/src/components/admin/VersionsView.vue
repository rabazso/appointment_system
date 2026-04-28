<template>
  <div class="space-y-4">
    <div v-if="showCreate" class="flex justify-end">
      <button
        type="button"
        class="inline-flex h-10 shrink-0 items-center justify-center rounded-xl bg-[#ff9838] px-5 text-sm font-semibold text-black shadow-[0_10px_24px_rgba(249,115,22,0.18)] transition hover:bg-[#ffab5c] disabled:cursor-not-allowed disabled:opacity-50"
        @click="$emit('create')"
      >
        + {{ createLabel }}
      </button>
    </div>

    <div v-if="versions.length" class="space-y-3">
      <p class="pt-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Current</p>
      <template
        v-for="(version, index) in versions"
        :key="version.id"
      >
        <p
          v-if="index === 1"
          class="pt-2 text-xs font-semibold uppercase tracking-wide text-slate-500"
        >
          Upcoming
        </p>

        <slot
          name="card"
          :version="version"
          :index="index"
          :variant="index === 0 ? 'current' : 'upcoming'"
        />
      </template>
    </div>

    <div v-else class="rounded-xl border border-dashed border-slate-200 bg-slate-50/70 px-4 py-6 text-center text-sm text-slate-500">
      No versions yet.
    </div>
  </div>
</template>

<script setup>
defineEmits(['create'])

defineProps({
  versions: {
    type: Array,
    required: true
  },
  createLabel: {
    type: String,
    default: 'Schedule change',
  },
  showCreate: {
    type: Boolean,
    default: true,
  },
})

</script>
