<script setup>
import { computed, nextTick, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import {Clock, Mail, Phone, MapPin} from 'lucide-vue-next'
import { usePublicShopStore } from '@stores/PublicShopStore.js'
const title = import.meta.env.VITE_APP_NAME
const props = defineProps({
  variant: { type: String, required: false }
})

let bgcolor = ref('bg-primary')
let textcolor = ref('text-primary-foreground')
if (props.variant === 'background') {
  bgcolor = ref('bg-background')
  textcolor = ref('text-foreground')
}

const publicShop = usePublicShopStore()
onMounted(() => {
  publicShop.fetchPublicShopData()
})

const contactAddress = computed(() => publicShop.contact.address || 'Address not set')
const contactPhone = computed(() => publicShop.contact.phone || 'Phone not set')
const contactEmail = computed(() => publicShop.contact.email || 'Email not set')
const openingHours = computed(() => publicShop.openingHours)
const contactLinks = computed(() => {
  const links = Array.isArray(publicShop.contact.links) ? publicShop.contact.links : []

  return links
    .map((link) => ({
      label: String(link?.label || '').trim(),
      url: normalizeUrl(link?.url),
    }))
    .filter((link) => link.label && link.url)
})
const phoneHref = computed(() => {
  if (!publicShop.contact.phone) return null
  return `tel:${publicShop.contact.phone.replace(/\s+/g, '')}`
})
const emailHref = computed(() => {
  if (!publicShop.contact.email) return null
  return `mailto:${publicShop.contact.email}`
})
const formatHours = (day) => {
  if (!day?.isOpen) return 'Closed'
  if (!day.openTime || !day.closeTime) return 'Closed'
  return `${day.openTime} - ${day.closeTime}`
}

function normalizeUrl(value) {
  const raw = String(value || '').trim()
  if (!raw) return ''

  if (/^https?:\/\//i.test(raw)) {
    return raw
  }

  return `https://${raw}`
}

const router = useRouter()
const scrollToLink = async (link) => {
  if (!link) return

  if (link.hash) {
    if (router.currentRoute.value.path !== link.to) {
      await router.push(link.to)
      await nextTick()
    }
    const element = document.querySelector(link.hash)
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' })
    }
  } else {
    router.push(link.to)
  }
}
</script>
<template>
    <footer class="p-8 md:grid md:grid-cols-3 md:gap-8 flex flex-col items-start gap-8" :class="[bgcolor, textcolor]">
        <div class="col-span-1 mx-auto mt-5 md:mt-0">
            <h2 class="text-2xl font-bold mb-4">Contact</h2>
            <div class="flex space-x-2">
                <div class="items-end">
                    <MapPin/>
                </div>
                <div class="items-start">
                    <p>{{ contactAddress }}</p>
                </div>
            </div>

            <div class="mt-4 flex space-x-2">
                <div class="items-end">
                    <Phone/>
                </div>
                <div class="items-start">
                    <a v-if="phoneHref" :href="phoneHref" class="hover:underline">{{ contactPhone }}</a>
                    <p v-else>{{ contactPhone }}</p>
                </div>
            </div>
            
            <div class="mt-4 flex space-x-2">
                <div class="items-end">
                    <Mail/>
                </div>
                <div class="items-start">
                    <a v-if="emailHref" :href="emailHref" class="hover:underline">{{ contactEmail }}</a>
                    <p v-else>{{ contactEmail }}</p>
                </div>
            </div>

            <div class="mt-4 flex items-start space-x-2">
                <div class="items-end">
                    <Clock/>
                </div>
                <div class="items-start">
                    <p
                      v-for="day in openingHours"
                      :key="day.weekday"
                    >
                      <span class="font-medium">{{ day.label }}: </span>
                      <span> {{ formatHours(day) }}</span>
                    </p>
                </div>
            </div>

            <div v-if="contactLinks.length" class="mt-4">
                <p class="font-semibold">Links</p>
                <ul class="mt-1 space-y-1">
                    <li v-for="(link, index) in contactLinks" :key="`${link.url}-${index}`">
                        <a
                          :href="link.url"
                          target="_blank"
                          rel="noopener noreferrer"
                          class="underline hover:opacity-80"
                        >
                          {{ link.label }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-span-1 flex-col items-center justify-start hidden md:flex">
            <div class="mt-4">
                <RouterLink to="/" class="flex items-center" @click.prevent="scrollToLink({ to: '/', hash: '#hero' })">
                    <div class="w-8 h-8 bg-accent rounded-lg flex items-center justify-center">
                        <span class="font-bold text-primary text-lg block">✂</span>
                    </div>
                    <span class="text-xl font-bold block ml-2">{{ title }}</span>
                </RouterLink>
            </div>
        </div>
        <div class="col-span-1 md:text-right mx-auto hidden md:block">
            <h2 class="text-2xl font-bold mb-4">Quick Links</h2>
            <ul>
                <li class="mb-2 underline"><RouterLink to="/">Home</RouterLink></li>
                <li class="mb-2 underline"><RouterLink to="/barbers">Our Barbers</RouterLink></li>
                <li class="mb-2 underline"><RouterLink to="/contact">Contact</RouterLink></li>
                <li class="mb-2 underline"><RouterLink to="/booking">Book Now</RouterLink></li>
            </ul>
        </div>
    </footer>
</template>
