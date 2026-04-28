import { ref, watch } from 'vue'

export function useEmployeeScheduleNavigation() {
  const viewMode = ref('daily')
  const cancelledOrderBy = ref('newest')
  const selectedDate = ref(new Date())
  const preserveSelectedDateOnDailySwitch = ref(false)

  watch(viewMode, (mode, previousMode) => {
    if (mode === 'daily') {
      if (previousMode && previousMode !== 'daily' && !preserveSelectedDateOnDailySwitch.value) {
        selectedDate.value = new Date()
      }
      preserveSelectedDateOnDailySwitch.value = false
    }
  })

  function goToday() {
    selectedDate.value = new Date()
  }

  function goPrev() {
    if (viewMode.value === 'monthly') {
      selectedDate.value = new Date(selectedDate.value.getFullYear(), selectedDate.value.getMonth() - 1, 1)
      return
    }

    const next = new Date(selectedDate.value)
    next.setDate(selectedDate.value.getDate() - (viewMode.value === 'weekly' ? 7 : 1))
    selectedDate.value = next
  }

  function goNext() {
    if (viewMode.value === 'monthly') {
      selectedDate.value = new Date(selectedDate.value.getFullYear(), selectedDate.value.getMonth() + 1, 1)
      return
    }

    const next = new Date(selectedDate.value)
    next.setDate(selectedDate.value.getDate() + (viewMode.value === 'weekly' ? 7 : 1))
    selectedDate.value = next
  }

  return {
    cancelledOrderBy,
    goNext,
    goPrev,
    goToday,
    preserveSelectedDateOnDailySwitch,
    selectedDate,
    viewMode,
  }
}