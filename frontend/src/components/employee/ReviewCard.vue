<script setup>
import { Eye, EyeOff, Star } from 'lucide-vue-next'

const props = defineProps({
  review: {
    type: Object,
    required: true,
  },
  showVisibilityToggle: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['open', 'toggle-visibility'])

function openReview() {
  emit('open', props.review)
}

function toggleVisibility() {
  emit('toggle-visibility', props.review.id)
}

function formatReviewDate(value) {
  if (!value) return ''
  const text = String(value)
  return text.includes('T') ? text.slice(0, 10) : text.slice(0, 10)
}

function getVisibleServices(services = []) {
  if (!Array.isArray(services) || services.length === 0) return []
  return services.slice(0, 1)
}

function getHiddenServiceCount(services = []) {
  if (!Array.isArray(services) || services.length <= 1) return 0
  return services.length - 1
}
</script>

<template>
  <article
    class="flex min-h-[12.5rem] flex-col rounded-2xl border border-black/10 p-3 transition hover:border-black/20 hover:bg-slate-50/60"
    role="button"
    tabindex="0"
    @click="openReview"
    @keydown.enter="openReview"
    @keydown.space.prevent="openReview"
  >
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <div class="mb-1 flex items-center gap-1">
          <Star
            v-for="star in 5"
            :key="star"
            class="h-4 w-4 shrink-0"
            :class="star <= Number(review.rating) ? 'fill-yellow-400 text-yellow-400' : 'text-slate-300'"
          />
        </div>
        <h3 class="truncate text-base font-semibold text-black">
          {{ review.client }}
        </h3>
        <p class="mt-1 text-xs text-slate-500">
          {{ formatReviewDate(review.created_at) }}
        </p>
      </div>

      <button
        v-if="showVisibilityToggle"
        type="button"
        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-black/10 bg-white text-slate-700 transition hover:border-black"
        :aria-label="review.is_visible ? 'Visible review' : 'Hidden review'"
        @click.stop="toggleVisibility"
      >
        <Eye v-if="review.is_visible" class="h-4 w-4" />
        <EyeOff v-else class="h-4 w-4" />
      </button>
    </div>

    <p class="mt-3 min-h-[3rem] line-clamp-2 text-sm leading-6 text-slate-700">
      {{ review.comment || 'No comment.' }}
    </p>

    <div v-if="review.services?.length" class="mt-3 flex flex-wrap gap-2">
      <span
        v-for="service in getVisibleServices(review.services)"
        :key="service"
        class="rounded-full border border-black/10 px-2.5 py-1 text-xs font-medium text-slate-600"
      >
        {{ service }}
      </span>
      <span
        v-if="getHiddenServiceCount(review.services) > 0"
        class="rounded-full border border-black/10 px-2.5 py-1 text-xs font-medium text-slate-500"
      >
        +{{ getHiddenServiceCount(review.services) }}
      </span>
    </div>
  </article>
</template>
