<template>
  <PageLayout
    current-section="reviews"
    title="Reviews"
    description="Check reviews written for your appointments."
  >
    <div class="flex min-h-0 flex-1 flex-col gap-4">
      <section class="flex min-h-0 flex-1 flex-col rounded-2xl bg-white p-6 shadow-sm md:p-8">
        <div v-if="loading" class="text-sm text-slate-500">
          Loading reviews...
        </div>

        <div v-else class="flex min-h-0 flex-1 flex-col">
          <div class="mb-4 grid gap-3 xl:grid-cols-[max-content_max-content_1fr_max-content_max-content] xl:items-end">
            <div>
              <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Rating</span>
              <Select v-model="filters.minRating">
                <SelectTrigger>
                  <SelectValue placeholder="All" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All</SelectItem>
                  <SelectItem value="5">5</SelectItem>
                  <SelectItem value="4">4</SelectItem>
                  <SelectItem value="3">3</SelectItem>
                  <SelectItem value="2">2</SelectItem>
                  <SelectItem value="1">1</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div>
              <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Visibility</span>
              <Select v-model="filters.visibility">
                <SelectTrigger>
                  <SelectValue placeholder="All" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All</SelectItem>
                  <SelectItem value="visible">Visible</SelectItem>
                  <SelectItem value="hidden">Hidden</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div></div>

            <div class="inline-flex items-center gap-1 rounded-xl border border-black/10 bg-white p-1">
              <p class="text-sm text-slate-500 p-1">Average:</p>
              <p class="text-sm font-semibold text-slate-900">{{ averageRating }}</p>
            </div>

            <div class="inline-flex items-center gap-1 rounded-xl border border-black/10 bg-white p-1">
              <p class="text-sm text-slate-500 p-1">Count:</p>
              <p class="text-sm font-semibold text-slate-900">{{ reviewedCount }}</p>
            </div>
          </div>

          <div class="min-h-0 flex-1 overflow-auto pr-1">
            <div v-if="filteredReviews.length" class="grid gap-3 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
              <ReviewCard
                v-for="review in filteredReviews"
                :key="review.id"
                :review="review"
                :show-visibility-toggle="false"
                @open="openReview"
              />
            </div>

            <div
              v-else
              class="flex h-full flex-1 items-center justify-center rounded-2xl text-sm"
            >
              No reviews yet
            </div>
          </div>
        </div>
      </section>
    </div>

    <ReviewDetailsModal
      v-if="selectedReview"
      :review="selectedReview"
      :show-visibility-toggle="false"
      :format-review-date="formatReviewDate"
      @close="closeReview"
    />
  </PageLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PageLayout from '@/components/employee/PageLayout.vue'
import ReviewCard from '@/components/ReviewCard.vue'
import ReviewDetailsModal from '@/components/ReviewDetailsModal.vue'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { getEmployeeReviews } from '@/api/index'
import { useToastStore } from '@/stores/ToastStore.js'

const route = useRoute()
const router = useRouter()
const toast = useToastStore()
const selectedReview = ref(null)
const reviews = ref([])
const loading = ref(false)
const syncingFromRoute = ref(false)
const filters = reactive({
  visibility: 'all',
  minRating: 'all',
})

const filteredReviews = computed(() => {
  return reviews.value.filter((review) => {
    if (filters.visibility === 'visible' && !review.is_visible) return false
    if (filters.visibility === 'hidden' && review.is_visible) return false
    if (filters.minRating !== 'all' && Number(review.rating) < Number(filters.minRating)) return false
    return true
  })
})

const reviewedCount = computed(() => reviews.value.length)
const averageRating = computed(() => {
  if (!reviews.value.length) return '0.0'
  const sum = reviews.value.reduce((acc, review) => acc + Number(review.rating || 0), 0)
  return (sum / reviews.value.length).toFixed(1)
})

function syncFiltersFromQuery(query) {
  filters.visibility = typeof query.visibility === 'string' ? query.visibility : 'all'
  filters.minRating = typeof query.minRating === 'string' ? query.minRating : 'all'
}

function buildQueryFromFilters() {
  const query = {}
  if (filters.visibility !== 'all') query.visibility = filters.visibility
  if (filters.minRating !== 'all') query.minRating = filters.minRating
  return query
}

async function loadReviews() {
  loading.value = true
  try {
    const response = await getEmployeeReviews()
    reviews.value = (response.data?.data ?? response.data ?? []).map((review) => ({
      ...review,
      is_visible: Boolean(review.is_visible),
    }))
  } catch {
    toast.showError('Failed to load data.')
  } finally {
    loading.value = false
  }
}

function openReview(review) {
  selectedReview.value = review
}

function closeReview() {
  selectedReview.value = null
}

function formatReviewDate(value) {
  return value ? String(value).slice(0, 10) : ''
}

onMounted(async () => {
  await loadReviews()
})

watch(
  () => route.query,
  (query) => {
    syncingFromRoute.value = true
    syncFiltersFromQuery(query)
    syncingFromRoute.value = false
  },
  { immediate: true },
)

watch(
  filters,
  () => {
    if (syncingFromRoute.value) return
    router.replace({ query: buildQueryFromFilters() })
  },
  { deep: true },
)
</script>
