<script setup>
import {ref} from 'vue'
import {Accordion, AccordionContent, AccordionItem, AccordionTrigger} from '@components/ui/accordion'
import {Card, CardContent, CardDescription, CardHeader, CardTitle} from '@components/ui/card'
import {RadioGroup, RadioGroupItem} from '@components/ui/radio-group'
import {Label} from '@components/ui/label'
import { Scissors } from 'lucide-vue-next'
import {Calendar } from '@components/ui/calendar'
import { CalendarDate, fromDate, getLocalTimeZone, today } from '@internationalized/date'
import {Select, SelectContent, SelectItem, SelectTrigger, SelectValue} from '@components/ui/select'
import { Button } from '@components/ui/button'



const services = [
  { id: "1", name: "Hair Cutting", duration: "30 min", price: 35 },
  { id: "2", name: "Shaving", duration: "45 min", price: 50 },
  { id: "3", name: "Beard", duration: "30 min", price: 40 },
  { id: "4", name: "Hair + Beard", duration: "60 min", price: 70 }
]

const barbers = [
  { id: "1", name: "BNarber1", shortdesc: "Haircuts", experience: "12 years" },
  { id: "2", name: "Barber2", shortdesc: "Modern Styles", experience: "8 years" },
  { id: "3", name: "Barber3", shortdesc: "Shaves", experience: "10 years" },
  { id: "4", name: "BNarber4", shortdesc: "Beard", experience: "15 years" }
]

const timeSlots = ['9:00', '10:00', '11:00', '12:00']

const date = ref(fromDate(new Date(), getLocalTimeZone()))
const defaultPlaceholder = today(getLocalTimeZone())

const selectedService = ref('')
const selectedBarber = ref('')
const selectedTime = ref('')
const openSection = ref('service')

const handleSubmit = (()=> {
    console.log('successful booking')
})

</script>
<template>
<form @submit.prevent="handleSubmit" class="space y-6">
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
                            <div class="grid gap-3 grid-cols-2">
                                <Label v-for="service in services" :key="service.id" class="flex gap-3 rounded-lg border-2 p-4" :class="selectedService === service.id ? 'border-primary bg-primary/5' : 'border-border bg-background' ">
                                    <RadioGroupItem :value="service.id" />
                                    <div class="flex-1">
                                        <div class="flex justify-between">
                                            <div class="flex items-center gap-2">
                                                <Scissors class="size-4"></Scissors>
                                                <p class="font-semibold">{{ service.name }}</p>
                                            </div>
                                            <p class="font-bold text-accent">${{ service.price }}</p>
                                        </div>
                                        <p class="text-sm text-muted-foreground">{{ service.duration }}</p>
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
                            <div class="grid gap-3 grid-cols-2">
                                <Label v-for="barber in barbers" :key="barber.id" class="flex gap-3 rounded-lg border-2 p-4" :class="selectedBarber === barber.id ? 'border-primary bg-primary/5' : 'border-border bg-background' ">
                                    <RadioGroupItem :value="barber.id" />
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="font-semibold">{{ barber.name }}</p>
                                        </div>
                                        <p class="text-muted-foreground text-sm mt-1">{{ barber.shortdesc }}</p>
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
                        <div class="w-fit">
                            <Label class="mb-3 block font-semibold">Date</Label>
                            <Calendar v-model="date" :default-placeholder="defaultPlaceholder" class="rounded-md border border-border shadow-sm" layout="month-and-year" :min-value="today(getLocalTimeZone())" :max-value="new CalendarDate(2035, 1, 1)"/>
                        </div>
                        <div>
                            <Label class="font-semibold block mb-3">Time</Label>
                            <Select v-model="selectedTime">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Choose a time slot"/>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="time in timeSlots" :key="time" :value="time">
                                        {{ time }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </AccordionContent>
            </Card>
        </AccordionItem>
    </Accordion>

    <Card v-if="selectedBarber && selectedService && selectedTime && date" class="border-accent/30 bg-accent/10">
        <CardHeader>
            <CardTitle>Booking Summary</CardTitle>
        </CardHeader>
        <CardContent class="space-y-2">
            <div class="flex justify-between">
                <p class="text-muted-foreground">Service:</p>
                <p class="font-semibold">{{ services.find(x => x.id ===selectedService).name }}</p>
            </div>
            <div class="flex justify-between">
                <p class="text-muted-foreground">Barber:</p>
                <p class="font-semibold">{{ barbers.find(x => x.id === selectedBarber).name }}</p>
            </div>
            <div class="flex justify-between">
                <p class="text-muted-foreground">Date:</p>
                <p class="font-semibold">{{ date.toDate(getLocalTimeZone()).toLocaleDateString('en-US') }}</p>
            </div>
            <div class="flex justify-between">
                <p class="text-muted-foreground">Time:</p>
                <p class="font-semibold">{{ selectedTime }}</p>
            </div>
            <div class="flex justify-between">
                <p class="text-muted-foreground">Total:</p>
                <p class="font-semibold">${{ services.find(x => x.id ===selectedService).price }}</p>
            </div>
        </CardContent>
    </Card>

    <Button type="submit" size="lg" class="w-full text-lg font-bold" v-if="selectedBarber && selectedService && selectedTime && date">
        Confirm booking
    </Button>
</form>
</template>