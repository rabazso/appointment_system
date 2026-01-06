<script setup>
import {ref, nextTick} from 'vue'
import { useRouter } from 'vue-router'
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
    <footer class="p-8 md:grid md:grid-cols-3 md:gap-8 flex flex-col items-start gap-8" :class="bgcolor, textcolor">
        <div class="col-span-1">
            <h2 class="text-2xl font-bold mb-4">Contact</h2>
            <p class="flex items-start mb-2">
                <span class="mr-2">📍</span>Budapest 1234 Barber utca 67
            </p>
            <p class="flex items-center mb-2">
                <span class="mr-2">📞</span> 012345678
            </p>
            <p class="flex items-center mb-2">
                <span class="mr-2">✉︎</span> barber@shop.com
            </p>
            <div class="mt-4">
                <p class="flex items-start">
                    <span class="mr-2">🕒</span>Mon-Fri: 9am-8pm
                </p>
                <p class="ml-6">
                    Sat: 10am-5pm
                </p>
                <p class="ml-6">
                    Sun: closed
                </p>
            </div>
        </div>
        <div class="col-span-1 flex flex-col items-center justify-start">
            <div class="mt-4">
                <RouterLink to="/" class="flex items-center" @click.prevent="scrollToLink({ to: '/', hash: '#hero' })">
                    <div class="w-8 h-8 bg-accent rounded-lg flex items-center justify-center">
                        <span class="font-bold text-primary text-lg block">✂</span>
                    </div>
                    <span class="text-xl font-bold block ml-2">{{ title }}</span>
                </RouterLink>
            </div>
        </div>
        <div class="col-span-1 md:text-right">
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