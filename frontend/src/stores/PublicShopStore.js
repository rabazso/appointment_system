import { defineStore } from 'pinia'
import { getShopInformation, getShopOpeningHours } from '@/api'

const WEEKDAY_LABELS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']

function normalizeTime(time) {
  return time ? String(time).slice(0, 5) : null
}

function normalizeOpeningHours(rows = []) {
  const byWeekday = new Map(
    rows.map((row) => [
      Number(row.weekday),
      {
        weekday: Number(row.weekday),
        label: WEEKDAY_LABELS[Number(row.weekday)] ?? `Day ${row.weekday}`,
        isOpen: Boolean(row.is_open),
        openTime: normalizeTime(row.open_time),
        closeTime: normalizeTime(row.close_time),
      },
    ]),
  )

  return WEEKDAY_LABELS.map((label, weekday) => {
    return byWeekday.get(weekday) ?? {
      weekday,
      label,
      isOpen: false,
      openTime: null,
      closeTime: null,
    }
  })
}

export const usePublicShopStore = defineStore('public-shop', {
  state: () => ({
    contact: {
      phone: '',
      email: '',
      address: '',
      links: [],
    },
    openingHours: normalizeOpeningHours([]),
    isLoading: false,
    isLoaded: false,
  }),
  actions: {
    async fetchPublicShopData({ force = false } = {}) {
      if (this.isLoading) return
      if (this.isLoaded && !force) return

      this.isLoading = true

      try {
        const [shopInformationResponse, openingHoursResponse] = await Promise.all([
          getShopInformation(),
          getShopOpeningHours(),
        ])

        const shopInformation = shopInformationResponse?.data?.data ?? shopInformationResponse?.data ?? {}
        const openingHoursRows = openingHoursResponse?.data?.data ?? openingHoursResponse?.data ?? []

        this.contact = {
          phone: shopInformation.phone ?? '',
          email: shopInformation.email ?? '',
          address: shopInformation.address ?? '',
          links: Array.isArray(shopInformation.links) ? shopInformation.links : [],
        }
        this.openingHours = normalizeOpeningHours(openingHoursRows)
        this.isLoaded = true
      } catch (error) {
        // Keep safe defaults when public shop data is unavailable.
      } finally {
        this.isLoading = false
      }
    },
  },
})
