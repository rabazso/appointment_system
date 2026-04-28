<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6"
    @click.self="$emit('close')"
  >
    <div class="w-full max-w-md rounded-3xl bg-white p-5 shadow-2xl md:p-6">
      <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
          <div class="mb-2 flex items-center gap-1">
            <Star
              v-for="star in 5"
              :key="star"
              class="h-4 w-4 shrink-0"
              :class="star <= Number(review.rating) ? 'fill-yellow-400 text-yellow-400' : 'text-slate-300'"
            />
          </div>
          <h3 class="truncate text-2xl font-semibold text-black">
            {{ review.client }}
          </h3>
          <p class="mt-1 text-sm text-slate-500">
            {{ formatReviewDate(review.created_at) }}
          </p>
        </div>

        <div class="flex shrink-0 items-start gap-2">
          <button
            v-if="showVisibilityToggle"
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white text-slate-700 transition hover:border-black"
            :aria-label="review.is_visible ? 'Make review hidden' : 'Make review visible'"
            @click="$emit('toggle-visibility', review.id)"
          >
            <Eye v-if="review.is_visible" class="h-4 w-4" />
            <EyeOff v-else class="h-4 w-4" />
          </button>

          <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white text-slate-700 transition hover:border-black"
            aria-label="Close review details"
            @click="$emit('close')"
          >
            <span class="text-xl leading-none">×</span>
          </button>
        </div>
      </div>

      <div class="mt-6 space-y-5">
        <div>
          <h4 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Comment</h4>
          <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-700">
            {{ review.comment || 'No comment.' }}
          </p>
        </div>

        <div>
          <h4 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Services</h4>
          <div v-if="review.services?.length" class="mt-2 flex flex-wrap gap-2">
            <span
              v-for="service in review.services"
              :key="service"
              class="rounded-full border border-black/10 px-2.5 py-1 text-xs font-medium text-slate-600"
            >
              {{ service }}
            </span>
          </div>
          <p v-else class="mt-2 text-sm text-slate-400">
            No services listed.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Eye, EyeOff, Star } from 'lucide-vue-next'

defineProps({
  review: {
    type: Object,
    required: true,
  },
  formatReviewDate: {
    type: Function,
    required: true,
  },
  showVisibilityToggle: {
    type: Boolean,
    default: true,
  },
})

defineEmits(['close', 'toggle-visibility'])
</script>
