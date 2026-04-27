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
        <select
          v-model="model.serviceId"
          class="mt-1 w-full appearance-none rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
        >
          <option value="">All services</option>
          <option v-for="service in services" :key="service.id" :value="String(service.id)">
            {{ service.name }}
          </option>
        </select>
      </div>

      <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 leading-none">Employee</label>
        <select
          v-model="model.employeeId"
          class="mt-1 w-full appearance-none rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
        >
          <option value="">All employees</option>
          <option v-for="employee in employees" :key="employee.id" :value="String(employee.id)">
            {{ employee.name }}
          </option>
        </select>
      </div>

      <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 leading-none">Status</label>
        <select
          v-model="model.status"
          class="mt-1 w-full appearance-none rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
        >
          <option v-for="option in statusOptions" :key="option.value || 'all'" :value="option.value">
            {{ option.label }}
          </option>
        </select>
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
          class="mt-1 w-full [font-variant-numeric:tabular-nums] rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
        />
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 leading-none">From</label>
          <input
            v-model="model.dateFrom"
            type="date"
            class="mt-1 w-full [font-variant-numeric:tabular-nums] rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
          />
        </div>

        <div>
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 leading-none">To</label>
          <input
            v-model="model.dateTo"
            type="date"
            class="mt-1 w-full [font-variant-numeric:tabular-nums] rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
          />
        </div>
      </div>

      <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 leading-none">Order by</label>
        <div class="mt-2 grid grid-cols-2 gap-2">
          <select
            v-model="model.orderBy"
            class="w-full rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
          >
            <option value="start_datetime">Date & time</option>
            <option value="created_at">Created at</option>
          </select>

          <select
            v-model="model.orderDirection"
            class="w-full rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
          >
            <option value="desc">Desc</option>
            <option value="asc">Asc</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { X } from 'lucide-vue-next'
import { computed } from 'vue'

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
