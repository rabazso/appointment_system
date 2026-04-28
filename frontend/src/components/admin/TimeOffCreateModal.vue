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
              <Select v-model="form.employees[index]">
                <SelectTrigger class="w-full rounded-lg border-black/10 bg-white px-3 py-2 text-base">
                  <SelectValue placeholder="Select employee" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="employee in employees" :key="employee.id" :value="String(employee.id)">
                    {{ employee.name }}
                  </SelectItem>
                </SelectContent>
              </Select>

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
              <PopoverRoot
                :open="openDayPickerIndex === index"
                @update:open="(open) => setDayPickerOpen(index, open)"
              >
                <PopoverTrigger as-child>
                  <button
                    type="button"
                    class="w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-base outline-none transition hover:border-black [font-variant-numeric:tabular-nums] flex items-center justify-between"
                  >
                    <span>{{ form.days[index] || 'YYYY-MM-DD' }}</span>
                    <CalendarIcon class="h-4 w-4 text-slate-500" />
                  </button>
                </PopoverTrigger>
                <PopoverPortal>
                  <PopoverContent
                    side="bottom"
                    align="start"
                    :side-offset="6"
                    :collision-padding="12"
                    position-strategy="fixed"
                    class="z-[90] w-auto rounded-xl border border-slate-200 bg-white p-2 shadow-lg"
                  >
                    <Calendar
                      :model-value="calendarValue(form.days[index])"
                      layout="month-and-year"
                      class="rounded-md"
                      :min-value="todayDateValue"
                      @update:model-value="(value) => setDayAtIndex(index, value)"
                    />
                  </PopoverContent>
                </PopoverPortal>
              </PopoverRoot>
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
          <label class="text-xs font-semibold tracking-wider text-gray-500 leading-none">Reason</label>
          <textarea
            v-model="form.note"
            placeholder="Reason for time off"
            class="mt-1 w-full min-h-24 resize-none rounded-lg border bg-white px-3 py-2 text-base outline-none transition hover:border-black"
            :class="noteError ? 'border-rose-500' : 'border-black/10'"
          />
          <p v-if="noteError" class="mt-1 whitespace-normal break-words text-xs leading-snug text-rose-500">
            {{ noteError }}
          </p>
        </div>
      </div>

      <div v-if="showSaveButton" class="mt-4 flex justify-end">
        <Button @click="saveTimeOff">Save</Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { parseDate } from '@internationalized/date'
import { computed, reactive, ref } from 'vue'
import { Calendar as CalendarIcon, X } from 'lucide-vue-next'
import { PopoverContent, PopoverPortal, PopoverRoot, PopoverTrigger } from 'reka-ui'
import Button from '@/components/admin/Button.vue'
import { Calendar } from '@/components/ui/calendar'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { useToastStore } from '@/stores/ToastStore.js'

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
const todayISO = new Date().toISOString().slice(0, 10)
const todayDateValue = parseDate(todayISO)

const form = reactive(createForm())
const submitted = ref(false)
const toast = useToastStore()
const openDayPickerIndex = ref(null)

const filledDays = computed(() => form.days.filter(Boolean))
const filledEmployees = computed(() => form.employees.filter(Boolean))
const noteError = computed(() => {
  if (!submitted.value) {
    return ''
  }

  return form.note.trim() ? '' : 'Required'
})
const hasRequiredFields = computed(() => Boolean(
  filledEmployees.value.length &&
  filledDays.value.length,
))
const showSaveButton = computed(() => hasRequiredFields.value)

function createForm() {
  return {
    employees: [''],
    days: [props.initialDate || todayISO],
    note: '',
    status: 'approved',
  }
}

function addDay() {
  form.days.push(todayISO)
}

function removeDay(index) {
  form.days.splice(index, 1)

  if (openDayPickerIndex.value === index) {
    openDayPickerIndex.value = null
  } else if (openDayPickerIndex.value > index) {
    openDayPickerIndex.value -= 1
  }
}

function addEmployee() {
  form.employees.push('')
}

function removeEmployee(index) {
  form.employees.splice(index, 1)
}

function calendarValue(value) {
  if (!value) return undefined

  try {
    return parseDate(value)
  } catch {
    return undefined
  }
}

function toIsoDate(value) {
  return value?.toString?.() || ''
}

function setDayAtIndex(index, value) {
  form.days[index] = toIsoDate(value)
  openDayPickerIndex.value = null
}

function setDayPickerOpen(index, isOpen) {
  openDayPickerIndex.value = isOpen ? index : (openDayPickerIndex.value === index ? null : openDayPickerIndex.value)
}

function saveTimeOff() {
  submitted.value = true

  if (!hasRequiredFields.value) {
    toast.showError('Failed to save changes.')
    return
  }

  emit('save', {
    employees: filledEmployees.value,
    days: filledDays.value,
    note: form.note.trim(),
    status: form.status,
  })
}
</script>
