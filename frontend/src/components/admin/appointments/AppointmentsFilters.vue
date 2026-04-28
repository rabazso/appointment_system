<template>
  <div class="rounded-2xl bg-white p-5 shadow-lg">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-semibold text-slate-950">Filters</h2>
      <button
        type="button"
        class="text-sm font-medium text-black transition hover:text-slate-900"
        @click="$emit('reset')"
      >
        Reset
      </button>
      <button
        type="button"
        class=""
        @click="$emit('close')"
      >
        <X class="h-8 w-8" />
      </button>
    </div>

    <div class="mt-5 space-y-2">
      <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 leading-none">Search</label>
        <input
          v-model="model.search"
          type="text"
          placeholder="Customer, employee, service"
          class="mt-1 w-full rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
        />
      </div>

      <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 leading-none">Service</label>
        <Select v-model="serviceIdSelect">
          <SelectTrigger>
            <SelectValue placeholder="All services" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem :value="ALL_SELECT_VALUE">All services</SelectItem>
            <SelectItem v-for="service in services" :key="service.id" :value="String(service.id)">
              {{ service.name }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 leading-none">Employee</label>
        <Select v-model="employeeIdSelect">
          <SelectTrigger>
            <SelectValue placeholder="All employees" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem :value="ALL_SELECT_VALUE">All employees</SelectItem>
            <SelectItem v-for="employee in employees" :key="employee.id" :value="String(employee.id)">
              {{ employee.name }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 leading-none">Status</label>
        <Select v-model="statusSelect">
          <SelectTrigger>
            <SelectValue placeholder="All" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem
              v-for="option in statusOptions"
              :key="option.value || 'all'"
              :value="option.value === '' ? ALL_SELECT_VALUE : option.value"
            >
              {{ option.label }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 leading-none">Quick date filter</label>
        <div class="mt-1 grid grid-cols-3 gap-2">
          <button type="button" class="rounded-xl border px-3 py-2 text-sm font-medium transition hover:border-black" @click="setQuickDate('today')">
            Today
          </button>
          <button type="button" class="rounded-xl border px-3 py-2 text-sm font-medium transition hover:border-black" @click="setQuickDate('this_week')">
            This week
          </button>
          <button type="button" class="rounded-xl border px-3 py-2 text-sm font-medium transition hover:border-black" @click="setQuickDate('this_month')">
            This month
          </button>
        </div>
      </div>

      <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 leading-none">Date</label>
        <input
          v-model="singleDate"
          type="date"
          class="mt-1 w-full rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
        />
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 leading-none">From</label>
          <input
            v-model="model.dateFrom"
            type="date"
            class="mt-1 w-full rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
          />
        </div>

        <div>
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 leading-none">To</label>
          <input
            v-model="model.dateTo"
            type="date"
            class="mt-1 w-full rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
          />
        </div>
      </div>

      <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 leading-none">Order by</label>
        <div class="mt-2 grid grid-cols-2 gap-2">
          <Select v-model="model.orderBy">
            <SelectTrigger>
              <SelectValue placeholder="Date & time" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="start_datetime">Date & time</SelectItem>
              <SelectItem value="created_at">Created at</SelectItem>
            </SelectContent>
          </Select>

          <Select v-model="model.orderDirection">
            <SelectTrigger>
              <SelectValue placeholder="Desc" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="desc">Desc</SelectItem>
              <SelectItem value="asc">Asc</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { X } from 'lucide-vue-next'
import { computed } from 'vue'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const emit = defineEmits(['reset', 'close'])

const props = defineProps({
  model: {
    type: Object,
    required: true,
  },
  services: {
    type: Array,
    required: true,
  },
  employees: {
    type: Array,
    required: true,
  },
  statusOptions: {
    type: Array,
    required: true,
  },
})

const ALL_SELECT_VALUE = '__all__'

const serviceIdSelect = computed({
  get: () => (props.model.serviceId === '' ? ALL_SELECT_VALUE : props.model.serviceId),
  set: (value) => {
    props.model.serviceId = value === ALL_SELECT_VALUE ? '' : value
  },
})

const employeeIdSelect = computed({
  get: () => (props.model.employeeId === '' ? ALL_SELECT_VALUE : props.model.employeeId),
  set: (value) => {
    props.model.employeeId = value === ALL_SELECT_VALUE ? '' : value
  },
})

const statusSelect = computed({
  get: () => (props.model.status === '' ? ALL_SELECT_VALUE : props.model.status),
  set: (value) => {
    props.model.status = value === ALL_SELECT_VALUE ? '' : value
  },
})

const singleDate = computed({
  get() {
    if (props.model.dateFrom && props.model.dateFrom === props.model.dateTo) {
      return props.model.dateFrom
    }

    return ''
  },
  set(value) {
    props.model.dateFrom = value
    props.model.dateTo = value
  },
})

function setQuickDate(value) {
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  if (value === 'today') {
    const iso = today.toISOString().slice(0, 10)
    props.model.dateFrom = iso
    props.model.dateTo = iso
    return
  }

  if (value === 'this_week') {
    const day = today.getDay()
    const diff = (day + 6) % 7
    const start = new Date(today)
    start.setDate(today.getDate() - diff)
    const end = new Date(start)
    end.setDate(start.getDate() + 6)
    props.model.dateFrom = start.toISOString().slice(0, 10)
    props.model.dateTo = end.toISOString().slice(0, 10)
    return
  }

  if (value === 'this_month') {
    const start = new Date(today.getFullYear(), today.getMonth(), 1)
    const end = new Date(today.getFullYear(), today.getMonth() + 1, 0)
    props.model.dateFrom = start.toISOString().slice(0, 10)
    props.model.dateTo = end.toISOString().slice(0, 10)
  }
}
</script>
