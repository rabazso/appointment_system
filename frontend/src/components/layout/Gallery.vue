<script setup>
import { onMounted, ref } from 'vue'
import { getShopImages } from '@/api'
import {
  Carousel,
  CarouselContent,
  CarouselItem,
} from '@/components/ui/carousel'

const images = ref([])

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

async function loadGallery() {
  try {
    const response = await getShopImages()
    const galleryImages = response.data.data
    images.value = shuffleImages(galleryImages)
  } catch (error) {
    console.error('Failed to load shop gallery images:', error)
    images.value = []
  }
}

onMounted(loadGallery)
</script>

<template>
  <section id="gallery" class="bg-background py-15 scroll-mt-15">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="mb-12 text-center">
        <h2 class="mb-4 text-4xl font-bold md:text-5xl">Gallery</h2>
        <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
          A quick look at the shop, the atmosphere, and recent work.
        </p>
      </div>

      <Carousel
        v-if="images.length"
        class="relative w-full max-w-6xl block mx-auto"
        :opts="{
          align: 'start',
          loop: true,
        }"
      >
        <CarouselContent class="-ml-2">
          <CarouselItem
            v-for="(image, index) in images"
            :key="`${image.id}-${index}`"
            class="pl-2 sm:basis-1/2 lg:basis-1/3"
          >
            <article class="overflow-hidden rounded-xl border border-border bg-muted/20">
              <img
                :src="image.original_url"
                :alt="`Shop gallery image ${image.id}`"
                class="h-48 w-full object-cover md:h-56"
                loading="lazy"
              >
            </article>
          </CarouselItem>
        </CarouselContent>
      </Carousel>

      <div v-if="!images.length" class="px-6 py-12 text-center text-muted-foreground">
        No gallery images available yet.
      </div>
    </div>
  </section>
</template>
