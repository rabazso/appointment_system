<template>
  <ModalShell content-class="max-w-lg" @close="$emit('close')">
    <div class="space-y-5">
      <div class="flex flex-col gap-4">
        <div class="flex items-start gap-4">
          <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-slate-100">
            <img
              v-if="employee.profile_image?.preview_url"
              :src="employee.profile_image.preview_url"
              :alt="employee.name"
              class="h-full w-full object-cover"
            />
            <div
              v-else
              class="flex h-full w-full items-center justify-center bg-slate-100"
            >
              <User class="h-8 w-8 text-slate-500" />
            </div>
          </div>

          <div class="min-w-0 flex-1">
            <div class="space-y-3">
              <div class="min-w-0">
                <h2 class="text-2xl font-semibold tracking-tight text-slate-950">{{ employee.name }}</h2>
                <p class="mt-1 text-sm text-slate-500">Quick overview of this employee.</p>
              </div>

              <div class="flex flex-wrap items-center gap-4">
                <div
                  class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold"
                  :class="employee.is_available ? 'bg-emerald-100 text-emerald-900' : 'bg-rose-100 text-rose-900'"
                >
                  <span class="h-2 w-2 rounded-full" :class="employee.is_available ? 'bg-emerald-500' : 'bg-rose-500'" />
                  {{ employee.is_available ? 'Available' : 'Unavailable' }}
                </div>

                <div class="inline-flex items-center gap-1 text-sm font-semibold text-slate-950">
                  <Star class="h-4 w-4 fill-amber-400 text-amber-400" />
                  {{ formatRating(employee.rating) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-3">
          <RouterLink
            :to="'/barbers/' + employee.id"
            class="flex w-full items-center gap-4 rounded-xl border border-slate-200 bg-white px-4 py-4 transition hover:border-slate-300 hover:bg-slate-50"
          >
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600">
              <ContactRound class="h-5 w-5" />
            </div>
            <div>
              <p class="text-lg font-semibold text-slate-900">Profile</p>
              <p class="mt-1 text-sm text-slate-500">
                {{ employee.is_available ? 'Currently available for bookings.' : 'Currently unavailable for bookings.' }}
              </p>
            </div>
          </RouterLink>

          <RouterLink
            :to="{name: 'Appointments', query:{employeeId: employee.id}}"
            type="button"
            class="flex w-full items-center gap-4 rounded-xl border border-slate-200 bg-white px-4 py-4 text-left transition hover:border-slate-300 hover:bg-slate-50"
          >
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600">
              <CalendarDays class="h-5 w-5" />
            </div>
            <div>
              <p class="text-lg font-semibold text-slate-900">Appointments</p>
              <p class="mt-1 text-sm text-slate-500">Review and manage this employee's appointments.</p>
            </div>
          </RouterLink>

          <RouterLink
            :to="{ name: 'AdminReviews', query: { employeeId: employee.id } }"
            type="button"
            class="flex w-full items-center gap-4 rounded-xl border border-slate-200 bg-white px-4 py-4 text-left transition hover:border-slate-300 hover:bg-slate-50"
          >
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600">
              <MessageSquareText class="h-5 w-5" />
            </div>
            <div>
              <p class="text-lg font-semibold text-slate-900">Reviews</p>
              <p class="mt-1 text-sm text-slate-500">Review and manage customer feedback for this employee.</p>
            </div>
          </RouterLink>
        </div>
      </div>
    </div>
  </ModalShell>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import { CalendarDays, ContactRound, MessageSquareText, Star, User } from 'lucide-vue-next'
import ModalShell from '@/components/admin/ModalShell.vue'

defineEmits(['close'])

const props = defineProps({
  employee: {
    type: Object,
    required: true,
  },
})

function formatRating(rating) {
  const value = Number(rating)
  return Number.isFinite(value) ? value.toFixed(1) : '0.0'
}

</script>
