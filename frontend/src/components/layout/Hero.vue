<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@components/ui/button'
import AuthChoiceModal from '@/components/modals/AuthChoiceModal.vue'
import AuthModal from '@/components/auth/AuthModal.vue'
import Toast from '@/components/ui/Toast.vue'
import { useAuthStore } from '@stores/AuthStore.js'

const router = useRouter()
const auth = useAuthStore()

const showAuthChoice = ref(false)
const loginOpen = ref(false)
const showToast = ref(false)
const toastMessage = ref('')

const isLoggedIn = computed(() => auth.isLoggedIn)

watch(isLoggedIn, (loggedIn) => {
  if (!loggedIn) {
    showAuthChoice.value = false
    loginOpen.value = false
  }
})

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

function handleAuthSuccess({ token, user_id, message }) {
  auth.setToken(token)
  auth.setUser(user_id)

  showAuthChoice.value = false
  loginOpen.value = false

  toastMessage.value = message
  showToast.value = true

  router.push('/booking')
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
      @login="loginOpen = true"
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
