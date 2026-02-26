<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { getEmployees } from '@/api/index'
import { useAuthStore } from '@/stores/AuthStore'

import AuthChoiceModal from '@/components/modals/AuthChoiceModal.vue'
import AuthModal from '@/components/auth/AuthModal.vue'
import Toast from '@/components/ui/Toast.vue'

const router = useRouter()
const authStore = useAuthStore()

const barbers = ref([])
const showAuthChoice = ref(false)
const loginOpen = ref(false)
const showToast = ref(false)
const toastMessage = ref('')

const isLoggedIn = computed(() => authStore.isLoggedIn)

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

onMounted(async () => {
  try {
    const response = await getEmployees()
    barbers.value = response.data 
  } catch (error) {
    console.error('Nem sikerült betölteni a barbereket:', error)
  }
})

function handleBookingClick() {
  if (isLoggedIn.value) {
    router.push({ name: 'Booking' })
  } else {
    showAuthChoice.value = true
  }
}

function continueAsGuest() {
  showAuthChoice.value = false
  router.push({ name: 'Booking' })
}

function handleLoginChoice() {
  showAuthChoice.value = false
  loginOpen.value = true
}

function handleAuthSuccess(message) {
  loginOpen.value = false
  toastMessage.value = message
  showToast.value = true
  
  router.push({ name: 'Booking' })
}
</script>

<template>
    <section id="barbers" class="bg-white relative"> <div v-if="barbers.length === 0" class="text-center py-16">
            <p>Loading barbers...</p>
        </div>

        <div v-for="(barber, index) in barbers" :key="barber.id" :class="['py-16 px-4 sm:px-6 lg:px-8 flex justify-center', index % 2 === 1 ? 'bg-black text-white' : 'bg-white text-black']">
            <div class="max-w-4xl w-full flex flex-col md:flex-row items-center md:items-start gap-8">
                <div class="w-64 h-64 bg-gray-300 rounded-lg overflow-hidden flex-shrink-0">
                    <img :src="getImageSrc(barber)" :alt="barber.name" class="w-full h-full object-cover" @error="onImageError">
                </div>
                <div class="flex flex-col text-left space-y-4">
                    <h2 class="text-4xl font-bold">{{ barber.name }}</h2>
                    <p class="text-accent font-bold uppercase tracking-tight text-sm">
                        Professional Barber
                    </p>
                    <p class="max-w-md opacity-90 leading-relaxed">
                        {{ barber.bio || 'No description available for this barber.' }}
                    </p>
                    
                    <div class="pt-4">
                        <Button class="px-7 py-7 text-lg" @click="handleBookingClick">
                            Book your appointment
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <AuthChoiceModal
            v-if="showAuthChoice"
            @guest="continueAsGuest"
            @login="handleLoginChoice"
            @close="showAuthChoice = false"
        />

        <AuthModal
            v-if="loginOpen"
            @close="loginOpen = false"
            @success="handleAuthSuccess"
        />

        <Toast
            v-if="showToast"
            :message="toastMessage"
            @close="showToast = false"
        />

    </section>
</template>
