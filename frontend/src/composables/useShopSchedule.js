import {
  deleteShopSpecialDay,
  getShopOpeningHours,
  getShopSpecialDayByDate,
  getShopSpecialDaysByMonth,
  patchShopOpeningHour,
  patchShopSpecialDay,
  postShopSpecialDay,
} from '@/api/index'
import { formatYearMonth } from '@/utils/date'

export function useShopSchedule() {
  function normalizeTimeForInput(time) {
    return time ? time.slice(0, 5) : null
  }

  function normalizeSpecialDay(day) {
    return {
      id: day.id,
      name: day.name,
      dateISO: day.date,
      openTime: normalizeTimeForInput(day.open_time),
      closeTime: normalizeTimeForInput(day.close_time),
    }
  }

  async function fetchSpecialDays(month) {
    const response = await getShopSpecialDaysByMonth(month)
    return response.data.data.map((day) => normalizeSpecialDay(day))
  }

  async function fetchSpecialDayByDate(date) {
    const response = await getShopSpecialDayByDate(date)
    const day = response.data?.data

    return day ? normalizeSpecialDay(day) : null
  }

  async function saveSpecialDays(payload, currentSpecialDays) {
    const resolveSpecialDay = async (dateISO) => {
      const existingSpecialDay = currentSpecialDays.find((specialDay) => specialDay.dateISO === dateISO)

      if (existingSpecialDay) {
        return existingSpecialDay
      }

      return fetchSpecialDayByDate(dateISO)
    }

    if (!payload.isSpecial) {
      const specialDaysToDelete = (await Promise.all(
        payload.days.map((dateISO) => resolveSpecialDay(dateISO)),
      )).filter(Boolean)

      await Promise.all(specialDaysToDelete.map((specialDay) => deleteShopSpecialDay(specialDay.id)))
      return
    }

    await Promise.all(payload.days.map(async (dateISO) => {
      const existingSpecialDay = await resolveSpecialDay(dateISO)
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
        openTime: normalizeTimeForInput(day.open_time),
        closeTime: normalizeTimeForInput(day.close_time),
      }
    })
    return days.sort((a, b) => a.weekday - b.weekday)
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

  function parseMonthQuery(value) {
    if (typeof value !== 'string') return null
    if (value.length !== 7) return null

    const parts = value.split('-')
    if (parts.length !== 2) return null

    const [yearRaw, monthRaw] = parts
    if (yearRaw.length !== 4 || monthRaw.length !== 2) return null

    const year = Number(yearRaw)
    const month = Number(monthRaw)

    if (!Number.isInteger(year) || !Number.isInteger(month)) {
      return null
    }

    if (month < 1 || month > 12) {
      return null
    }

    return new Date(year, month - 1, 1)
  }

  function syncMonthFromQuery(query, fallbackDate = new Date()) {
    return parseMonthQuery(query?.month) ?? fallbackDate
  }

  function buildMonthQuery(displayMonth, defaultMonthKey = formatYearMonth(new Date())) {
    const month = formatYearMonth(displayMonth)
    return month === defaultMonthKey ? {} : { month }
  }

  return {
    buildMonthQuery,
    fetchSpecialDayByDate,
    fetchSpecialDays,
    syncMonthFromQuery,
    saveSpecialDays,
    fetchOpeningHours,
    saveOpeningHours,
  }
}
