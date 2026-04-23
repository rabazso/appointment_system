<template>
  <ModalShell
    show-back
    @back="$emit('back')"
    @close="$emit('close')"
  >
    <ModalHeader
      title="Service details"
      description="Review the selected assigned services."
    />

    <VersionDetailActions
      :valid-from="services.valid_from"
      :valid-to="services.valid_to"
      @edit="$emit('edit')"
      @delete="$emit('delete')"
    />

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
      <template v-if="services.services?.length">
        <div
          v-for="(service, index) in services.services"
          :key="service.name"
          class="grid grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-4 px-5 py-4"
          :class="index !== services.services.length - 1 ? 'border-b border-slate-100' : ''"
        >
          <span class="text-sm font-medium text-slate-700">{{ service.name }}</span>
          <span class="text-sm font-semibold text-slate-900">{{ service.price }} Ft</span>
          <span class="text-sm font-medium text-slate-500">{{ service.duration }} min</span>
        </div>
      </template>

      <div v-else class="px-4 py-6 text-center text-sm text-slate-500">
        No services assigned.
      </div>
    </div>
  </ModalShell>
</template>

<script setup>
import ModalShell from '@/components/admin/ModalShell.vue'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import VersionDetailActions from './VersionDetailActions.vue'

defineEmits(['back', 'close', 'edit', 'delete'])

defineProps({
  services: {
    type: Object,
    required: true,
  },
})
</script>
