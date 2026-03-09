<template>
  <div class="flex h-dvh overflow-hidden bg-slate-100">
    <Sidebar />

    <main class="flex-1 w-full overflow-y-auto p-6">
      <header class="mb-8 flex items-center justify-between rounded-2xl bg-white p-8 shadow-sm">
        <div>
          <h1 class="text-4xl font-semibold text-black">Employees</h1>
          <p class="mt-1 text-xs text-gray-500">Manage your business employees</p>
        </div>

        <Button @click="openAddModal">
          + new employee
        </Button>
      </header>

      <div class="max-w-7xl mx-auto w-full grid gap-6 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
        <article
          v-for="employee in employees"
          :key="employee.id"
          class="flex min-h-64 flex-col rounded-2xl bg-white p-4 shadow-lg"
        >
          <div class="mb-3 flex items-center justify-between">
            <span
              class="rounded-full px-2 py-1 text-xs font-semibold"
              :class="employee.active ? 'bg-emerald-100 text-emerald-900' : 'bg-rose-100 text-rose-900'"
            >
              {{ employee.active ? 'Active' : 'Inactive' }}
            </span>

            <button
              class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-500 transition hover:bg-slate-50"
              @click="openDeleteModal(employee)">
              <Trash class="h-5 w-5" />
            </button>
          </div>

          <div class="mb-4 flex justify-center">
            <div class="h-24 w-24 overflow-hidden rounded-full">
              <img
                v-if="employee.avatar"
                :src="employee.avatar"
                :alt="employee.name"
                class="h-full w-full"
              />
              <div
                v-else
                class="flex h-full w-full items-center justify-center bg-slate-200"
              >
                <User class="h-9 w-9 text-slate-500" />
              </div>
            </div>
          </div>

          <div class="mb-2">
            <h3 class="text-xl font-semibold text-black text-center">
              {{ employee.name }}
            </h3>
          </div>

          <p class="text-sm text-black text-center font-semibold">
            ⭐ {{ Number(employee.rating).toFixed(1) }}
          </p>

          <div class="mt-auto flex justify-end pt-3">
            <ToggleButton v-model="employee.active" @click="toggleEmployeeStatus(employee.id)"/>
          </div>
        </article>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

import { Trash, User } from 'lucide-vue-next'
import Button from '@/components/admin/Button.vue'
import ToggleButton from '@/components/admin/ToggleButton.vue'
import Sidebar from '@/components/admin/Sidebar.vue'
import { getEmployees } from '@/api/index'

  const employees = ref([])

 onMounted(async () => {
  const data = (await getEmployees()).data

  employees.value = data.map((employee) => ({
    ...employee,
    rating: (Math.random() * 5).toFixed(1),
  }))
})

</script>