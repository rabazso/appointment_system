<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
  >
    <div class="relative flex w-96 flex-col rounded-2xl bg-white p-4 pt-12">
      <button class="absolute top-2 right-2" @click="emit('close')">
        <X class="w-8 h-8" />
      </button>

      <h2 class="text-2xl font-semibold">Add time off</h2>

      <div class="mt-4 flex w-full min-w-0 flex-col gap-4">
        <div>
          <label class="text-xs font-semibold tracking-wider text-gray-500 leading-none">Choose an employee</label>
          <div class="flex flex-col gap-2">
            <div
              v-for="(_, index) in form.employees"
              :key="`time-off-employee-${index}`"
              class="grid items-center gap-2"
              :class="{ '[grid-template-columns:minmax(0,1fr)_30px]': form.employees.length > 1 }"
            >
              <select
                v-model="form.employees[index]"
                class="w-full appearance-none rounded-lg border border-black/10 bg-white px-3 py-2 text-base outline-none transition hover:border-black"
              >
                <option disabled value="">Select employee</option>
                <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                  {{ employee.name }}
                </option>
              </select>

              <button
                v-if="form.employees.length > 1"
                type="button"
                class="inline-flex h-[22px] w-[22px] items-center justify-center rounded-[6px] border border-[#d1d5db] bg-white p-0 text-[12px] font-medium leading-none text-[#475569] transition hover:border-black"
                aria-label="Remove employee"
                @click="removeEmployee(index)"
              >
                x
              </button>
            </div>

            <div class="flex justify-end">
              <button
                type="button"
                class="rounded-lg border border-black/10 bg-white p-2 text-sm font-medium transition hover:border-black"
                @click="addEmployee"
              >
                + Add employee
              </button>
            </div>
          </div>
        </div>

        <div>
          <label class="text-xs font-semibold tracking-wider text-gray-500 leading-none">Choose a day</label>
          <div class="flex flex-col gap-2">
            <div
              v-for="(_, index) in form.days"
              :key="`time-off-day-${index}`"
              class="grid items-center gap-2"
              :class="{ '[grid-template-columns:minmax(0,1fr)_30px]': form.days.length > 1 }"
            >
              <input
                v-model="form.days[index]"
                type="date"
                class="w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-base outline-none transition hover:border-black [font-variant-numeric:tabular-nums]"
              />
              <button
                v-if="form.days.length > 1"
                type="button"
                class="inline-flex h-[22px] w-[22px] items-center justify-center rounded-[6px] border border-[#d1d5db] bg-white p-0 text-[12px] font-medium leading-none text-[#475569] transition hover:border-black"
                aria-label="Remove day"
                @click="removeDay(index)"
              >
                x
              </button>
            </div>

            <div class="flex justify-end">
              <button
                type="button"
                class="rounded-lg border border-black/10 bg-white p-2 text-sm font-medium transition hover:border-black"
                @click="addDay"
              >
                + Add day
              </button>
            </div>
          </div>
        </div>

        <div>
          <label class="text-xs font-semibold tracking-wider text-gray-500 leading-none">Reason (optional)</label>
          <textarea
            v-model="form.note"
            placeholder="Reason for time off"
            class="mt-1 w-full min-h-24 resize-none rounded-lg border border-black/10 bg-white px-3 py-2 text-base outline-none transition hover:border-black"
          />
        </div>
      </div>

      <div v-if="showSaveButton" class="mt-4 flex justify-end">
        <Button @click="saveTimeOff">Save</Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive } from 'vue'
import { X } from 'lucide-vue-next'
import Button from '@/components/admin/Button.vue'

const props = defineProps({
  initialDate: {
    type: String,
    default: '',
  },
  employees: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['close', 'save'])

const form = reactive(createForm())

const filledDays = computed(() => form.days.filter(Boolean))
const filledEmployees = computed(() => form.employees.filter(Boolean))
const hasRequiredFields = computed(() => Boolean(filledEmployees.value.length && filledDays.value.length))
const showSaveButton = computed(() => hasRequiredFields.value)

function createForm() {
  return {
    employees: [''],
    days: [props.initialDate ?? ''],
    note: '',
    status: 'approved',
    type: 'vacation',
  }
}

function addDay() {
  form.days.push('')
}

function removeDay(index) {
  form.days.splice(index, 1)
}

function addEmployee() {
  form.employees.push('')
}

function removeEmployee(index) {
  form.employees.splice(index, 1)
}

function saveTimeOff() {
  if (!hasRequiredFields.value) return

  emit('save', {
    employees: filledEmployees.value,
    days: filledDays.value,
    note: form.note.trim(),
    status: form.status,
    type: form.type,
  })
}
</script>
