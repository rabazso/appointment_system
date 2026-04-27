<script setup>
import { computed, onMounted, ref } from 'vue'
import { Eye, EyeOff, Star, ChevronDown } from 'lucide-vue-next'
import PageLayout from '@/components/employee/PageLayout.vue'
import { getEmployeeReviews, patchEmployeeReviewVisibility } from '@/api/index.js'

const loading = ref(true)
const saving = ref(false)
const error = ref('')
const reviews = ref([])
const savedSnapshot = ref('[]')
const originalReviews = ref([])
const visibilityFilter = ref('all')
const minRating = ref('all')
const selectedReview = ref(null)

const isDirty = computed(() => JSON.stringify(snapshotReviews(reviews.value)) !== savedSnapshot.value)

const filteredReviews = computed(() => {
  return [...reviews.value]
    .filter((review) => {
      if (visibilityFilter.value === 'visible' && !review.is_visible) return false
      if (visibilityFilter.value === 'hidden' && review.is_visible) return false

      if (minRating.value !== 'all' && Number(review.rating) < Number(minRating.value)) return false

      return true
    })
})

onMounted(async () => {
  await loadReviews()
})

async function loadReviews() {
  loading.value = true
  error.value = ''

  try {
    const response = await getEmployeeReviews()
    reviews.value = (response.data ?? []).map((review) => ({
      ...review,
      is_visible: Boolean(review.is_visible),
    }))
    originalReviews.value = reviews.value.map((review) => ({ ...review }))
    savedSnapshot.value = JSON.stringify(snapshotReviews(reviews.value))
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load reviews.'
  } finally {
    loading.value = false
  }
}

function toggleVisibility(reviewId) {
  reviews.value = reviews.value.map((review) => (
    review.id === reviewId
      ? { ...review, is_visible: !review.is_visible }
      : review
  ))
  if (selectedReview.value?.id === reviewId) {
    selectedReview.value = reviews.value.find((review) => review.id === reviewId) ?? null
  }
}

function clearFilters() {
  visibilityFilter.value = 'all'
  minRating.value = 'all'
}

function resetChanges() {
  reviews.value = originalReviews.value.map((review) => ({ ...review }))
}

async function saveChanges() {
  saving.value = true
  error.value = ''

  try {
    const original = JSON.parse(savedSnapshot.value || '[]')
    const originalMap = new Map(original.map((item) => [item.id, item.is_visible]))

    for (const review of reviews.value) {
      if (originalMap.get(review.id) === review.is_visible) continue
      await patchEmployeeReviewVisibility(review.id, { is_visible: review.is_visible })
    }

    await loadReviews()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save reviews.'
  } finally {
    saving.value = false
  }
}

function snapshotReviews(items) {
  return items.map((review) => ({
    id: review.id,
    is_visible: Boolean(review.is_visible),
  }))
}

function getVisibleServices(services = []) {
  if (!Array.isArray(services) || services.length === 0) {
    return []
  }

  return services.slice(0, 1)
}

function getHiddenServiceCount(services = []) {
  if (!Array.isArray(services) || services.length <= 1) {
    return 0
  }

  return services.length - 1
}

function openReview(review) {
  selectedReview.value = review
}

function closeReview() {
  selectedReview.value = null
}

function formatReviewDate(value) {
  if (!value) return ''
  const text = String(value)
  return text.includes('T') ? text.slice(0, 10) : text.slice(0, 10)
}
</script>

<template>
  <PageLayout
    current-section="reviews"
    title="Reviews"
    description="Manage which client reviews are visible on your employee profile."
  >
    <div class="flex min-h-0 flex-1 flex-col gap-4">
      <section class="flex min-h-0 flex-1 flex-col rounded-2xl bg-white p-6 shadow-sm md:p-8">
        <div v-if="error" class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
          {{ error }}
        </div>

        <div v-if="loading" class="text-sm text-slate-500">
          Loading reviews...
        </div>

        <div v-else class="flex min-h-0 flex-1 flex-col">
          <div class="mb-4 grid gap-3 xl:grid-cols-[9rem_9rem]">
            <div>
              <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Rating</span>
              <label class="relative block">
                <Star class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <select v-model="minRating" class="w-full appearance-none rounded-xl border border-black/10 bg-white py-3 pl-9 pr-9 text-sm outline-none transition focus:border-black">
                  <option value="all">All</option>
                  <option value="5">5+</option>
                  <option value="4">4+</option>
                  <option value="3">3+</option>
                  <option value="2">2+</option>
                  <option value="1">1+</option>
                </select>
                <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
              </label>
            </div>

            <div>
              <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Visibility</span>
              <label class="relative block">
                <Eye class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <select v-model="visibilityFilter" class="w-full appearance-none rounded-xl border border-black/10 bg-white py-3 pl-9 pr-9 text-sm outline-none transition focus:border-black">
                  <option value="all">All</option>
                  <option value="visible">Visible</option>
                  <option value="hidden">Hidden</option>
                </select>
                <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
              </label>
            </div>
          </div>

          <div class="min-h-0 flex-1 overflow-auto pr-1">
            <div v-if="filteredReviews.length" class="grid gap-3 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
              <article
                v-for="review in filteredReviews"
                :key="review.id"
                class="flex min-h-[12.5rem] flex-col rounded-2xl border border-black/10 p-3 transition hover:border-black/20 hover:bg-slate-50/60"
                role="button"
                tabindex="0"
                @click="openReview(review)"
                @keydown.enter="openReview(review)"
                @keydown.space.prevent="openReview(review)"
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
                    type="button"
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-black/10 bg-white text-slate-700 transition hover:border-black"
                    :aria-label="review.is_visible ? 'Visible review' : 'Hidden review'"
                    @click.stop="toggleVisibility(review.id)"
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
            </div>

            <div
              v-else
              class="flex min-h-0 flex-1 items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 text-sm text-slate-400"
            >
              No reviews yet
            </div>
          </div>

          <div v-if="isDirty" class="mt-4 shrink-0 -mx-6 -mb-6 rounded-b-2xl border-t border-black/10 bg-white px-6 py-4 md:-mx-8">
            <div class="flex justify-end gap-3">
              <button
                type="button"
                :disabled="saving"
                class="rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                :class="{ 'cursor-not-allowed opacity-60': saving }"
                @click="resetChanges"
              >
                Cancel
              </button>
              <button
                type="button"
                :disabled="saving"
                class="rounded-xl bg-secondary px-4 py-2 text-sm font-semibold text-black transition hover:bg-[#ffab5c]"
                :class="{ 'cursor-not-allowed opacity-60': saving }"
                @click="saveChanges"
              >
                {{ saving ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </div>
        </div>
      </section>
    </div>

    <div
      v-if="selectedReview"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6"
      @click.self="closeReview"
    >
      <div class="w-full max-w-md rounded-3xl bg-white p-5 shadow-2xl md:p-6">
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0">
            <div class="mb-2 flex items-center gap-1">
              <Star
                v-for="star in 5"
                :key="star"
                class="h-4 w-4 shrink-0"
                :class="star <= Number(selectedReview.rating) ? 'fill-yellow-400 text-yellow-400' : 'text-slate-300'"
              />
            </div>
            <h3 class="truncate text-2xl font-semibold text-black">
              {{ selectedReview.client }}
            </h3>
            <p class="mt-1 text-sm text-slate-500">
              {{ formatReviewDate(selectedReview.created_at) }}
            </p>
          </div>

          <div class="flex shrink-0 items-start gap-2">
            <button
              type="button"
              class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white text-slate-700 transition hover:border-black"
              :aria-label="selectedReview.is_visible ? 'Make review hidden' : 'Make review visible'"
              @click.stop="toggleVisibility(selectedReview.id)"
            >
              <Eye v-if="selectedReview.is_visible" class="h-4 w-4" />
              <EyeOff v-else class="h-4 w-4" />
            </button>

            <button
              type="button"
              class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white text-slate-700 transition hover:border-black"
              aria-label="Close review details"
              @click="closeReview"
            >
              <span class="text-xl leading-none">×</span>
            </button>
          </div>
        </div>

        <div class="mt-6 space-y-5">
          <div>
            <h4 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Comment</h4>
            <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-700">
              {{ selectedReview.comment || 'No comment.' }}
            </p>
          </div>

          <div>
            <h4 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Services</h4>
            <div v-if="selectedReview.services?.length" class="mt-2 flex flex-wrap gap-2">
              <span
                v-for="service in selectedReview.services"
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
  </PageLayout>
</template>
