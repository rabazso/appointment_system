<template>
  <div class="flex h-dvh overflow-hidden bg-slate-100">
    <Sidebar :isOpen="sidebarOpen" @close="sidebarOpen = false" />

    <main class="flex-1 w-full overflow-y-auto p-8">
      <Header
        title="Employees"
        description="Manage your business employees"
        @menu-click="sidebarOpen = true"
      >
        <template #actions>
          <Button @click="showCreateEmployeeModal = true">
            + new employee
          </Button>
        </template>
      </Header>

      <div
        class="mx-auto grid w-full max-w-7xl grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
      >
        <article
          v-for="employee in employees"
          :key="employee.id"
          class="flex min-h-64 cursor-pointer flex-col rounded-2xl bg-white p-4 shadow-lg transition hover:shadow-xl"
          @click="openEmployeeOverviewModal(employee)"
        >
          <div class="mb-3 flex items-center justify-between">
            <span
              class="rounded-full px-2 py-1 text-xs font-semibold"
              :class="employee.is_available ? 'bg-emerald-100 text-emerald-900' : 'bg-rose-100 text-rose-900'"
            >
              {{ employee.is_available ? 'Available' : 'Unavailable' }}
            </span>

            <button
              type="button"
              class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-500 transition hover:bg-slate-50"
              @click.stop="openEmployeeDeleteModal(employee)"
            >
              <Trash class="h-5 w-5" />
            </button>
          </div>

          <div class="mb-4 flex justify-center">
            <div class="h-24 w-24 overflow-hidden rounded-full">
              <img
                v-if="employee.profile_image?.preview_url"
                :src="employee.profile_image.preview_url"
                :alt="employee.name"
                class="h-full w-full object-cover"
              />
              <div
                v-else
                class="flex h-full w-full items-center justify-center bg-slate-200"
              >
                <User class="h-9 w-9 text-slate-500" />
              </div>
            </div>
          </div>

          <div class="mb-2 text-center">
            <h3 class="text-xl font-semibold text-black">
              {{ employee.name }}
            </h3>
          </div>

          <p class="text-center text-sm font-semibold text-black">
            <Star class="inline-block h-4 w-4 text-yellow-500 fill-current" />
            {{ formatRating(employee.rating) }}
          </p>

          <div class="mt-auto flex justify-end pt-3">
            <button
              type="button"
              class="inline-flex h-9 items-center gap-2 rounded-lg border border-black/10 bg-white px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
              @click.stop="openEmployeeConfigModal(employee)"
            >
              <Settings class="h-4 w-4" />
              Configure
            </button>
          </div>
        </article>
      </div>
    </main>
  </div>

  <EmployeeConfigurationsModal
    v-if="showEmployeeConfigModal"
    :employee="selectedEmployee"
    @close="closeEmployeeConfigModal"
  />

  <EmployeeOverviewModal
    v-if="showEmployeeOverviewModal && selectedEmployee"
    :employee="selectedEmployee"
    @close="closeEmployeeOverviewModal"
  />

  <EmployeeCreateModal
    v-if="showCreateEmployeeModal"
    :saving="savingEmployee"
    @close="closeCreateEmployeeModal"
    @save="saveEmployee"
  />

  <EmployeeDeleteModal
    v-if="showEmployeeDeleteModal && selectedEmployee"
    :employee="selectedEmployee"
    @close="closeEmployeeDeleteModal"
    @confirm="removeSelectedEmployee"
  />

</template>

<script setup>
import { nextTick, ref, onMounted } from 'vue'
import { Settings, Trash, User } from 'lucide-vue-next'
import Button from '@/components/admin/Button.vue'
import EmployeeCreateModal from '@/components/admin/employee/EmployeeCreateModal.vue'
import EmployeeDeleteModal from '@/components/admin/employee/EmployeeDeleteModal.vue'
import EmployeeConfigurationsModal from '@/components/admin/employee/EmployeeConfigureModal.vue'
import EmployeeOverviewModal from '@/components/admin/employee/EmployeeOverviewModal.vue'
import Header from '@/components/admin/Header.vue'
import Sidebar from '@/components/admin/Sidebar.vue'
import { createEmployee, deleteEmployee, getEmployees } from '@/api'
import { useToastStore } from '@/stores/ToastStore.js'
import { Star } from 'lucide-vue-next'

const employees = ref([])
const sidebarOpen = ref(false)
const selectedEmployee = ref(null)
const showEmployeeConfigModal = ref(false)
const showEmployeeOverviewModal = ref(false)
const showCreateEmployeeModal = ref(false)
const showEmployeeDeleteModal = ref(false)
const savingEmployee = ref(false)
const toast = useToastStore()

onMounted(async () => {
  await fetchEmployees()
})

async function fetchEmployees() {
  try {
    employees.value = (await getEmployees()).data.data
  } catch (error) {
    toast.showError('Failed to load data.')
  }
}

function openEmployeeOverviewModal(employee) {
  selectedEmployee.value = employee
  showEmployeeOverviewModal.value = true
}

async function closeEmployeeOverviewModal() {
  showEmployeeOverviewModal.value = false
  await nextTick()
  selectedEmployee.value = null
}

function openEmployeeConfigModal(employee) {
  selectedEmployee.value = employee
  showEmployeeConfigModal.value = true
}

async function closeEmployeeConfigModal() {
  showEmployeeConfigModal.value = false
  await nextTick()
  selectedEmployee.value = null
}

function openEmployeeDeleteModal(employee) {
  selectedEmployee.value = employee
  showEmployeeDeleteModal.value = true
}

async function closeEmployeeDeleteModal() {
  showEmployeeDeleteModal.value = false
  await nextTick()
  selectedEmployee.value = null
}

function closeCreateEmployeeModal() {
  showCreateEmployeeModal.value = false
}

async function saveEmployee(payload) {
  savingEmployee.value = true

  try {
    await createEmployee(payload)
    await fetchEmployees()
    closeCreateEmployeeModal()
    toast.show('Changes saved successfully.')
  } catch (error) {
    toast.showError('Failed to save changes.')
  } finally {
    savingEmployee.value = false
  }
}

async function removeSelectedEmployee() {
  if (!selectedEmployee.value) return

  try {
    await deleteEmployee(selectedEmployee.value.id)
    await fetchEmployees()
    await closeEmployeeDeleteModal()
    toast.show('Changes saved successfully.')
  } catch (error) {
    toast.showError('Failed to save changes.')
  }
}

function formatRating(rating) {
  const value = Number(rating)
  return Number.isFinite(value) ? value.toFixed(1) : '0.0'
}

</script>
