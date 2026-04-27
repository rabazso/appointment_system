<script setup>
import { computed, onMounted } from 'vue'
import { Clock3, Mail, MapPin, Phone } from 'lucide-vue-next'
import { usePublicShopStore } from '@stores/PublicShopStore.js'

const publicShop = usePublicShopStore()
onMounted(() => {
  publicShop.fetchPublicShopData()
})

const fallbackAddress = 'Budapest, Clark Ádám tér'
const address = computed(() => publicShop.contact.address || fallbackAddress)
const phone = computed(() => publicShop.contact.phone || 'Phone not set')
const email = computed(() => publicShop.contact.email || 'Email not set')
const phoneHref = computed(() => {
  if (!publicShop.contact.phone) return null
  return `tel:${publicShop.contact.phone.replace(/\s+/g, '')}`
})
const emailHref = computed(() => {
  if (!publicShop.contact.email) return null
  return `mailto:${publicShop.contact.email}`
})
const mapURL = computed(() => {
  const encodedAddress = encodeURIComponent(address.value)
  return `https://maps.google.com/maps?q=${encodedAddress}&t=&z=15&ie=UTF8&iwloc=&output=embed`
})
const openingHours = computed(() => publicShop.openingHours)

function formatHours(day) {
  if (!day?.isOpen) return 'Closed'
  if (!day.openTime || !day.closeTime) return 'Closed'
  return `${day.openTime} - ${day.closeTime}`
}
</script>

<template>
  <section class="mx-auto max-w-6xl px-4 py-8 md:py-16">
    <div class="mb-8 text-center md:mb-12">
      <h1 class="mb-3 text-4xl font-bold text-foreground md:text-5xl">Contact Us</h1>
      <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
        Have a question, need help with a booking, or want to reach the shop directly? We are here to help.
      </p>
    </div>

    <div class="grid gap-8 lg:grid-cols-2">
      <div class="rounded-xl border bg-background p-6 shadow-sm md:p-8">
        <h2 class="mb-6 text-2xl font-semibold text-foreground">Get In Touch</h2>

        <div class="space-y-6">
          <div class="flex items-start gap-4">
            <div class="rounded-lg bg-accent/20 p-2">
              <MapPin class="h-5 w-5 text-primary" />
            </div>
            <div>
              <p class="font-semibold text-foreground">Address</p>
              <p class="text-muted-foreground">{{ address }}</p>
            </div>
          </div>

          <div class="flex items-start gap-4">
            <div class="rounded-lg bg-accent/20 p-2">
              <Phone class="h-5 w-5 text-primary" />
            </div>
            <div>
              <p class="font-semibold text-foreground">Phone</p>
              <a
                v-if="phoneHref"
                :href="phoneHref"
                class="text-muted-foreground transition-colors hover:text-foreground"
              >
                {{ phone }}
              </a>
              <p v-else class="text-muted-foreground">{{ phone }}</p>
            </div>
          </div>

          <div class="flex items-start gap-4">
            <div class="rounded-lg bg-accent/20 p-2">
              <Mail class="h-5 w-5 text-primary" />
            </div>
            <div>
              <p class="font-semibold text-foreground">Email</p>
              <a
                v-if="emailHref"
                :href="emailHref"
                class="text-muted-foreground transition-colors hover:text-foreground"
              >
                {{ email }}
              </a>
              <p v-else class="text-muted-foreground">{{ email }}</p>
            </div>
          </div>

          <div class="flex items-start gap-4">
            <div class="rounded-lg bg-accent/20 p-2">
              <Clock3 class="h-5 w-5 text-primary" />
            </div>
            <div>
              <p class="font-semibold text-foreground">Opening Hours</p>
              <p
                v-for="day in openingHours"
                :key="day.weekday"
                class="text-muted-foreground"
              >
                <span class="font-medium">{{ day.label }}: </span>
                <span> {{ formatHours(day) }}</span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="relative min-h-[360px] overflow-hidden rounded-xl border bg-background shadow-sm md:min-h-[420px]">
        <iframe
          :src="mapURL"
          title="Barbershop location map"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          class="absolute inset-0 h-full w-full border-0"
        ></iframe>
      </div>
    </div>
  </section>
</template>
