import {
  deleteShopSpecialDay,
  getShopOpeningHours,
  getShopSpecialDaysByMonth,
  patchShopOpeningHour,
  patchShopSpecialDay,
  postShopOpeningHour,
  postShopSpecialDay,
} from '@/api/index'

export function useShopSchedule() {
  async function fetchSpecialDays(month) {
    const response = await getShopSpecialDaysByMonth(month)
    return response.data.data.map((day) => ({
      id: day.id,
      name: day.name,
      dateISO: day.date,
      openTime: day.open_time,
      closeTime: day.close_time,
    }))
  }

  async function saveSpecialDays(payload, currentSpecialDays) {
    if (!payload.isSpecial) {
      const specialDaysToDelete = currentSpecialDays.filter(
        (specialDay) => payload.days.includes(specialDay.dateISO))

      await Promise.all(specialDaysToDelete.map((specialDay) => deleteShopSpecialDay(specialDay.id)))
      return
    }

    await Promise.all(payload.days.map((dateISO) => {
      const existingSpecialDay = currentSpecialDays.find((specialDay) => specialDay.dateISO === dateISO)
      const isOpen = payload.status === 'open'
      const requestPayload = {
        date: dateISO,
        name: payload.name || '',
        open_time: isOpen ? `${payload.openTime}:00` : null,
        close_time: isOpen ? `${payload.closeTime}:00` : null,
      }

      if (existingSpecialDay) {
        return patchShopSpecialDay(existingSpecialDay.id, requestPayload)
      }

      return postShopSpecialDay(requestPayload)
    }))
  }

  async function fetchOpeningHours() {
    const response = await getShopOpeningHours()
    const days = response.data.data.map((day) => {
      return {
        id: day.id,
        weekday: day.weekday,
        isOpen: day.is_open,
        openTime: day.open_time,
        closeTime: day.close_time,
      }
    })
    return days;
  }

async function saveOpeningHours(data) {
  await Promise.all(
    data.map((day) =>
      patchShopOpeningHour(day.id, {
        open_time: day.isOpen ? `${day.openTime}:00` : null,
        close_time: day.isOpen ? `${day.closeTime}:00` : null,
      })
    )
  )
}
  return {
    fetchSpecialDays,
    saveSpecialDays,
    fetchOpeningHours,
    saveOpeningHours,
  }
}
