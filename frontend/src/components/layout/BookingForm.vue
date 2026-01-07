<script setup>
import { ref, watch, nextTick, onMounted } from 'vue'
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@components/ui/accordion'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@components/ui/card'
import { RadioGroup, RadioGroupItem } from '@components/ui/radio-group'
import { Label } from '@components/ui/label'
import { Scissors } from 'lucide-vue-next'
import { Calendar } from '@components/ui/calendar'
import { fromDate, getLocalTimeZone, today } from '@internationalized/date'
import { Button } from '@components/ui/button'
import { 
  getServices, 
  getEmployeesByService, 
  getAppointmentByServiceAndDate, 
  getAppointmentsByServiceAndDateAndEmployee,

} from '@/api/index'

const selectedService = ref('')
const selectedBarber = ref('')
const selectedDate = ref('')
const selectedTime = ref('')
const userData = ref({ name: '', email: '', phone: '' })

const openSection = ref('service')

const services = ref([])
const barbers = ref([])
const timeSlots = ref([])

const barberRef = ref(null)
const dateTimeRef = ref(null)
const userDataRef = ref(null)

onMounted(async () => {
  services.value = (await getServices()).data
})

watch(selectedService, async (serviceId) => {
  if (!serviceId) return

  barbers.value = (await getEmployeesByService(serviceId)).data

  openSection.value = 'barber'

  await nextTick()
  barberRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
})

watch(selectedBarber, async (barberId) => {
  if (!barberId) return

  selectedTime.value = ''
    selectedDate.value = today(getLocalTimeZone())
  await loadTimeSlots()
  openSection.value = 'datetime'

  await nextTick()
  dateTimeRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
})

watch(selectedDate, async () => {
  if (!selectedService.value || !selectedBarber.value) return
  selectedTime.value = ''
  await loadTimeSlots()
})

async function loadTimeSlots() {
  const isoDate = selectedDate.value.toDate(getLocalTimeZone()).toISOString().split('T')[0]
  let res
  if (selectedBarber.value) {
    res = await getAppointmentsByServiceAndDateAndEmployee(selectedService.value, isoDate, selectedBarber.value)
  } else {
    res = await getAppointmentByServiceAndDate(selectedService.value, isoDate)
  }
  timeSlots.value = res.data?.[isoDate] || []
}

const isReadyForUser = () => selectedService.value && selectedBarber.value && selectedTime.value && selectedDate.value

watch(
  () => isReadyForUser(),
  async (ready) => {
    if (ready) {
      openSection.value = 'userdata'
      await nextTick()
      userDataRef.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      })
    }
  }
)

const handleSubmit = () => {}
</script>

<template>
<form @submit.prevent class="space-y-6">

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
            <RadioGroup v-model="selectedService">
              <div class="grid gap-3 md:grid-cols-2">
                <Label v-for="service in services" :key="service.id"
                       class="flex gap-3 p-4 rounded-lg border-2"
                       :class="selectedService === service.id ? 'border-primary bg-primary/5' : 'border-border bg-background'">
                  <RadioGroupItem :value="service.id" />
                  <div class="flex-1">
                    <div class="flex items-center gap-2">
                      <Scissors class="size-4" />
                      <p class="font-semibold">{{ service.name }}</p>
                    </div>
                    <p class="text-sm text-muted-foreground">{{ service.duration }} mins</p>
                  </div>
                </Label>
              </div>
            </RadioGroup>
          </CardContent>
        </AccordionContent>
      </Card>
    </AccordionItem>

    <AccordionItem value="barber" :disabled="!selectedService">
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
                       class="flex gap-3 p-4 rounded-lg border-2"
                       :class="selectedBarber === barber.id ? 'border-primary bg-primary/5' : 'border-border bg-background'">
                  <RadioGroupItem :value="barber.id" />
                  <div class="flex-1">
                    <p class="font-semibold">{{ barber.name }}</p>
                    <p class="text-sm text-accent mt-1">${{ barber.services.price }}</p>
                  </div>
                </Label>
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
                          class="rounded-md border border-border shadow-sm"
                          layout="month-and-year"
                          :min-value="today(getLocalTimeZone())"
                          :max-value="today(getLocalTimeZone()).add({weeks:4})"/>
              </div>
              <div>
                <Label class="block mb-2 font-semibold">Choose a Time Slot</Label>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                  <Button v-for="time in timeSlots" :key="time"
                          variant="outline"
                          :class="selectedTime === time ? 'border-accent border-2 bg-primary/10' : 'bg-background'"
                          @click="selectedTime = time">
                    {{ time }}
                  </Button>
                </div>
              </div>
            </div>
          </CardContent>
        </AccordionContent>
      </Card>
      </div>
    </AccordionItem>

    <AccordionItem value="userdata" :disabled="!selectedTime">
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
            <Label class="block font-semibold">Name</Label>
            <input v-model="userData.name" type="text" placeholder="Your full name" class="w-full border border-border rounded-md p-2"/>
            <Label class="block font-semibold">Email</Label>
            <input v-model="userData.email" type="email" placeholder="you@example.com" class="w-full border border-border rounded-md p-2"/>
            <Label class="block font-semibold">Phone</Label>
            <input v-model="userData.phone" type="tel" placeholder="Your phone number" class="w-full border border-border rounded-md p-2"/>
          </CardContent>
        </AccordionContent>
      </Card>
      </div>
    </AccordionItem>

  </Accordion>

  <Card v-if="isReadyForUser()" class="border-accent/30 bg-accent/10" >
    <CardHeader>
      <CardTitle>Booking Summary</CardTitle>
    </CardHeader>
    <CardContent class="space-y-2">
      <div class="flex justify-between"><p class="text-muted-foreground">Service:</p><p class="font-semibold">{{ services.find(s => s.id === selectedService)?.name }}</p></div>
      <div class="flex justify-between"><p class="text-muted-foreground">Barber:</p><p class="font-semibold">{{ barbers.find(b => b.id === selectedBarber)?.name }}</p></div>
      <div class="flex justify-between"><p class="text-muted-foreground">Date:</p><p class="font-semibold">{{ selectedDate.toDate(getLocalTimeZone()).toISOString().split('T')[0] }}</p></div>
      <div class="flex justify-between"><p class="text-muted-foreground">Time:</p><p class="font-semibold">{{ selectedTime }}</p></div>
      <div class="flex justify-between"><p class="text-muted-foreground">Total:</p><p class="font-semibold">${{ barbers.find(b => b.id === selectedBarber)?.services.price }}</p></div>
    </CardContent>
  </Card>

  <Button v-if="isReadyForUser()" type="submit" size="lg" class="w-full text-lg font-bold mt-3" @click="handleSubmit">
    Confirm Booking
  </Button>
</form>
</template>
