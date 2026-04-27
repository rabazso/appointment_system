<script setup>
import { onUnmounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { X } from 'lucide-vue-next'
import BaseHeader from '@components/layout/BaseHeader.vue'
import Footer from '@components/layout/Footer.vue'
import { Button } from '@/components/ui/button'
import ReviewCard from '@components/ReviewCard.vue'
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/ui/carousel'
import { getEmployeeById } from '@/api'

const route = useRoute()
const router = useRouter()

const fallbackImage = '/images/barber_placeholder.png'
const barber = ref(null)
const reviews = ref([])
const errorMessage = ref('')
const activeGalleryImage = ref(null)
const reviewSnapCount = ref(0)
const reviewCarouselApi = ref(null)
const reviewAutoScrollInterval = ref(null)

async function loadEmployeeDetails() {
  errorMessage.value = ''

  try {
    const response = await getEmployeeById(route.params.id)
    const payload = response.data.data
    barber.value = payload
    reviews.value = payload.reviews
  } catch (error) {
    barber.value = null
    reviews.value = []

    if (error.response?.status === 404) {
      router.push({ name: 'Barbers' })
    } else {
      errorMessage.value = 'Failed to load barber profile.'
    }
  }
}

function onImageError(event) {
  event.target.src = fallbackImage
}

function openGalleryPreview(image) {
  activeGalleryImage.value = image
}

function closeGalleryPreview() {
  activeGalleryImage.value = null
}

function updateReviewCarouselState(api = reviewCarouselApi.value) {
  if (!api) return
  reviewSnapCount.value = api.scrollSnapList().length
}

function stopReviewAutoScroll() {
  if (!reviewAutoScrollInterval.value) return
  clearInterval(reviewAutoScrollInterval.value)
  reviewAutoScrollInterval.value = null
}

function startReviewAutoScroll() {
  stopReviewAutoScroll()

  if (!reviewCarouselApi.value || reviewSnapCount.value <= 1) return

  reviewAutoScrollInterval.value = setInterval(() => {
    const api = reviewCarouselApi.value
    if (!api) return

    if (api.canScrollNext()) {
      api.scrollNext()
      return
    }

    api.scrollTo(0)
  }, 4000)
}

function onReviewCarouselInit(api) {
  if (reviewCarouselApi.value) {
    reviewCarouselApi.value.off('select', updateReviewCarouselState)
    reviewCarouselApi.value.off('reInit', updateReviewCarouselState)
  }

  reviewCarouselApi.value = api
  api.on('select', updateReviewCarouselState)
  api.on('reInit', updateReviewCarouselState)

  updateReviewCarouselState(api)
  startReviewAutoScroll()
}

onUnmounted(() => {
  if (reviewCarouselApi.value) {
    reviewCarouselApi.value.off('select', updateReviewCarouselState)
    reviewCarouselApi.value.off('reInit', updateReviewCarouselState)
  }

  stopReviewAutoScroll()
})

watch(
  () => route.params.id,
  () => {
    loadEmployeeDetails()
  },
  { immediate: true },
)
</script>

<template>
  <BaseHeader />

  <main class="min-h-screen bg-background py-10">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <div
        v-if="errorMessage"
        class="rounded-xl border border-red-200 bg-red-50 px-6 py-4 text-red-700"
      >
        {{ errorMessage }}
      </div>

      <template v-else-if="barber">
        <section class="grid items-start gap-8 rounded-2xl border border-border bg-white p-6 lg:grid-cols-[280px_1fr] lg:p-8">
          <div class="mx-auto h-[280px] w-full max-w-[280px] self-start overflow-hidden rounded-xl bg-muted lg:mx-0 lg:w-[280px]">
            <img :src="barber.profile_image?.preview_url || fallbackImage" :alt="barber.name" class="h-full w-full object-cover"
              @error="onImageError">
          </div>

          <div class="space-y-4">
            <h1 class="text-3xl font-bold md:text-4xl">{{ barber.name }}</h1>
            <p class="text-sm font-medium uppercase tracking-wide text-muted-foreground">
              {{ barber.is_available ? 'Available for booking' : 'Currently unavailable' }}
            </p>
            <p class="text-base text-foreground">
              {{ barber.bio || 'No description available for this barber.' }}
            </p>

            <p class="inline-flex w-fit items-center rounded-full border border-accent/30 bg-accent/10 px-3 py-1 text-sm font-semibold text-accent">
              Rating:
              <span class="ml-1.5">
                {{ barber.rating ?? 'No rating yet' }}
              </span>
              <span v-if="barber.rating !== null" class="ml-1">★</span>
            </p>

            <div class="space-y-1 text-sm">
              <p><span class="font-semibold">Email:</span> {{ barber.email }}</p>
              <p v-if="barber.phone"><span class="font-semibold">Phone:</span> {{ barber.phone }}</p>
            </div>

            <div v-if="barber.links?.length" class="space-y-2">
              <p class="font-semibold">Links</p>
              <ul class="space-y-1">
                <li v-for="(link, index) in barber.links" :key="`${link.url}-${index}`">
                  <a
                    :href="link.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-sm font-semibold text-accent hover:underline"
                  >
                    {{ link.label }}
                  </a>
                </li>
              </ul>
            </div>

            <Button class="mt-4 w-fit" @click="router.push({ name: 'Booking' })">
              Book your appointment
            </Button>
          </div>
        </section>

        <section class="mt-10 rounded-2xl border border-border bg-white p-6">
          <h2 class="text-2xl font-bold">Services</h2>

          <p v-if="!barber.services?.length" class="mt-4 text-muted-foreground">No services available yet.</p>

          <div
            v-else
            class="mt-4 grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-4"
          >
            <article
              v-for="service in barber.services"
              :key="service.id"
              class="rounded-xl border border-border bg-background p-4"
            >
              <h3 class="text-lg font-semibold">{{ service.name }}</h3>
              <p class="mt-2 text-sm">
                Duration: {{ service.duration }} mins
              </p>
              <p class="text-sm">
                Price: ${{ service.price }}
              </p>
            </article>
          </div>
        </section>

        <section class="mt-10 rounded-2xl border border-border bg-white p-6">
          <h2 class="text-2xl font-bold">Gallery</h2>

          <p v-if="!barber.gallery?.length" class="mt-4 text-muted-foreground">No gallery images yet.</p>

          <div v-else class="mt-4 grid grid-cols-[repeat(auto-fill,minmax(160px,1fr))] gap-3">
            <button
              v-for="image in barber.gallery"
              :key="image.id"
              type="button"
              class="overflow-hidden rounded-lg border border-border bg-muted text-left"
              @click="openGalleryPreview(image)"
            >
              <img
                :src="image?.preview_url || fallbackImage"
                :alt="`Gallery image ${image.id}`"
                class="h-36 w-full object-cover transition-transform duration-200 hover:scale-105"
                @error="onImageError"
              >
            </button>
          </div>
        </section>

        <section class="mt-10 rounded-2xl border border-border bg-white p-6">
          <h2 class="text-2xl font-bold">Reviews</h2>

          <p v-if="!reviews.length" class="mt-4 text-muted-foreground">No reviews yet.</p>

          <Carousel
            v-else
            class="mt-4 w-full px-11 sm:px-12"
            @init-api="onReviewCarouselInit"
            @mouseenter="stopReviewAutoScroll"
            @mouseleave="startReviewAutoScroll"
            :opts="{
              align: 'start',
              loop: false,
            }"
          >
            <CarouselContent class="-ml-2">
              <CarouselItem
                v-for="review in reviews"
                :key="review.id"
                class="basis-full pl-2 md:basis-1/2 xl:basis-1/3"
              >
                <ReviewCard :review="review" />
              </CarouselItem>
            </CarouselContent>

            <CarouselPrevious
              v-if="reviewSnapCount > 1"
              class="shadow-none left-2 h-9 w-9 border-slate-300 bg-white text-slate-600 hover:bg-slate-200 hover:text-slate-700 disabled:opacity-40"
            />
            <CarouselNext
              v-if="reviewSnapCount > 1"
              class="shadown-none right-2 h-9 w-9 border-slate-300 bg-white text-slate-600 hover:bg-slate-200 hover:text-slate-700 disabled:opacity-40"
            />
          </Carousel>

        </section>
      </template>
    </div>
  </main>

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

  <Footer />
</template>
