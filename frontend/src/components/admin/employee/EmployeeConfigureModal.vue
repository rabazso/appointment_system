<template>
  <ModalShell
    v-if="activeSection === 'menu'"
    @close="$emit('close')"
  >
    <ModalHeader
      title="Employee Configuration"
      description="Choose what you want to configure for this employee."
    />

    <div class="space-y-3">
      <button
        v-for="item in menuItems"
        :key="item.section"
        type="button"
        class="flex w-full items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-4 text-left transition hover:bg-slate-50"
        @click="activeSection = item.section"
      >
        <div class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600">
          <component
            :is="item.icon"
            v-if="item.icon"
            class="h-4.5 w-4.5"
          />
        </div>
        <div>
          <p class="text-lg font-semibold text-slate-900">{{ item.title }}</p>
          <p class="mt-1 text-sm text-slate-500">{{ item.description }}</p>
        </div>
      </button>
    </div>
  </ModalShell>

    <EmployeeScheduleModal
      v-else-if="activeSection === 'schedule'"
      :employee="employee"
      @back="showMenu"
      @close="$emit('close')"
    />

</template>

<script setup>
import { ref } from 'vue'
import { Clock } from 'lucide-vue-next'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'
import EmployeeScheduleModal from './EmployeeScheduleModal.vue'

defineEmits(['close'])

defineProps({
  employee: {
    type: Object,
    required: true,
  },
})

const activeSection = ref('menu')

const menuItems = [
  {
    section: 'schedule',
    title: 'Schedule',
    description: 'Review schedule changes and working hours.',
    icon: Clock,
  }
]

function showMenu() {
  activeSection.value = 'menu'
}
</script>
