<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { X } from 'lucide-vue-next'
import Autoplay from 'embla-carousel-autoplay'
import { getShopImages } from '@/api'
import { useToastStore } from '@/stores/ToastStore.js'
import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselNext,
  CarouselPrevious,
} from '@/components/ui/carousel'

const plugin = Autoplay({
  delay: 4000,
  stopOnMouseEnter: true,
  stopOnInteraction: false,
})

const fallbackImage = '/images/barber_placeholder.png'
const images = ref([])
const imagePairs = ref([])
const toast = useToastStore()
const snapCount = ref(0)
const carouselApiRef = ref(null)
const activeGalleryImage = ref(null)

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

function openGalleryPreview(image) {
  activeGalleryImage.value = image
}

function closeGalleryPreview() {
  activeGalleryImage.value = null
}

function onImageError(event) {
  event.target.src = fallbackImage
}

function shuffleImages(items) {
  const shuffled = [...items]

  for (let index = shuffled.length - 1; index > 0; index -= 1) {
    const randomIndex = Math.floor(Math.random() * (index + 1))
    const temp = shuffled[index]
    shuffled[index] = shuffled[randomIndex]
    shuffled[randomIndex] = temp
  }

  return shuffled
}

function pickDifferentImage(pool, currentImageId) {
  const candidates = pool.filter((item) => item.id !== currentImageId)
  if (!candidates.length) return null
  const randomIndex = Math.floor(Math.random() * candidates.length)
  return candidates[randomIndex]
}

function buildImagePairs(items) {
  const pairs = []

  for (let index = 0; index < items.length; index += 2) {
    const first = items[index]
    let second = items[index + 1] ?? null

    if (!second && first) {
      second = pickDifferentImage(items, first.id)
    }

    if (first && second) {
      pairs.push([first, second])
    }
  }

  return pairs
}

async function loadGallery() {
  try {
    const response = await getShopImages()
    const galleryImages = response.data.data
    const shuffled = shuffleImages(galleryImages)
    images.value = shuffled
    imagePairs.value = buildImagePairs(shuffled)
  } catch (error) {
    console.error('Failed to load shop gallery images:', error)
    images.value = []
    imagePairs.value = []
    toast.showError('Failed to load gallery images.')
  }
}

onMounted(loadGallery)

onUnmounted(() => {
  if (!carouselApiRef.value) return
  carouselApiRef.value.off('select', updateCarouselState)
  carouselApiRef.value.off('reInit', updateCarouselState)
})
</script>

<template>
  <section id="gallery" class="bg-background py-15 scroll-mt-15 xl:h-[80vh]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="mb-12 text-center">
        <h2 class="mb-4 text-4xl font-bold md:text-5xl">Gallery</h2>
        <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
          A quick look at the shop, the atmosphere, and recent work.
        </p>
      </div>

      <Carousel
        v-if="imagePairs.length"
        class="relative mx-auto block w-full px-11 sm:px-12"
        :plugins="[plugin]"
        @init-api="onCarouselInit"
        :opts="{
          align: 'start',
          loop: true,
        }"
        @mouseenter="plugin.stop()"
        @mouseleave="plugin.reset(); plugin.play()"
      >
        <CarouselContent class="-ml-2">
          <CarouselItem
            v-for="(pair, index) in imagePairs"
            :key="`gallery-pair-${index}-${pair[0].id}-${pair[1].id}`"
            class="pl-2 sm:basis-1/2 lg:basis-1/3"
          >
            <div class="grid grid-rows-2 gap-3">
              <button
                type="button"
                class="block w-full overflow-hidden rounded-xl border border-border bg-muted/20 text-left"
                @click="openGalleryPreview(pair[0])"
              >
                <img
                  :src="pair[0].original_url"
                  :alt="`Shop gallery image ${pair[0].id}`"
                  class="aspect-video w-full object-cover transition-transform duration-200 hover:scale-105"
                  loading="lazy"
                  @error="onImageError"
                >
              </button>

              <button
                type="button"
                class="block w-full overflow-hidden rounded-xl border border-border bg-muted/20 text-left"
                @click="openGalleryPreview(pair[1])"
              >
                <img
                  :src="pair[1].original_url"
                  :alt="`Shop gallery image ${pair[1].id}`"
                  class="aspect-video w-full object-cover transition-transform duration-200 hover:scale-105"
                  loading="lazy"
                  @error="onImageError"
                >
              </button>
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

      <div v-if="!imagePairs.length" class="px-6 py-12 text-center text-muted-foreground">
        At least two gallery images are needed to show the two-row carousel.
      </div>
    </div>
  </section>

  <div
    v-if="activeGalleryImage"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-6"
  >
    <div class="relative max-h-full max-w-6xl" @click.stop>
      <button
        type="button"
        class="absolute right-3 top-3 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-slate-900 shadow-sm transition hover:bg-white"
        @click="closeGalleryPreview"
      >
        <X class="h-5 w-5" />
      </button>

      <img
        :src="activeGalleryImage.original_url"
        :alt="`Gallery image ${activeGalleryImage.id}`"
        class="max-h-[85vh] max-w-full rounded-2xl object-contain shadow-2xl"
        @error="onImageError"
      >
    </div>
  </div>
</template>
