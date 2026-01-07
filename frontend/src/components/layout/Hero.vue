<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@components/ui/button'
import AuthChoiceModal from '@/components/modals/AuthChoiceModal.vue'
import AuthModal from '@/components/auth/AuthModal.vue'
import { logout } from '@/api/index'

const router = useRouter()

const token = ref(localStorage.getItem('token'))
const isLoggedIn = computed(() => token.value != null)

const showAuthChoice = ref(false)
const loginOpen = ref(false)
const showToast = ref(false)
const toastMessage = ref('')

function handleBookingClick() {
  if (isLoggedIn.value) {
    router.push('/booking')
  } else {
    showAuthChoice.value = true
  }
}

function continueAsGuest() {
  showAuthChoice.value = false
  router.push('/booking')
}


function handleAuthSuccess(message) {
  token.value = localStorage.getItem('token')
  loginOpen.value = false
  showAuthChoice.value = false
  toastMessage.value = message
  showToast.value = true
}

async function handleLogout() {
  try {
    await logout()
  } catch (error) {
    console.error(error.response?.data?.message || 'Logout failed')
  } finally {
    token.value = null
    localStorage.removeItem('token')
    toastMessage.value = 'You have successfully signed out.'
    showToast.value = true
  }
}
</script>

<template>
  <section id="hero" class="relative bg-primary text-primary-foreground min-h-[93vh] flex items-center">
    <div class="relative mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-32 max-w-3xl">
      <h1 class="text-5xl md:text-7xl font-extrabold mb-6">
        Book Your Fresh Cut Today
      </h1>

      <p class="text-lg md:text-xl text-primary-foreground/80 mb-8">
        Our expert barbers deliver precise cuts and personalized grooming.
      </p>

      <div class="flex flex-col sm:flex-row gap-4">
        <Button class="px-7 py-7 text-lg" @click="handleBookingClick">
          Book Now
        </Button>

        <Button variant="outline" class="px-7 py-7 text-lg" to="/learn-more">
          Learn More
        </Button>

      
      </div>
    </div>

    <AuthChoiceModal
      v-if="showAuthChoice"
      @guest="continueAsGuest"
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
