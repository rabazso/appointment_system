<script setup>
import { ref } from 'vue'
import Header from '@/components/admin/Header.vue'
import Sidebar from '@/components/employee/Sidebar.vue'
defineEmits(['action-click'])

const props = defineProps({
  currentSection: {
    type: String,
    default: 'appointments',
  },
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    required: true,
  },
  actionLabel: {
    type: String,
    default: '',
  },
  showAction: {
    type: Boolean,
    default: false,
  },
})

const sidebarOpen = ref(false)
</script>

<template>
  <div class="flex h-dvh overflow-hidden bg-slate-100">
    <Sidebar
      :is-open="sidebarOpen"
      :current-section="currentSection"
      @close="sidebarOpen = false"
    />

    <main class="flex min-h-0 flex-1 flex-col overflow-y-auto p-4 md:p-8">
      <Header
        :title="props.title"
        :description="props.description"
        :action-label="props.actionLabel"
        :show-action="props.showAction"
        @menu-click="sidebarOpen = true"
        @action-click="$emit('action-click')"
      />

      <slot />
    </main>
  </div>
</template>
