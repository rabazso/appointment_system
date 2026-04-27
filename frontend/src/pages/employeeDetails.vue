<script setup>
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Star, X } from 'lucide-vue-next'
import BaseHeader from '@components/layout/BaseHeader.vue'
import Footer from '@components/layout/Footer.vue'
import { Button } from '@/components/ui/button'
import { getEmployeeById } from '@/api'

const route = useRoute()
const router = useRouter()

const fallbackImage = '/images/barber_placeholder.png'
const barber = ref(null)
const reviews = ref([])
const errorMessage = ref('')
const activeGalleryImage = ref(null)

function resetEmployeeDetailsState() {
  barber.value = null
  reviews.value = []
}

async function loadEmployeeDetails() {
  errorMessage.value = ''

  try {
    const response = await getEmployeeById(route.params.id)
    const payload = response.data.data
    barber.value = payload
    reviews.value = payload?.reviews?.data ?? []
  } catch (error) {
    resetEmployeeDetailsState()

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

function reviewStars(review) {
  const value = Number(review?.rating)
  if (!Number.isFinite(value) || value <= 0) return 0
  return Math.min(5, Math.max(0, Math.round(value)))
}

function formatReviewDate(value) {
  if (!value) return ''

  const date = new Date(value)
  if (!Number.isFinite(date.getTime())) return ''

  return new Intl.DateTimeFormat('hu-HU', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date)
}

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
            <img
              :src="barber.profile_image?.preview_url || fallbackImage"
              :alt="barber.name"
              class="h-full w-full object-cover"
              @error="onImageError"
            >
          </div>

          <div class="space-y-4">
            <h1 class="text-3xl font-bold md:text-4xl">{{ barber.name }}</h1>
            <p class="text-sm font-medium uppercase tracking-wide text-muted-foreground">
              {{ barber.is_available ? 'Available for booking' : 'Currently unavailable' }}
            </p>
            <p class="text-base leading-relaxed text-foreground">
              {{ barber.bio || 'No description available for this barber.' }}
            </p>

            <p class="inline-flex w-fit items-center rounded-full border border-accent/30 bg-accent/10 px-3 py-1 text-sm font-semibold text-accent">
              Rating:
              <span class="ml-1.5">
                {{ barber.rating ?? 'No rating yet' }}
              </span>
              <span v-if="barber.rating !== null" class="ml-1">★</span>
            </p>

            <div class="space-y-1 text-sm text-muted-foreground">
              <p>Email: {{ barber.email }}</p>
              <p v-if="barber.phone">Phone: {{ barber.phone }}</p>
            </div>

            <div v-if="barber.links?.length" class="space-y-2">
              <p class="text-sm font-semibold">Links</p>
              <ul class="space-y-1">
                <li v-for="(link, index) in barber.links" :key="`${link.url}-${index}`">
                  <a
                    :href="link.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-sm text-accent hover:underline"
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
              <p class="mt-2 text-sm text-muted-foreground">
                Duration: {{ service.duration }} mins
              </p>
              <p class="text-sm text-muted-foreground">
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
                :src="image.preview_url"
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

          <div v-else class="mt-4 space-y-4">
            <article
              v-for="review in reviews"
              :key="review.id"
              class="rounded-2xl border border-border bg-white p-5 shadow-sm transition-shadow hover:shadow-md"
            >
              <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                  <p class="truncate text-base font-semibold text-slate-900">
                    {{ review.customer?.name || 'Anonymous' }}
                  </p>
                  <p class="mt-1 text-xs text-slate-500">
                    {{ formatReviewDate(review.created_at) }}
                  </p>
                </div>

                <div class="flex shrink-0 items-center gap-0.5 text-accent">
                  <Star
                    v-for="star in reviewStars(review)"
                    :key="`${review.id}-${star}`"
                    class="h-4 w-4 fill-accent text-accent"
                  />
                </div>
              </div>

              <p class="mt-4 text-sm leading-relaxed text-slate-700">
                {{ review.comment || '' }}
              </p>
            </article>
          </div>

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
