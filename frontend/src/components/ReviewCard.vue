<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
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
const servicesWrap = ref(null)
const availableWidth = ref(0)

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

function measureWidth() {
  availableWidth.value = servicesWrap.value?.clientWidth ?? 0
}

function estimateChipWidth(text) {
  return Math.ceil(text.length * 6) + 18
}

const visibleServices = computed(() => {
  const services = Array.isArray(props.review.services) ? props.review.services : []
  if (!services.length) return []
  if (!availableWidth.value) return services.slice(0, 1)

  const gap = 8
  const moreChipWidth = estimateChipWidth(`+${Math.max(services.length - 1, 0)}`)
  let used = 0
  const visible = []

  for (let index = 0; index < services.length; index += 1) {
    const service = services[index]
    const chipWidth = estimateChipWidth(service)
    const remaining = services.length - index - 1
    const reserve = remaining > 0 ? moreChipWidth + gap : 0
    const nextUsed = visible.length === 0 ? chipWidth : used + gap + chipWidth

    if (nextUsed + reserve > availableWidth.value) break

    visible.push(service)
    used = nextUsed
  }

  return visible.length ? visible : services.slice(0, 1)
})

const hiddenServiceCount = computed(() => {
  const services = Array.isArray(props.review.services) ? props.review.services : []
  return Math.max(services.length - visibleServices.value.length, 0)
})

let resizeObserver

onMounted(async () => {
  await nextTick()
  measureWidth()

  if (typeof ResizeObserver !== 'undefined' && servicesWrap.value) {
    resizeObserver = new ResizeObserver(() => {
      measureWidth()
    })
    resizeObserver.observe(servicesWrap.value)
  }
})

onBeforeUnmount(() => {
  if (resizeObserver) {
    resizeObserver.disconnect()
  }
})
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

    <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-700">
      {{ review.comment || 'No comment.' }}
    </p>
  </article>
</template>
