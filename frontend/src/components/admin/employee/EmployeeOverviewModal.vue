<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
    @click.self="$emit('close')"
  >
    <div
      class="relative w-full max-w-2xl rounded-2xl bg-white p-5 text-slate-900 shadow-[0_24px_80px_rgba(15,23,42,0.18)]"
    >
      <button
        type="button"
        class="absolute right-3 top-3 text-slate-500 transition hover:text-slate-900"
        @click="$emit('close')"
      >
        <X class="h-6 w-6" />
      </button>

      <div class="mb-5 flex items-center gap-3">
        <div class="h-14 w-14 overflow-hidden rounded-xl bg-slate-100">
          <img
            v-if="employee.avatar || employee.photo_url"
            :src="employee.avatar || employee.photo_url"
            :alt="employee.name"
            class="h-full w-full object-cover"
          />
          <div
            v-else
            class="flex h-full w-full items-center justify-center bg-slate-100"
          >
            <User class="h-6 w-6 text-slate-500" />
          </div>
        </div>

        <div>
          <h2 class="text-2xl font-semibold tracking-tight text-slate-950">{{ employee.name }}</h2>
          <p class="mt-1 text-sm text-slate-500">{{ employeeMeta.role }}</p>
        </div>
      </div>

      <div class="grid gap-3 md:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-3.5">
          <p class="text-xl font-semibold text-slate-950">Profile</p>
          <p class="mt-1 text-base text-slate-500">{{ employeeMeta.experience }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-3.5">
          <p class="text-xl font-semibold text-slate-950">Appointments</p>
          <p class="mt-1 text-base text-slate-500">{{ employeeMeta.appointments }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-3.5 md:col-span-2">
          <p class="text-xl font-semibold text-slate-950">Time off</p>
          <p class="mt-1 text-base text-slate-500">{{ employeeMeta.timeOff }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { User, X } from 'lucide-vue-next'

defineEmits(['close'])

const props = defineProps({
  employee: {
    type: Object,
    required: true,
  },
})

const employeeMeta = computed(() => {
  const employeeMetaMap = {
    'Haircut Harry': {
      role: 'Senior barber',
      experience: '6 years experience',
      appointments: '8 upcoming',
      timeOff: 'No time off scheduled',
    },
    'Bouncy Bella': {
      role: 'Color specialist',
      experience: '8 years experience',
      appointments: '12 upcoming',
      timeOff: '2 upcoming leave days',
    },
    'Blowout Ben': {
      role: 'Senior stylist',
      experience: '9 years experience',
      appointments: '10 upcoming',
      timeOff: 'No time off scheduled',
    },
    'Loud Lucy': {
      role: 'Junior barber',
      experience: '3 years experience',
      appointments: '4 upcoming',
      timeOff: '1 upcoming leave',
    },
    'Crispy Chris': {
      role: 'Fade specialist',
      experience: '7 years experience',
      appointments: '7 upcoming',
      timeOff: 'No time off scheduled',
    },
  }

  return employeeMetaMap[props.employee.name] ?? {
    role: 'Senior barber',
    experience: '5 years experience',
    appointments: '5 upcoming',
    timeOff: 'No time off scheduled',
  }
})
</script>
