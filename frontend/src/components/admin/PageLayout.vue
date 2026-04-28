<script setup>
import { ref } from 'vue'
import Header from '@/components/admin/Header.vue'
import Sidebar from '@/components/admin/Sidebar.vue'
import { useSlots } from 'vue'

defineEmits(['action-click'])

const props = defineProps({
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
})

const sidebarOpen = ref(false)
const slots = useSlots()
</script>

<template>
  <div class="flex h-dvh overflow-hidden bg-slate-100">
    <Sidebar :is-open="sidebarOpen" @close="sidebarOpen = false" />

    <main class="flex min-h-0 flex-1 flex-col overflow-y-auto p-4 md:p-6 xl:p-8">
      <Header
        :title="props.title"
        :description="props.description"
        :action-label="props.actionLabel"
        @menu-click="sidebarOpen = true"
        @action-click="$emit('action-click')"
      >
        <template v-if="slots.actions" #actions>
          <slot name="actions" />
        </template>
      </Header>

      <slot />
    </main>
  </div>
</template>
