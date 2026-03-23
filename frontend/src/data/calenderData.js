export const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

export const INITIAL_WEEKLY_SCHEDULE = [
  { day: 'Sunday', isOpen: false, openTime: '08:00', closeTime: '16:00' },
  { day: 'Monday', isOpen: true, openTime: '08:00', closeTime: '16:00' },
  { day: 'Tuesday', isOpen: true, openTime: '08:00', closeTime: '16:00' },
  { day: 'Wednesday', isOpen: true, openTime: '08:00', closeTime: '16:00' },
  { day: 'Thursday', isOpen: true, openTime: '08:00', closeTime: '16:00' },
  { day: 'Friday', isOpen: true, openTime: '08:00', closeTime: '16:00' },
  { day: 'Saturday', isOpen: false, openTime: '08:00', closeTime: '16:00' }
]

export const INITIAL_HOLIDAYS = [
  { id: '1', dateISO: '2026-03-05', status: 'open', openTime: '08:00', closeTime: '20:00' },
  { id: '2', dateISO: '2026-03-07', status: 'closed' },
  { id: '4', dateISO: '2026-03-12', status: 'open', openTime: '10:00', closeTime: '18:00' },
  { id: '5', dateISO: '2026-03-19', status: 'closed' },
  { id: '6', dateISO: '2026-03-27', status: 'open', openTime: '08:00', closeTime: '12:00' },
]
