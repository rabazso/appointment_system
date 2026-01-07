<script setup>
import {ref, watch, nextTick, onMounted} from 'vue'
import {Accordion, AccordionContent, AccordionItem, AccordionTrigger} from '@components/ui/accordion'
import {Card, CardContent, CardDescription, CardHeader, CardTitle} from '@components/ui/card'
import {RadioGroup, RadioGroupItem} from '@components/ui/radio-group'
import {Label} from '@components/ui/label'
import { Scissors } from 'lucide-vue-next'
import {Calendar } from '@components/ui/calendar'
import { fromDate, getLocalTimeZone, today } from '@internationalized/date'
import { Button } from '@components/ui/button'
import { getServices, getEmployeesByService, getAppointmentByServiceAndDate, getAppointmentsByServiceAndDateAndEmployee } from '@/api/index'

const selectedService = ref('')
const selectedBarber = ref('')
const selectedTime = ref('')
const openSection = ref('service')

const services = ref([])
const barbers = ref([])
const date = ref(fromDate(new Date(), getLocalTimeZone()))
const defaultPlaceholder = today(getLocalTimeZone())

const timeSlots = ref([])


onMounted(async ()=>{
    services.value = (await getServices()).data
})

watch(selectedService, async (newService) => {
    if (!newService) return
    barbers.value = (await getEmployeesByService(selectedService.value)).data
})

const previousBarbers = ref([])
watch([selectedService, selectedBarber, date], async ([serviceId, barberId, selectedDate]) => {
    if (!serviceId) return
    const isoDate = selectedDate.toDate(getLocalTimeZone()).toISOString().split('T')[0]
    if((serviceId && selectedDate) && !barberId){
        const response = await getAppointmentByServiceAndDate(serviceId, isoDate)
        timeSlots.value = response.data[isoDate]
    }
    else{
        previousBarbers.value.push(barberId)
        const response = await getAppointmentsByServiceAndDateAndEmployee(serviceId, isoDate, barberId)
        timeSlots.value = response.data[isoDate]
    }
    if(previousBarbers.value.length == 1) return
    else selectedTime.value = ''
  }
)

const summaryRef = ref(null)
const isVisible = ()=> selectedBarber.value && selectedService.value && selectedTime.value && date.value
watch(isVisible, async(visible)=>{
    if(visible){
        await nextTick()
        summaryRef.value.$el.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        })
    }
})


const handleSubmit = (()=> {
    console.log('successful booking')
})
</script>
<template>
<form @submit.prevent class="space y-6">
    <Accordion type="single" collapsible class="space-y-4" v-model="openSection">
        <AccordionItem value="service" class="border-border">
            <Card class="bg-background">
                <AccordionTrigger class="px-6 pt-6 hover:no-underline">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center rounded-full bg-primary text-primary-foreground text-lg font-semibold size-10">
                            1
                        </div>
                        <div class="text-left">
                            <CardTitle class="text-foreground">Select Your Service</CardTitle>
                            <CardDescription>Choose the service you need</CardDescription>
                        </div>
                    </div>
                </AccordionTrigger>
                <AccordionContent>
                    <CardContent class="pt-4">
                        <RadioGroup v-model="selectedService" @update:modelValue="openSection = 'barber'">
                            <div class="grid gap-3 md:grid-cols-2">
                                <Label v-for="service in services" :key="service.id" class="flex gap-3 rounded-lg border-2 p-4" :class="selectedService === service.id ? 'border-primary bg-primary/5' : 'border-border bg-background' ">
                                    <RadioGroupItem :value="service.id" />
                                    <div class="flex-1">
                                        <div class="flex justify-between">
                                            <div class="flex items-center gap-2">
                                                <Scissors class="size-4"></Scissors>
                                                <p class="font-semibold">{{ service.name }}</p>
                                            </div>
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

        <AccordionItem value="barber" class="border-border">
            <Card class="bg-background">
                <AccordionTrigger class="px-6 pt-6 hover:no-underline">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center rounded-full bg-primary text-primary-foreground text-lg font-semibold size-10">
                            2
                        </div>
                        <div class="text-left">
                            <CardTitle class="text-foreground">Select Your Barber</CardTitle>
                            <CardDescription>Select your preferred barber</CardDescription>
                        </div>
                    </div>
                </AccordionTrigger>
                <AccordionContent>
                    <CardContent class="pt-4">
                        <RadioGroup v-model="selectedBarber" @update:modelValue="openSection = 'datetime'">
                            <div class="grid gap-3 md:grid-cols-2">
                                <Label v-for="barber in barbers" :key="barber.id" class="flex gap-3 rounded-lg border-2 p-4" :class="selectedBarber === barber.id ? 'border-primary bg-primary/5' : 'border-border bg-background' ">
                                    <RadioGroupItem :value="barber.id" />
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="font-semibold">{{ barber.name }}</p>
                                        </div>
                                        <p class="text-accent font-semibold text-sm mt-1">${{ barber.services.price }}</p>
                                    </div>
                                </Label>
                            </div>
                        </RadioGroup>
                    </CardContent>
                </AccordionContent>
            </Card>
        </AccordionItem>

        <AccordionItem value="datetime" class="border-border">
            <Card class="bg-background">
                <AccordionTrigger class="px-6 pt-6 hover:no-underline">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center rounded-full bg-primary text-primary-foreground text-lg font-semibold size-10">
                            3
                        </div>
                        <div class="text-left">
                            <CardTitle class="text-foreground">Pick Date and Time</CardTitle>
                            <CardDescription>Select your appointment date and time</CardDescription>
                        </div>
                    </div>
                </AccordionTrigger>
                <AccordionContent>
                    <CardContent class="pt-4 space-y-6">
                        <div class="grid md:grid-cols-2">
                            <div class="w-fit">
                                <Label class="mb-3 block font-semibold">Select Your Date</Label>
                                <Calendar v-model="date" :default-placeholder="defaultPlaceholder" class="rounded-md border border-border shadow-sm" layout="month-and-year" :min-value="today(getLocalTimeZone())" :max-value="today(getLocalTimeZone()).add({weeks:4})"/>
                            </div>
                            <div>
                                <Label class="font-semibold block mb-3">Choose a time slot</Label>
                                <Card>
                                    <CardContent class="flex flex-row flex-wrap space-x-2 space-y-2">
                                        <p v-if="!timeSlots" class="text-muted-foreground">
                                            No available time slots for this day
                                        </p>
                                        <Button v-model="selectedTime" variant="outline" v-for="time in timeSlots" :key="time" :value="time" class="w-fit px-8 py-4 bg-background" :class="selectedTime === time ? 'border-accent border-[2px] bg-primary/10' : 'bg-background'" @click="selectedTime = time">{{ time }}</Button>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </CardContent>
                </AccordionContent>
            </Card>
        </AccordionItem>
    </Accordion>

    <Card v-if="selectedBarber && selectedService && selectedTime && date" class="border-accent/30 bg-accent/10" ref="summaryRef">
        <CardHeader>
            <CardTitle>Booking Summary</CardTitle>
        </CardHeader>
        <CardContent class="space-y-2">
            <div class="flex justify-between">
                <p class="text-muted-foreground">Service:</p>
                <p class="font-semibold">{{ services.find(x => x.id ===selectedService)?.name }}</p>
            </div>
            <div class="flex justify-between">
                <p class="text-muted-foreground">Barber:</p>
                <p class="font-semibold">{{ barbers.find(x => x.id === selectedBarber).name }}</p>
            </div>
            <div class="flex justify-between">
                <p class="text-muted-foreground">Date:</p>
                <p class="font-semibold">{{ date.toDate(getLocalTimeZone()).toISOString().split('T')[0] }}</p>
            </div>
            <div class="flex justify-between">
                <p class="text-muted-foreground">Time:</p>
                <p class="font-semibold">{{ selectedTime }}</p>
            </div>
            <div class="flex justify-between">
                <p class="text-muted-foreground">Total:</p>
                <p class="font-semibold">${{ barbers.find(x => x.id ===selectedBarber).services.price }}</p>
            </div>
        </CardContent>
    </Card>

    <Button type="submit" @click="handleSubmit" size="lg" class="w-full text-lg font-bold mt-3" v-if="selectedBarber && selectedService && selectedTime && date">
        Confirm booking
    </Button>
</form>
</template>