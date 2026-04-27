import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getAdminReviews, getEmployees, patchAdminReviewVisibility } from '@/api/index'

export function useAdminReviews() {
  const route = useRoute()
  const router = useRouter()

  const loading = ref(false)
  const savingReviews = ref(false)
  const reviews = ref([])
  const employees = ref([])
  const syncingFromRoute = ref(false)
  const filters = reactive({
    employeeId: 'all',
    visibility: 'all',
    minRating: 'all',
  })

  const filteredReviews = computed(() => {
    return reviews.value.filter((review) => {
      if (filters.employeeId !== 'all' && String(review.employee?.id ?? '') !== String(filters.employeeId)) return false
      if (filters.visibility === 'visible' && !review.is_visible) return false
      if (filters.visibility === 'hidden' && review.is_visible) return false
      if (filters.minRating !== 'all' && Number(review.rating) < Number(filters.minRating)) return false
      return true
    })
  })

  const employeeOptions = computed(() => employees.value)

  function syncFiltersFromQuery(query) {
    filters.employeeId = typeof query.employeeId === 'string' ? query.employeeId : 'all'
    filters.visibility = typeof query.visibility === 'string' ? query.visibility : 'all'
    filters.minRating = typeof query.minRating === 'string' ? query.minRating : 'all'
  }

  function buildQueryFromFilters() {
    const query = {}

    if (filters.employeeId !== 'all') query.employeeId = filters.employeeId
    if (filters.visibility !== 'all') query.visibility = filters.visibility
    if (filters.minRating !== 'all') query.minRating = filters.minRating

    return query
  }

  async function loadReviews() {
    loading.value = true

    try {
      const response = await getAdminReviews({
        employee_id: filters.employeeId !== 'all' ? filters.employeeId : undefined,
        visibility: filters.visibility !== 'all' ? filters.visibility : undefined,
        min_rating: filters.minRating !== 'all' ? filters.minRating : undefined,
      })

      reviews.value = (response.data?.data ?? response.data ?? []).map((review) => ({
        ...review,
        is_visible: Boolean(review.is_visible),
      }))
    } finally {
      loading.value = false
    }
  }

  async function loadEmployees() {
    const response = await getEmployees()
    employees.value = response.data?.data ?? []
  }

  async function saveChanges(changedReviews = []) {
    savingReviews.value = true

    try {
      await Promise.all(
        changedReviews.map((review) =>
          patchAdminReviewVisibility(review.id, {
            is_visible: review.is_visible,
          }),
        ),
      )
    } finally {
      savingReviews.value = false
    }
  }

  onMounted(async () => {
    await Promise.all([loadReviews(), loadEmployees()]).catch(() => {})
  })

  watch(
    () => route.query,
    (query) => {
      syncingFromRoute.value = true
      syncFiltersFromQuery(query)
      syncingFromRoute.value = false
      loadReviews().catch(() => {})
    },
    { immediate: true },
  )

  watch(
    filters,
    () => {
      if (syncingFromRoute.value) return
      const nextQuery = buildQueryFromFilters()
      router.replace({ query: nextQuery })
      loadReviews().catch(() => {})
    },
    { deep: true },
  )

  return {
    employeeOptions,
    filteredReviews,
    filters,
    loading,
    reviews,
    saveChanges,
    savingReviews,
  }
}
