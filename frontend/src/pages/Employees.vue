<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { getEmployees } from '@/api/index'
import { useAuthStore } from '@/stores/AuthStore'
import { useToastStore } from '@/stores/ToastStore.js'

import BaseHeader from '@components/layout/BaseHeader.vue'
import BarberPageHero from '@components/layout/BarberPageHero.vue'
import Footer from '@components/layout/Footer.vue'
import AuthChoiceModal from '@/components/modals/AuthChoiceModal.vue'
import AuthModal from '@/components/auth/AuthModal.vue'
import Toast from '@/components/ui/Toast.vue'

const router = useRouter()
const authStore = useAuthStore()
const globalToast = useToastStore()

const barbers = ref([])
const showAuthChoice = ref(false)
const loginOpen = ref(false)
const showToast = ref(false)
const toastMessage = ref('')

const isLoggedIn = computed(() => authStore.isCustomerLoggedIn)

function onImageError(event) {
  event.target.src = '/images/barber_placeholder.png'
}

onMounted(async () => {
  try {
    const response = await getEmployees()
    barbers.value = response.data.data
  } catch (error) {
    globalToast.showError('Failed to load barbers.')
  }
})

function openBarberProfile(barberId) {
  router.push({ name: 'EmployeeDetails', params: { id: barberId } })
}

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
  <BaseHeader />
  <BarberPageHero />

  <section id="barbers" class="relative bg-white">
    <div
      v-for="(barber, index) in barbers"
      :key="barber.id"
      :class="[
        'flex justify-center px-4 py-12 sm:px-6 sm:py-16 lg:px-8',
        index % 2 === 1 ? 'bg-primary text-white' : 'bg-background text-black',
      ]"
    >
      <div class="flex w-full max-w-4xl flex-col items-center gap-8 md:flex-row md:items-start">
        <div class="h-64 w-full max-w-64 flex-shrink-0 overflow-hidden rounded-lg bg-gray-300">
          <img
            :src="barber.profile_image?.preview_url ?? '/images/barber_placeholder.png'"
            :alt="barber.name"
            class="h-full w-full object-cover"
            @error="onImageError"
          >
        </div>
        <div class="flex w-full min-w-0 flex-col space-y-4 text-left">
          <h2 class="break-words text-3xl font-bold sm:text-4xl">{{ barber.name }}</h2>
          <p class="text-accent text-sm font-bold uppercase tracking-tight">
            Professional Barber
          </p>
          <p class="line-clamp-3 max-w-md leading-relaxed opacity-90">
            {{ barber.bio || 'No description available for this barber.' }}
          </p>

          <div class="flex w-full flex-col gap-3 pt-4 sm:w-auto sm:flex-row sm:items-center">
            <Button
              class="w-full px-5 py-5 text-base sm:w-auto sm:px-7 sm:py-7 sm:text-lg"
              variant="outline"
              @click="openBarberProfile(barber.id)"
            >
              View profile
            </Button>
            <Button
              class="w-full px-5 py-5 text-base sm:w-auto sm:px-7 sm:py-7 sm:text-lg"
              @click="handleBookingClick"
            >
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

  <Footer />
</template>
