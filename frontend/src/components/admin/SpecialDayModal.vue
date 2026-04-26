<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 px-4 py-4"
  >
    <div class="relative flex w-96 flex-col rounded-2xl bg-white p-4 pt-12">
        <button class="absolute top-2 right-2" @click="emit('close')">
          <X class="w-8 h-8" />
        </button>

        <h2 class="text-2xl font-semibold">{{ title }}</h2>

        <div class="mt-4">
          <label class="text-xs font-semibold tracking-wider text-gray-500 leading-none">Name (optional)</label>
          <input
            v-model="form.name"
            type="text"
            placeholder="Enter name"
            class="hover:border-black transition w-full rounded-lg border border-black/10 outline-none bg-white py-2 px-3"
          />
        </div>

        <div class="mt-4">
          <label class="text-xs font-semibold text-gray-500 tracking-wider leading-none">Selected days</label>
          <div class="flex flex-col gap-2">
            <div
              v-for="(_, index) in form.days"
              :key="`special-day-${index}`"
              class="flex items-center gap-2"
            >
              <input
                v-model="form.days[index]"
                type="date"
                :min="todayISO"
                class="hover:border-black transition min-w-0 flex-1 rounded-lg border border-black/10 bg-white px-3 py-2 outline-none [font-variant-numeric:tabular-nums]"
              />

              <button
                v-if="form.days.length > 1"
                type="button"
                class="hover:border-black transition inline-flex h-[22px] w-[22px] shrink-0 items-center justify-center rounded-[6px] border border-[#d1d5db] bg-white p-0 text-[12px] font-medium leading-none text-[#475569]"
                aria-label="Remove day"
                @click="removeDay(index)"
              >
                x
              </button>
            </div>

            <div class="flex justify-end">
              <button
                type="button"
                class="hover:border-black transition p-2 rounded-lg border border-black/10 bg-white text-sm font-medium"
                @click="addDay"
              >
                + Add day
              </button>
            </div>
          </div>
        </div>

        <label class="mt-4 flex items-center gap-3">
          <ToggleButton v-model="form.isSpecial" />
          <span class="font-medium">Is special</span>
        </label>

        <div v-if="form.isSpecial" class="mt-4">
          <label class="text-xs font-semibold tracking-wider text-gray-500 leading-none">New schedule</label>
          <div class="grid grid-cols-2 rounded-lg border border-black/10 bg-white">
            <button
              type="button"
              class="h-10 rounded-lg font-bold text-sm transition m-1"
              :class="form.status === 'open' ? 'bg-emerald-100 text-emerald-900' : 'text-gray-500 hover:bg-black/5'"
              @click="form.status = 'open'"
            >
              Open
            </button>
            <button
              type="button"
              class="h-10 rounded-lg font-bold text-sm transition m-1"
              :class="form.status === 'closed' ? 'bg-rose-100 text-rose-900' : 'text-gray-500 hover:bg-black/5'"
              @click="form.status = 'closed'"
            >
              Closed
            </button>
          </div>

          <div v-if="form.status === 'open'" class="mt-4 flex items-center justify-center gap-2">
            <input v-model="form.openTime" type="time" class="p-1 rounded-lg border border-black/10 text-sm outline-none [font-variant-numeric:tabular-nums]" />
            <span class="shrink-0">-</span>
            <input v-model="form.closeTime" type="time" class="p-1 rounded-lg border border-black/10 text-sm outline-none [font-variant-numeric:tabular-nums]" />
          </div>
        </div>

        <div v-if="timeError" class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
          {{ timeError }}
        </div>

        <div v-if="showSaveButton" class="mt-4 flex justify-end">
          <Button @click="saveDay">Save</Button>
        </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { X } from 'lucide-vue-next'
import Button from '@/components/admin/Button.vue'
import ToggleButton from '@/components/admin/ToggleButton.vue'

const props = defineProps({
  day: {
    type: Object,
    default: null,
  },
  specialDays: {
    type: Array,
    default: () => [],
  },
  fetchSpecialDayByDate: {
    type: Function,
    default: null,
  },
  mode: {
    type: String,
    default: 'create',
  },
})

const emit = defineEmits(['close', 'save'])

const form = reactive(createForm(props.day))
const lookedUpSpecialDays = ref({})
const todayISO = new Date().toISOString().slice(0, 10)

const title = computed(() => (props.mode === 'edit' ? 'Edit special day' : 'Add special day'))
const initialSnapshot = computed(() => normalizeForm(createForm(props.day)))
const currentSnapshot = computed(() => normalizeForm(form))
const hasChanges = computed(() => initialSnapshot.value !== currentSnapshot.value)
const filledDays = computed(() => form.days.filter(Boolean))
const hasSelectedSpecialDay = computed(() => {
  return filledDays.value.some((dateISO) => {
    if (props.specialDays.some((specialDay) => specialDay.dateISO === dateISO)) {
      return true
    }

    return Boolean(lookedUpSpecialDays.value[dateISO])
  })
})
const hasRequiredFields = computed(() => {
  if (!filledDays.value.length) return false
  if (form.isSpecial) return true
  return hasSelectedSpecialDay.value
})
const timeError = computed(() => {
  if (!form.isSpecial || form.status !== 'open') {
    return ''
  }

  if (!form.openTime || !form.closeTime) {
    return 'Opening and closing times are required.'
  }

  if (form.closeTime <= form.openTime) {
    return 'Opening time must be earlier than closing time.'
  }

  return ''
})
const showSaveButton = computed(() => {
  return hasRequiredFields.value && (props.mode === 'create' || hasChanges.value)
})

watch(
  () => props.day,
  (day) => {
    Object.assign(form, createForm(day))
    lookedUpSpecialDays.value = {}
  },
)

watch(
  filledDays,
  async (days) => {
    if (!props.fetchSpecialDayByDate) {
      return
    }

    const missingDates = days.filter((dateISO) => {
      if (props.specialDays.some((specialDay) => specialDay.dateISO === dateISO)) {
        return false
      }

      return dateISO && !(dateISO in lookedUpSpecialDays.value)
    })

    await Promise.all(
      missingDates.map(async (dateISO) => {
        const specialDay = await props.fetchSpecialDayByDate(dateISO)
        lookedUpSpecialDays.value = {
          ...lookedUpSpecialDays.value,
          [dateISO]: specialDay,
        }
      }),
    )
  },
  { immediate: true },
)

function createForm(day) {
  const isOpen = Boolean(day?.openTime && day?.closeTime)

  return {
    id: day?.id ?? null,
    name: day?.name ?? '',
    days: [day?.dateISO ?? ''],
    isSpecial: day?.isSpecial ?? Boolean(day?.id),
    status: day?.status ?? (isOpen ? 'open' : 'closed'),
    openTime: day?.openTime?.slice(0, 5) ?? '08:00',
    closeTime: day?.closeTime?.slice(0, 5) ?? '16:00',
  }
}

function normalizeForm(value) {
  return JSON.stringify({
    id: value.id,
    name: value.name,
    days: value.days.filter(Boolean),
    isSpecial: value.isSpecial,
    status: value.status,
    openTime: value.isSpecial && value.status === 'open' ? value.openTime : '',
    closeTime: value.isSpecial && value.status === 'open' ? value.closeTime : '',
  })
}

function addDay() {
  form.days.push(todayISO)
}

function removeDay(index) {
  form.days.splice(index, 1)
}

function saveDay() {
  if (!hasRequiredFields.value) return
  if (timeError.value) return

  emit('save', {
    id: form.id,
    name: form.name.trim(),
    days: filledDays.value,
    isSpecial: form.isSpecial,
    status: form.status,
    openTime: form.status === 'open' ? form.openTime : undefined,
    closeTime: form.status === 'open' ? form.closeTime : undefined,
  })
}
</script>
