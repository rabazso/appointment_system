<script setup>
import { ref } from 'vue'
import Header from '@/components/admin/Header.vue'
import EmployeeSidebar from '@/components/employee/EmployeeSidebar.vue'

const sidebarOpen = ref(false)
const currentSection = ref('appointments')
</script>

<template>
  <div class="flex h-dvh overflow-hidden bg-slate-100">
    <EmployeeSidebar
      :is-open="sidebarOpen"
      :current-section="currentSection"
      @close="sidebarOpen = false"
      @select-section="currentSection = $event"
    />

    <main class="flex min-h-0 flex-1 flex-col overflow-y-auto p-4 md:p-8">
      <Header
        title="Employee Dashboard"
        description="Manage appointments, profile updates, and availability."
        :show-action="false"
        @menu-click="sidebarOpen = true"
      />

      <section
        v-if="currentSection === 'appointments'"
        class="rounded-2xl bg-white p-8 shadow-sm"
      >
        <p class="text-sm text-gray-500">Appointments will move into this employee workspace.</p>
      </section>

      <section
        v-else-if="currentSection === 'profile'"
        class="rounded-2xl bg-white p-8 shadow-sm"
      >
        <p class="text-sm text-gray-500">Profile editing tools will live here.</p>
      </section>

      <section
        v-else
        class="rounded-2xl bg-white p-8 shadow-sm"
      >
        <p class="text-sm text-gray-500">Time-off management will live here.</p>
      </section>
    </main>
  </div>
</template>
