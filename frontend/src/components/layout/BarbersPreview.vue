<script setup>
import { Button } from '@components/ui/button'
import { Card, CardHeader, CardContent, CardTitle, CardDescription } from '@components/ui/card'
import Autoplay from 'embla-carousel-autoplay'
import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselNext,
  CarouselPrevious,
} from '@/components/ui/carousel'
import { getEmployees } from '@/api/index'
import { ref, onMounted, onUnmounted } from 'vue'
import { useToastStore } from '@/stores/ToastStore.js'

const plugin = Autoplay({
  delay: 4000,
  stopOnMouseEnter: true,
  stopOnInteraction: false,
})

const barbers = ref([])
const toast = useToastStore()
const snapCount = ref(0)
const carouselApiRef = ref(null)

function updateCarouselState(api = carouselApiRef.value) {
  if (!api) return
  snapCount.value = api.scrollSnapList().length
}

function onCarouselInit(api) {
  if (carouselApiRef.value) {
    carouselApiRef.value.off('select', updateCarouselState)
    carouselApiRef.value.off('reInit', updateCarouselState)
  }

  carouselApiRef.value = api
  api.on('select', updateCarouselState)
  api.on('reInit', updateCarouselState)
  updateCarouselState(api)
}

function formatRatingLabel(rating) {
  return Number.isInteger(rating) ? String(rating) : rating.toFixed(1)
}

onMounted(async () => {
  try {
    const response = await getEmployees()
    barbers.value = response.data.data
  } catch (error) {
    console.error('Failed to load barbers preview:', error)
    toast.showError('Failed to load barbers preview.')
  }
})

onUnmounted(() => {
  if (!carouselApiRef.value) return
  carouselApiRef.value.off('select', updateCarouselState)
  carouselApiRef.value.off('reInit', updateCarouselState)
})
</script>
<template>
    <section id="barbers" class="py-15 bg-background scroll-mt-15 xl:h-[94vh]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Meet Our Barbers</h2>
                <p class="text-lg text-muted-foreground max-w-2xl mx-auto">Handpicked professionals with years of experience in modern and classic barbering techniques</p>
            </div>
            <Carousel
              v-if="barbers.length > 0"
              class="relative w-full px-11 sm:px-12"
              :plugins="[plugin]"
              @init-api="onCarouselInit"
              :opts="{
                align: 'start',
                loop: true,
              }"
              @mouseenter="plugin.stop()"
              @mouseleave="plugin.reset(); plugin.play()"
            >
              <CarouselContent class="-ml-1">
                <CarouselItem
                  v-for="barber in barbers"
                  :key="barber?.id"
                  class="pl-1 md:basis-1/2 lg:basis-1/3"
                >
                  <div class="p-1">
                    <RouterLink :to="{ name: 'EmployeeDetails', params: { id: barber.id } }" class="block">
                      <Card class="gap-3 py-4 hover:scale-105 transition-transform duration-300">
                        <CardHeader>
                          <img
                            :src="barber.profile_image?.preview_url ?? '/images/barber_placeholder.png'"
                            :alt="barber?.name"
                            class="w-full h-72 object-cover rounded-lg"
                          >
                        </CardHeader>
                        <CardContent class="h-fit justify-start pt-0">
                          <CardTitle class="text-xl text-center font-bold text-primary mb-1">{{ barber.name }}</CardTitle>
                          <CardDescription class="mt-1 flex justify-center">
                            <p class="inline-flex w-fit items-center rounded-full border border-accent/40 bg-accent/10 px-3 py-1 text-sm font-semibold text-accent">
                              Rating:
                              <span class="ml-1.5">
                                {{ barber?.rating !== null && barber?.rating !== undefined ? formatRatingLabel(barber.rating) : 'No rating yet' }}
                              </span>
                              <span v-if="barber?.rating !== null && barber?.rating !== undefined" class="ml-1">★</span>
                            </p>
                          </CardDescription>
                        </CardContent>
                      </Card>
                    </RouterLink>
                  </div>
                </CarouselItem>
              </CarouselContent>

              <CarouselPrevious
                v-if="snapCount > 1"
                class="left-2 h-9 w-9 border-slate-300 bg-white text-slate-600 shadow-none hover:bg-slate-200 hover:text-slate-700 disabled:opacity-40"
              />
              <CarouselNext
                v-if="snapCount > 1"
                class="right-2 h-9 w-9 border-slate-300 bg-white text-slate-600 shadow-none hover:bg-slate-200 hover:text-slate-700 disabled:opacity-40"
              />
            </Carousel>
            <p v-else class="text-center text-muted-foreground">Loading barbers...</p>
            <div class="text-center mt-22">
                <Button variant="secondary" class="text-lg md:text-xl font-bold w-full" :to="{ name: 'Barbers' }">
                    See More...
                </Button>
            </div>
        </div>
    </section>
</template>
