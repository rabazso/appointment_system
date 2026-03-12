<script setup>
import { Button } from '@components/ui/button'
import { Card, CardHeader, CardContent, CardTitle, CardDescription } from '@components/ui/card'
import Autoplay from 'embla-carousel-autoplay'
import {
  Carousel,
  CarouselContent,
  CarouselItem,
} from '@/components/ui/carousel'
import { getEmployees } from '@/api/index'
import { ref, onMounted, onUnmounted } from 'vue'

const plugin = Autoplay({
  delay: 4000,
  stopOnMouseEnter: true,
  stopOnInteraction: false,
})

function getImageSrc(barber) {
  const backendOrigin = 'http://backend.vm1.test'
  const byId = {
    1: 'Blowout Ben.png',
    2: 'Crispy Chris.png',
    3: 'Bouncy Bella.png',
    4: 'Loud Lucy.png',
    5: 'Haircut Harry.png'
  }

  const fileName = byId[barber?.id]
  if (!fileName) return '/images/barber_placeholder.png'
  return `${backendOrigin}/storage/images/${barber.id}/${encodeURIComponent(fileName)}`
}

function onImageError(event) {
  event.target.src = '/images/barber_placeholder.png'
}

const barbers = ref([])
const selectedIndex = ref(0)
const snapCount = ref(0)
const carouselApiRef = ref(null)

function updateCarouselState(api = carouselApiRef.value) {
  if (!api) return
  selectedIndex.value = api.selectedScrollSnap()
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

function scrollToDot(index) {
  carouselApiRef.value?.scrollTo(index)
}

onMounted(async () => {
  try {
    barbers.value = (await getEmployees()).data
  } catch (error) {
    console.error('Failed to load barbers preview:', error)
  }
})

onUnmounted(() => {
  if (!carouselApiRef.value) return
  carouselApiRef.value.off('select', updateCarouselState)
  carouselApiRef.value.off('reInit', updateCarouselState)
})
</script>
<template>
    <section id="barbers" class="py-15 bg-background scroll-mt-15">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Meet Our Barbers</h2>
                <p class="text-lg text-muted-foreground max-w-2xl mx-auto">Handpicked professionals with years of experience in modern and classic barbering techniques</p>
            </div>
            <Carousel
              v-if="barbers.length > 0"
              class="relative w-full"
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
                  <div class="p-1 h-full">
                    <Card class="h-full hover:scale-105 transition-transform duration-300">
                      <CardHeader>
                        <img
                          :src="getImageSrc(barber)"
                          :alt="barber?.name"
                          class="w-full h-72 object-cover rounded-lg"
                          @error="onImageError"
                        >
                      </CardHeader>
                      <CardContent>
                        <CardTitle class="text-xl font-bold text-primary mb-1">{{ barber.name }}</CardTitle>
                        <CardDescription class="text-accent font-semibold">{{ barber.bio }}</CardDescription>
                      </CardContent>
                    </Card>
                  </div>
                </CarouselItem>
              </CarouselContent>
            </Carousel>
            <div
              v-if="barbers.length > 0 && snapCount > 1"
              class="mt-6 flex items-center justify-center gap-2"
            >
              <button
                v-for="index in snapCount"
                :key="index"
                type="button"
                class="h-2.5 w-2.5 rounded-full transition-colors duration-200"
                :class="selectedIndex === index - 1 ? 'bg-accent' : 'bg-muted hover:bg-muted-foreground/40'"
                :aria-label="`Go to slide ${index}`"
                @click="scrollToDot(index - 1)"
              />
            </div>
            <p v-else class="text-center text-muted-foreground">Loading barbers...</p>
            <div class="text-center mt-8">
                <Button variant="secondary" class="text-lg md:text-xl font-bold w-full" to="/barbers">
                    See More...
                </Button>
            </div>
        </div>
    </section>
</template>
