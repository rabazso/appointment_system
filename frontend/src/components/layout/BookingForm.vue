<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { Label } from '@/components/ui/label'
import { Scissors } from 'lucide-vue-next'
import { Calendar } from '@/components/ui/calendar'
import { getLocalTimeZone, parseDate, today } from '@internationalized/date'
import { Button } from '@/components/ui/button'
import { 
  getBookingDays,
  getBookingEmployees,
  getBookingSummary,
  getBookingServices,
  getBookingSlots,
  postAppointment
} from '@/api/index'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/AuthStore.js'
import { useToastStore } from '@/stores/ToastStore.js'

const router = useRouter()
const toast = useToastStore()

const selectedServices = ref([])
const selectedBarber = ref('')
const selectedDate = ref(null)
const calendarDate = ref(today(getLocalTimeZone()))
const selectedTime = ref('')
const userData = ref({ name: '', email: ''})
const customerNote = ref('')
const bookingErrorMessage = ref('')

const openSection = ref('service')

const services = ref([])
const barbers = ref([])
const bookableDays = ref([])
const bookableDaysLoaded = ref(false)
const timeSlots = ref([])
const bookingSummary = ref({
  services: [],
  total_duration: 0,
  total_price: 0,
})
const bookingSummaryLoaded = ref(false)
const hasContinuedToBarber = ref(false)
const needsBarberRefresh = ref(false)

const barberRef = ref(null)
const dateTimeRef = ref(null)
const userDataRef = ref(null)

const store = useAuthStore()
const isAuthenticated = computed(() => store.isCustomerLoggedIn)
const pad = (n) => n.toString().padStart(2,'0')
const serviceIds = computed(() => selectedServices.value.map((serviceId) => Number(serviceId)))
const guestName = computed(() => userData.value.name.trim())
const guestEmail = computed(() => userData.value.email.trim())
const guestDetailsReady = computed(() => guestName.value !== '' && guestEmail.value !== '')
const selectedServiceIdsSet = computed(() => new Set(serviceIds.value))
const selectedServiceObjs = computed(() =>
  services.value.filter((service) => selectedServiceIdsSet.value.has(Number(service.id))),
)
const selectedServiceNames = computed(() => selectedServiceObjs.value.map((service) => service.name).join(', '))
const selectedBarberObj = computed(() =>
  barbers.value.find((barber) => Number(barber.id) === Number(selectedBarber.value)),
)
const bookingTotalDuration = computed(() => Number(bookingSummary.value.total_duration || 0))
const bookingTotalPrice = computed(() => Number(bookingSummary.value.total_price || 0))
const bookableDateSet = computed(() => new Set(
  bookableDays.value
    .filter((day) => day.is_bookable)
    .map((day) => day.date),
))
const bookableDaysByDate = computed(() =>
  bookableDays.value.reduce((accumulator, day) => {
    if (day?.date) {
      accumulator[day.date] = day
    }
    return accumulator
  }, {}),
)
const calendarDayStatuses = computed(() =>
  bookableDays.value.reduce((accumulator, day) => {
    if (day?.date && day?.is_bookable) {
      const status = occupancyStatus(day)
      if (status) {
        accumulator[day.date] = status
      }
    }
    return accumulator
  }, {}),
)
const selectedDayOccupancy = computed(() => {
  if (!selectedDate.value) return null

  const day = bookableDaysByDate.value[dateToString(selectedDate.value)]
  if (!day) return null

  return {
    status: occupancyStatus(day),
    percent: normalizeOccupancyPercent(day.occupancy_percent),
    isBookable: Boolean(day.is_bookable),
  }
})
const selectedDayOccupancyLabel = computed(() => {
  const occupancy = selectedDayOccupancy.value
  if (!occupancy) return ''

  if (!occupancy.isBookable) {
    return 'No availability on this day.'
  }

  if (occupancy.status === 'limited') {
    return `${occupancy.percent}% booked - very limited availability.`
  }

  if (occupancy.status === 'busy') {
    return `${occupancy.percent}% booked - getting busy.`
  }

  return `${occupancy.percent}% booked - plenty of availability.`
})
const selectedAppointmentStart = computed(() => {
  if (!selectedDate.value || !selectedTime.value) return ''

  return `${dateToString(selectedDate.value)} ${selectedTime.value}`
})

onMounted(async () => {
  try {
    const res = await getBookingServices()
    services.value = apiCollection(res)
  } catch (error) {
    bookingErrorMessage.value = extractBookingError(error, 'Failed to load services.')
    toast.showError('Failed to load services.')
  }
})

function resetAfterServiceChange() {
  const previouslyLoadedBarbers = hasContinuedToBarber.value

  selectedBarber.value = ''
  selectedDate.value = null
  selectedTime.value = ''
  barbers.value = []
  bookableDays.value = []
  bookableDaysLoaded.value = false
  timeSlots.value = []
  resetBookingSummary()
  needsBarberRefresh.value = previouslyLoadedBarbers
}

function toggleService(serviceId) {
  const id = Number(serviceId)
  const service = services.value.find((item) => Number(item.id) === id)

  if (service && !isServiceSelectable(service)) {
    return
  }

  if (selectedServiceIdsSet.value.has(id)) {
    selectedServices.value = selectedServices.value.filter((selectedId) => selectedId !== id)
  } else {
    selectedServices.value = [...selectedServices.value, id]
  }

  resetAfterServiceChange()
}

async function loadBarbersForSelectedServices({ openAfterLoad = false } = {}) {
  if (serviceIds.value.length === 0) return

  bookingErrorMessage.value = ''

  try {
    const res = await getBookingEmployees(serviceIds.value)
    barbers.value = apiCollection(res)
      .filter((row) => row.is_valid !== false && row.employee)
      .map((row) => ({
        ...row.employee,
        valid_from: row.valid_from,
        valid_to: row.valid_to,
      }))

    hasContinuedToBarber.value = true
    needsBarberRefresh.value = false

    if (openAfterLoad) {
      openSection.value = 'barber'
      await nextTick()
      barberRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
  } catch (error) {
    bookingErrorMessage.value = extractBookingError(error, 'Failed to load barbers.')
    toast.showError('Failed to load barbers.')
  }
}

async function continueToBarber() {
  if (serviceIds.value.length === 0) return

  resetAfterServiceChange()
  await loadBarbersForSelectedServices({ openAfterLoad: true })
}

watch(openSection, async (section) => {
  if (section !== 'barber') return

  if (serviceIds.value.length === 0) {
    openSection.value = 'service'
    return
  }

  if (!hasContinuedToBarber.value) {
    openSection.value = 'service'
    return
  }

  if (needsBarberRefresh.value) {
    await loadBarbersForSelectedServices()
  }
})

watch(selectedBarber, async (barberId) => {
  if (!barberId) return
  selectedTime.value = ''
  resetBookingSummary()
  bookableDaysLoaded.value = false
  const todayDate = today(getLocalTimeZone())
  calendarDate.value = todayDate
  selectedDate.value = todayDate

  let searchMonth = todayDate
  let firstBookableDay = null

  for (let attempt = 0; attempt < 2; attempt += 1) {
    await loadBookableDays(searchMonth)
    firstBookableDay = bookableDays.value.find((day) => day.is_bookable)

    if (firstBookableDay) {
      break
    }

    searchMonth = searchMonth.add({ months: 1 })
  }

  if (firstBookableDay) {
    const firstBookableDate = parseDate(firstBookableDay.date)
    selectedDate.value = firstBookableDate
    calendarDate.value = firstBookableDate
  }

  await loadTimeSlots()

  const todayIso = dateToString(todayDate)
  if (firstBookableDay && firstBookableDay.date !== todayIso && timeSlots.value.length > 0) {
    selectedTime.value = timeSlots.value[0]
  }

  openSection.value = 'datetime'
  await nextTick()
  dateTimeRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
})

watch(selectedDate, async () => {
  if (serviceIds.value.length === 0 || !selectedBarber.value) return
  selectedTime.value = ''
  resetBookingSummary()
  await loadTimeSlots()
})

watch(selectedTime, async (time) => {
  if (!time) {
    resetBookingSummary()
    return
  }

  await loadBookingSummary()
})

watch(calendarDate, async (date) => {
  if (serviceIds.value.length === 0 || !selectedBarber.value) return
  await loadBookableDays(date)
})

async function handleTimeSelection(time) {
  selectedTime.value = time
  if (!isAuthenticated.value) {
    openSection.value = 'userdata'
    await nextTick()
    userDataRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

async function loadTimeSlots() {
  if (!selectedDate.value || !selectedBarber.value) return

  const isoDate = dateToString(selectedDate.value)
  if (bookableDaysLoaded.value && !bookableDateSet.value.has(isoDate)) {
    timeSlots.value = []
    return
  }

  try {
    const res = await getBookingSlots(serviceIds.value, Number(selectedBarber.value), isoDate)
    const day = apiCollection(res).find((item) => item.date === isoDate)
    timeSlots.value = day?.slots || []
  } catch (error) {
    timeSlots.value = []
    bookingErrorMessage.value = extractBookingError(error, 'Failed to load time slots.')
    toast.showError('Failed to load time slots.')
  }
}

const isReadyForUser = () => serviceIds.value.length > 0 && selectedBarber.value && selectedTime.value && selectedDate.value
const canSubmitBooking = computed(() => isReadyForUser() && (isAuthenticated.value || guestDetailsReady.value))
const shouldShowBookingSummary = computed(() => isReadyForUser())
const shouldShowConfirmButton = computed(() => isReadyForUser())

function apiCollection(response) {
  return response?.data?.data || response?.data || []
}

function availabilityToDate(value) {
  if (!value) return null

  const raw = String(value)
  const parsed = new Date(raw)

  if (Number.isNaN(parsed.getTime())) {
    return null
  }

  return parsed
}

function normalizeOccupancyPercent(value) {
  const normalized = Number(value)

  if (!Number.isFinite(normalized)) {
    return 0
  }

  return Math.min(100, Math.max(0, Math.round(normalized)))
}

function occupancyStatus(day) {
  if (!day?.is_bookable) {
    return ''
  }

  const occupancy = normalizeOccupancyPercent(day?.occupancy_percent)

  if (occupancy >= 75) {
    return 'limited'
  }

  if (occupancy >= 50) {
    return 'busy'
  }

  return 'plenty'
}

function isServiceSelectable(service) {
  const validFromDate = availabilityToDate(service?.valid_from)
  if (!validFromDate) return true

  return new Date() >= validFromDate
}

function normalizeAvailabilityDate(value) {
  if (!value) return null

  const raw = String(value)
  const datePart = raw.match(/^(\d{4}-\d{2}-\d{2})/)
  const timePart = raw.match(/(\d{2}:\d{2})(?::\d{2})?/)

  if (datePart?.[1]) {
    if (timePart?.[1]) {
      return `${datePart[1]} ${timePart[1]}`
    }

    return datePart[1]
  }

  const parsed = availabilityToDate(raw)
  if (!parsed) {
    return raw
  }

  return `${parsed.getFullYear()}-${pad(parsed.getMonth() + 1)}-${pad(parsed.getDate())} ${pad(parsed.getHours())}:${pad(parsed.getMinutes())}`
}

function availabilityLabel(item) {
  const validFrom = normalizeAvailabilityDate(item?.valid_from)
  const validTo = normalizeAvailabilityDate(item?.valid_to)

  if (validFrom && validTo) {
    return `Available from ${validFrom} until ${validTo}.`
  }

  if (validFrom) {
    return `Available from ${validFrom}.`
  }

  if (validTo) {
    return `Available until ${validTo}.`
  }

  return ''
}

function dateToString(date) {
  if (!date) return ''
  return `${date.year}-${pad(date.month)}-${pad(date.day)}`
}

function isDateUnavailable(date) {
  if (!selectedBarber.value || !bookableDaysLoaded.value) {
    return false
  }

  return !bookableDateSet.value.has(dateToString(date))
}

function handleUnavailableDateClick() {
  selectedTime.value = ''
  resetBookingSummary()
}

function monthValueFor(date) {
  const targetDate = date || today(getLocalTimeZone())

  return `${targetDate.year}-${pad(targetDate.month)}`
}

async function loadBookableDays(startDate) {
  if (serviceIds.value.length === 0 || !selectedBarber.value) {
    return
  }

  try {
    const res = await getBookingDays(serviceIds.value, Number(selectedBarber.value), monthValueFor(startDate))
    bookableDays.value = apiCollection(res)
    bookableDaysLoaded.value = true
  } catch (error) {
    bookableDays.value = []
    bookableDaysLoaded.value = true
    bookingErrorMessage.value = extractBookingError(error, 'Failed to load dates.')
    toast.showError('Failed to load dates.')
  }
}

function resetBookingSummary() {
  bookingSummary.value = {
    services: [],
    total_duration: 0,
    total_price: 0,
  }
  bookingSummaryLoaded.value = false
}

async function loadBookingSummary() {
  if (serviceIds.value.length === 0 || !selectedBarber.value || !selectedAppointmentStart.value) {
    resetBookingSummary()
    return
  }

  try {
    const res = await getBookingSummary(
      serviceIds.value,
      Number(selectedBarber.value),
      selectedAppointmentStart.value
    )

    bookingSummary.value = res?.data?.data || {
      services: [],
      total_duration: 0,
      total_price: 0,
    }
    bookingSummaryLoaded.value = true
  } catch (error) {
    resetBookingSummary()
    bookingErrorMessage.value = extractBookingError(error, 'Failed to load summary.')
    toast.showError('Failed to load summary.')
  }
}

const handleSubmit = async () => {
  try {
    if (!isReadyForUser()) return
    bookingErrorMessage.value = ''

    if (!bookingSummaryLoaded.value) {
      await loadBookingSummary()
    }

    if (!isAuthenticated.value && !guestDetailsReady.value) {
      bookingErrorMessage.value = 'Please enter your name and email.'
      await openUserDataSection()
      return
    }

    const dateStr = dateToString(selectedDate.value)
    const appointmentStart = `${dateStr} ${selectedTime.value}`

    const appointmentPayload = {
      service_ids: serviceIds.value,
      employee_id: Number(selectedBarber.value),
      appointment_start: appointmentStart,
    }
    const trimmedCustomerNote = customerNote.value.trim()

    if (trimmedCustomerNote !== '') {
      appointmentPayload.customer_note = trimmedCustomerNote
    }

    if (!isAuthenticated.value){
      appointmentPayload.guest_name = guestName.value
      appointmentPayload.guest_email = guestEmail.value
    }

    await postAppointment(appointmentPayload)

    const bookingData = {
      serviceName: selectedServiceNames.value,
      barberName: selectedBarberObj.value?.name,
      date: dateStr,
      time: selectedTime.value,
      duration: bookingTotalDuration.value,
      price: bookingTotalPrice.value,
      note: trimmedCustomerNote || null,
    }

    router.push({ 
      path: '/confirmation-pending', 
      state: { booking: bookingData } 
    })

  } catch (err) {
    const wasAuthenticated = isAuthenticated.value

    bookingErrorMessage.value = extractBookingError(err, 'Failed to book appointment.')
    toast.showError('Failed to book appointment.')

    if (err?.response?.status === 401) {
      store.clearSession('customer')
      bookingErrorMessage.value = 'Your login expired. Please log in again.'
      return
    }

    if (!wasAuthenticated && hasGuestDetailsError(err)) {
      await openUserDataSection()
    }
  }
}

async function openUserDataSection() {
  openSection.value = 'userdata'
  await nextTick()
  userDataRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function extractBookingError(error, fallbackMessage) {
  return (
    error?.response?.data?.errors?.name?.[0] ||
    error?.response?.data?.errors?.guest_name?.[0] ||
    error?.response?.data?.errors?.email?.[0] ||
    error?.response?.data?.errors?.guest_email?.[0] ||
    error?.response?.data?.errors?.customer_id?.[0] ||
    error?.response?.data?.errors?.appointment_start?.[0] ||
    error?.response?.data?.message ||
    fallbackMessage
  )
}

function hasGuestDetailsError(error) {
  const fieldErrors = error?.response?.data?.errors || {}

  return Boolean(fieldErrors.name || fieldErrors.guest_name || fieldErrors.email || fieldErrors.guest_email || fieldErrors.customer_id)
}

watch(() => userData.value.name, () => {
  bookingErrorMessage.value = ''
})

watch(() => userData.value.email, () => {
  bookingErrorMessage.value = ''
})

watch([selectedServices, selectedBarber, selectedDate, selectedTime], () => {
  bookingErrorMessage.value = ''
})

watch(isAuthenticated, (loggedIn) => {
  if (loggedIn) {
    bookingErrorMessage.value = ''
  }
})
</script>

<template>
<form @submit.prevent="handleSubmit" class="space-y-6">

  <Accordion type="single" collapsible v-model="openSection" class="space-y-4">
    <AccordionItem value="service">
      <Card>
        <AccordionTrigger>
          <div class="flex items-center gap-2">
            <div class="w-10 h-10 flex items-center justify-center rounded-full bg-primary text-white font-semibold">1</div>
            <div>
              <CardTitle>Select Your Service</CardTitle>
              <CardDescription>Choose the service you need</CardDescription>
            </div>
          </div>
        </AccordionTrigger>
        <AccordionContent>
          <CardContent>
            <div class="grid gap-3 md:grid-cols-2">
              <Label v-for="service in services" :key="service.id"
                     class="flex gap-3 p-4 rounded-lg border-2"
                     :disabled="!isServiceSelectable(service)"
                     :class="selectedServiceIdsSet.has(Number(service.id)) ? 'border-primary bg-primary/5' : 'border-border bg-background'">
                <input
                  type="checkbox"
                  class="mt-1 h-4 w-4 accent-primary"
                  :disabled="!isServiceSelectable(service)"
                  :checked="selectedServiceIdsSet.has(Number(service.id))"
                  @change="toggleService(service.id)"
                />
                <div class="flex-1" :class="!isServiceSelectable(service) ? 'opacity-60' : ''">
                  <div class="flex items-center gap-2">
                    <Scissors class="size-4" />
                    <p class="font-semibold">{{ service.name }}</p>
                  </div>
                  <p class="text-sm text-muted-foreground">{{ service.description || 'Available for booking' }}</p>
                  <p v-if="availabilityLabel(service)" class="mt-1 text-xs text-muted-foreground">
                    {{ availabilityLabel(service) }}
                  </p>
                </div>
              </Label>
              <p v-if="services.length === 0" class="text-sm text-muted-foreground">
                No services are available right now.
              </p>
            </div>
            <div class="mt-4 flex justify-end">
              <Button type="button" :disabled="serviceIds.length === 0" @click="continueToBarber">
                Continue
              </Button>
            </div>
          </CardContent>
        </AccordionContent>
      </Card>
    </AccordionItem>

    <AccordionItem value="barber" :disabled="serviceIds.length === 0 || !hasContinuedToBarber">
        <div ref="barberRef">
      <Card>
        <AccordionTrigger>
          <div class="flex items-center gap-2">
            <div class="w-10 h-10 flex items-center justify-center rounded-full bg-primary text-white font-semibold">2</div>
            <div>
              <CardTitle>Select Your Barber</CardTitle>
              <CardDescription>Select your preferred barber</CardDescription>
            </div>
          </div>
        </AccordionTrigger>
        <AccordionContent>
          <CardContent>
            <RadioGroup v-model="selectedBarber">
              <div class="grid gap-3 md:grid-cols-2">
                <Label v-for="barber in barbers" :key="barber.id"
                       class="flex items-center gap-3 p-4 rounded-lg border-2"
                       :class="Number(selectedBarber) === Number(barber.id) ? 'border-primary bg-primary/5' : 'border-border bg-background'">
                  <RadioGroupItem :value="barber.id" class="mt-1" />
                  <div class="shrink-0">
                    <img
                      v-if="barber.profile_image?.preview_url"
                      :src="barber.profile_image.preview_url"
                      :alt="`${barber.name} preview`"
                      class="h-12 w-12 rounded-full object-cover"
                    />
                    <div
                      v-else
                      class="flex h-12 w-12 items-center justify-center rounded-full bg-muted text-sm font-semibold text-muted-foreground"
                    >
                      {{ barber.name?.charAt(0) || '?' }}
                    </div>
                  </div>
                  <div class="flex-1">
                    <p class="font-semibold">{{ barber.name }}</p>
                    <p v-if="barber.total_duration" class="text-xs text-muted-foreground">
                      {{ barber.total_duration }} min
                    </p>
                    <p v-if="availabilityLabel(barber)" class="text-xs text-muted-foreground">
                      {{ availabilityLabel(barber) }}
                    </p>
                  </div>
                </Label>
                <p v-if="barbers.length === 0" class="text-sm text-muted-foreground">
                  No barber can take this service right now.
                </p>
              </div>
            </RadioGroup>
          </CardContent>
        </AccordionContent>
      </Card>
      </div>
    </AccordionItem>

    <AccordionItem value="datetime" ref="dateRef" :disabled="!selectedBarber">
        <div ref="dateTimeRef">
      <Card>
        <AccordionTrigger>
          <div class="flex items-center gap-2">
            <div class="w-10 h-10 flex items-center justify-center rounded-full bg-primary text-white font-semibold">3</div>
            <div>
              <CardTitle>Pick Date and Time</CardTitle>
              <CardDescription>Select your appointment date and time</CardDescription>
            </div>
          </div>
        </AccordionTrigger>
        <AccordionContent>
          <CardContent>
            <div class="grid md:grid-cols-2 gap-4">
              <div class="w-fit">
                <Label class="block mb-2 font-semibold">Select Your Date</Label>
                <Calendar v-model="selectedDate"
                          v-model:placeholder="calendarDate"
                          class="rounded-md border border-border shadow-sm"
                          layout="month-and-year"
                          :min-value="today(getLocalTimeZone())"
                          :max-value="today(getLocalTimeZone()).add({weeks:4})"
                          :day-statuses="calendarDayStatuses"
                          :on-unavailable-date-click="handleUnavailableDateClick"
                          :is-date-unavailable="isDateUnavailable"/>
                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-xs text-muted-foreground">
                  <div class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-emerald-500" />
                    <span>Plenty of availability</span>
                  </div>
                  <div class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-amber-400" />
                    <span>Getting busy</span>
                  </div>
                  <div class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-rose-500" />
                    <span>Very limited</span>
                  </div>
                </div>
              </div>
              <div>
                <Label class="block mb-2 font-semibold">Choose a Time Slot</Label>
                <p v-if="selectedDayOccupancyLabel" class="mb-2 text-xs text-muted-foreground">
                  {{ selectedDayOccupancyLabel }}
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                  <Button v-for="time in timeSlots" :key="time"
                          variant="outline"
                          type="button" 
                          :class="selectedTime === time ? 'border-accent border-2 bg-primary/10' : 'bg-background'"
                          @click="handleTimeSelection(time)">
                    {{ time }}
                  </Button>
                  <p v-if="timeSlots.length === 0 && selectedDate" class="text-sm text-muted-foreground col-span-full mt-2">
                     No slots available.
                  </p>
                </div>
              </div>
            </div>
          </CardContent>
        </AccordionContent>
      </Card>
      </div>
    </AccordionItem>

    <AccordionItem value="userdata" :disabled="!selectedTime" v-if="!isAuthenticated">
        <div ref="userDataRef">
      <Card>
        <AccordionTrigger>
          <div class="flex items-center gap-2">
            <div class="w-10 h-10 flex items-center justify-center rounded-full bg-primary text-white font-semibold">4</div>
            <div>
              <CardTitle>Your Details</CardTitle>
              <CardDescription>Enter your information to complete the booking</CardDescription>
            </div>
          </div>
        </AccordionTrigger>
        <AccordionContent>
          <CardContent class="space-y-4">
            <div v-if="bookingErrorMessage" class="mb-4 py-2 px-3 rounded text-white bg-red-500">
              {{ bookingErrorMessage }}
            </div>
            <Label class="block font-semibold">Name</Label>
            <input v-model="userData.name" type="text" placeholder="Your full name" required class="w-full border border-border rounded-md p-2 bg-background text-foreground"/>
            <Label class="block font-semibold">Email</Label>
            <input v-model="userData.email" type="email" placeholder="you@example.com" required class="w-full border border-border rounded-md p-2 bg-background text-foreground"/>
          </CardContent>
        </AccordionContent>
      </Card>
      </div>
    </AccordionItem>
  </Accordion>

  <div v-if="bookingErrorMessage && isAuthenticated" class="mb-2 py-2 px-3 rounded text-white bg-red-500">
    {{ bookingErrorMessage }}
  </div>

  <Card v-if="shouldShowBookingSummary" class="border-accent/30 bg-accent/10" >
    <CardHeader>
      <CardTitle>Booking Summary</CardTitle>
    </CardHeader>
    <CardContent class="space-y-2">
      <div class="flex justify-between">
        <p class="text-muted-foreground">Service:</p>
        <p class="font-semibold text-right">{{ selectedServiceNames }}</p>
      </div>
      <div class="flex justify-between">
        <p class="text-muted-foreground">Barber:</p>
        <p class="font-semibold">{{ selectedBarberObj?.name }}</p>
      </div>
      <div class="flex justify-between">
        <p class="text-muted-foreground">Date:</p>
        <p class="font-semibold" v-if="selectedDate">
          {{ pad(selectedDate.year) }}-{{ pad(selectedDate.month) }}-{{ pad(selectedDate.day) }}
        </p>
      </div>
      <div class="flex justify-between">
        <p class="text-muted-foreground">Time:</p>
        <p class="font-semibold">{{ selectedTime }}</p>
      </div>
      <div class="flex justify-between">
        <p class="text-muted-foreground">Total duration:</p>
        <p v-if="bookingSummaryLoaded" class="font-semibold">{{ bookingTotalDuration }} min</p>
        <p v-else class="font-semibold text-muted-foreground">Calculating...</p>
      </div>
      <div class="flex justify-between">
        <p class="text-muted-foreground">Total:</p>
        <p v-if="bookingSummaryLoaded" class="font-semibold">${{ bookingTotalPrice }}</p>
        <p v-else class="font-semibold text-muted-foreground">Calculating...</p>
      </div>
      <div class="mt-4 border-t border-border pt-4">
        <Label class="block font-semibold">Note</Label>
        <textarea
          v-model="customerNote"
          maxlength="120"
          rows="4"
          placeholder="Any extra details for your appointment..."
          class="mt-2 w-full resize-none rounded-md border border-border bg-background p-3 text-foreground"
        />
        <p class="mt-1 text-xs text-muted-foreground">
          {{ customerNote.length }}/120
        </p>
      </div>
    </CardContent>
  </Card>

  <Button v-if="shouldShowConfirmButton" type="submit" size="lg" class="w-full text-lg font-bold mt-3" :disabled="!canSubmitBooking">
    Confirm Booking
  </Button>
</form>
</template>
