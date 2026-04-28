<template>
  <div class="flex h-dvh overflow-hidden bg-slate-100">
    <Sidebar :isOpen="sidebarOpen" @close="sidebarOpen = false" />

    <main class="flex h-full w-full flex-1 flex-col overflow-y-auto p-8">
      <Header
        title="Reviews"
        description="Manage which client reviews are visible on your employee profile."
        @menu-click="sidebarOpen = true"
      />

      <div class="flex min-h-0 flex-1 flex-col gap-4">
        <section class="flex min-h-0 flex-1 flex-col rounded-2xl bg-white p-6 shadow-sm md:p-8">
          <div v-if="loading" class="text-sm text-slate-500">
            Loading reviews...
          </div>

          <div v-else class="flex min-h-0 flex-1 flex-col">
            <div class="mb-4 grid gap-3 xl:grid-cols-[max-content_max-content_max-content] xl:justify-start">
              <div>
                <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Rating</span>
                <label class="block">
                  <Select v-model="filters.minRating">
                    <SelectTrigger class="w-full">
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
                </label>
              </div>

              <div>
                <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Visibility</span>
                <label class="block">
                  <Select v-model="filters.visibility">
                    <SelectTrigger class="w-full">
                      <SelectValue placeholder="All" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">All</SelectItem>
                      <SelectItem value="visible">Visible</SelectItem>
                      <SelectItem value="hidden">Hidden</SelectItem>
                    </SelectContent>
                  </Select>
                </label>
              </div>

              <div>
                <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Employee</span>
                <Select v-model="filters.employeeId">
                  <SelectTrigger class="w-full">
                    <SelectValue placeholder="All employees" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All employees</SelectItem>
                    <SelectItem
                    v-for="employee in employeeOptions"
                    :key="employee.id"
                    :value="String(employee.id)"
                  >
                    {{ employee.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div class="min-h-0 flex-1 overflow-auto pr-1">
              <div v-if="filteredReviews.length" class="grid gap-3 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
                <ReviewCard
                  v-for="review in filteredReviews"
                  :key="review.id"
                  :review="review"
                  :show-visibility-toggle="true"
                  @open="openReview"
                  @toggle-visibility="toggleVisibility"
                />
              </div>

              <div
                v-else
                class="flex h-full flex-1 items-center justify-center rounded-2xl text-sm"
              >
                No reviews yet
              </div>
            </div>

            <div v-if="isDirty" class="mt-4 shrink-0 -mx-6 -mb-6 rounded-b-2xl border-t border-black/10 bg-white px-6 py-4 md:-mx-8">
              <div class="flex justify-end gap-3">
                <button
                  type="button"
                  :disabled="savingReviews"
                  class="rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                  :class="{ 'cursor-not-allowed opacity-60': savingReviews }"
                  @click="onResetChanges"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  :disabled="savingReviews"
                  class="rounded-xl bg-secondary px-4 py-2 text-sm font-semibold text-black transition hover:bg-[#ffab5c]"
                  :class="{ 'cursor-not-allowed opacity-60': savingReviews }"
                  @click="onSaveChanges"
                >
                  {{ savingReviews ? 'Saving...' : 'Save' }}
                </button>
              </div>
            </div>
          </div>
        </section>
      </div>

      <ReviewDetailsModal
        v-if="selectedReview"
        :review="selectedReview"
        :format-review-date="formatReviewDate"
        @close="closeReview"
        @toggle-visibility="toggleVisibility"
      />
    </main>
  </div>
</template>


<script setup>
import { computed, ref, watch } from 'vue'
import Header from '@/components/admin/Header.vue'
import Sidebar from '@/components/admin/Sidebar.vue'
import ReviewCard from '@/components/ReviewCard.vue'
import ReviewDetailsModal from '@/components/ReviewDetailsModal.vue'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { useAdminReviews } from '@/composables/useAdminReviews'
import { useToastStore } from '@/stores/ToastStore.js'

const sidebarOpen = ref(false)
const selectedReview = ref(null)
const toast = useToastStore()

const {
  employeeOptions,
  filteredReviews,
  filters,
  loading,
  reviews,
  saveChanges,
  savingReviews,
} = useAdminReviews()

const originalReviews = ref([])

const dirtyReviews = computed(() =>
  reviews.value.filter((review) => review._dirty),
)

const isDirty = computed(() => dirtyReviews.value.length > 0)

function initializeOriginalReviews() {
  originalReviews.value = reviews.value.map((review) => ({
    id: review.id,
    is_visible: Boolean(review.is_visible),
  }))
}

function getOriginalVisibility(reviewId) {
  return originalReviews.value.find((review) => review.id === reviewId)?.is_visible
}

function openReview(review) {
  selectedReview.value = review
}

function closeReview() {
  selectedReview.value = null
}

function syncSelectedReview(reviewId) {
  if (selectedReview.value?.id !== reviewId) return

  selectedReview.value = reviews.value.find((review) => review.id === reviewId) ?? null
}

function toggleVisibility(reviewId) {
  reviews.value = reviews.value.map((review) => {
    if (review.id !== reviewId) return review

    const isVisible = !review.is_visible
    const originalVisible = getOriginalVisibility(review.id)

    return {
      ...review,
      is_visible: isVisible,
      _dirty: originalVisible !== isVisible,
    }
  })

  syncSelectedReview(reviewId)
}

function onResetChanges() {
  reviews.value = reviews.value.map((review) => ({
    ...review,
    is_visible: getOriginalVisibility(review.id) ?? review.is_visible,
    _dirty: false,
  }))

  if (selectedReview.value) {
    syncSelectedReview(selectedReview.value.id)
  }
}

async function onSaveChanges() {
  try {
    await saveChanges(dirtyReviews.value)

    reviews.value = reviews.value.map((review) => ({
      ...review,
      _dirty: false,
    }))

    initializeOriginalReviews()

    if (selectedReview.value) {
      syncSelectedReview(selectedReview.value.id)
    }
    toast.show('Changes saved successfully.')
  } catch (error) {
    toast.showError('Failed to save changes.')
  }
}

watch(
  reviews,
  (nextReviews) => {
    if (!nextReviews.length) return
    if (originalReviews.value.length) return
    initializeOriginalReviews()
  },
  { immediate: true },
)

function formatReviewDate(value) {
  return value ? String(value).slice(0, 10) : ''
}

</script>
