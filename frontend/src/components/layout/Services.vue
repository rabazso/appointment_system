<script setup>
import { Button } from '@components/ui/button'
import { getServices } from '@/api/index'
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@stores/AuthStore.js'
import AuthChoiceModal from '@/components/modals/AuthChoiceModal.vue'
import AuthModal from '@/components/auth/AuthModal.vue'
import Toast from '@/components/ui/Toast.vue'
import { useToastStore } from '@/stores/ToastStore.js'

const services = ref([])
const router = useRouter()
const auth = useAuthStore()
const globalToast = useToastStore()

const showAuthChoice = ref(false)
const loginOpen = ref(false)
const showToast = ref(false)
const toastMessage = ref('')

const isLoggedIn = computed(() => auth.isLoggedIn)

onMounted(async () => {
  try {
    services.value = (await getServices()).data
  } catch (error) {
    services.value = []
    globalToast.showError('Failed to load services.')
  }
})

watch(isLoggedIn, (loggedIn) => {
  if (!loggedIn) {
    showAuthChoice.value = false
    loginOpen.value = false
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

function openLoginModal() {
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
  <section id="services" class="bg-primary py-18 text-primary-foreground scroll-mt-15">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mx-auto mb-14 max-w-3xl text-center md:mb-20">
        <h2 class="mb-4 text-4xl font-bold tracking-tight text-primary-foreground md:text-5xl">Our Services</h2>
        <p class="mx-auto max-w-2xl text-card-foreground/70 leading-7 md:text-lg">
          Premium grooming solutions tailored to every man's style and needs
        </p>
      </div>
      <div class="grid grid-cols-1 gap-6 md:grid-cols-2 md:gap-x-6 md:gap-y-7 lg:gap-x-8 lg:gap-y-8">
        <div
          v-for="(service, index) in services"
          :key="service.id"
          :class="index % 2 === 1 ? 'md:translate-y-10' : ''"
        >
          <article
            class="rounded-[1.35rem] border border-white/14 bg-card px-6 py-7 text-left shadow-[0_0_0_1px_rgba(255,255,255,0.02)] transition duration-300 hover:border-accent/55 hover:shadow-[0_20px_45px_rgba(0,0,0,0.28)] sm:px-8 sm:py-8"
          >
            <h3 class="text-2xl font-semibold tracking-tight text-card-foreground">
              {{ service.name }}
            </h3>
            <p class="mt-3 max-w-xl text-base leading-7 text-card-foreground/72 sm:text-lg">
              {{ service.description }}
            </p>
          </article>
        </div>
      </div>
      <div class="mt-16 text-center">
        <Button class="min-h-14 rounded-2xl p-8 text-lg font-bold md:text-xl" @click="handleBookingClick">
          Book your appointment
        </Button>
      </div>
    </div>
    <AuthChoiceModal
      v-if="showAuthChoice"
      @guest="continueAsGuest"
      @login="openLoginModal"
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
