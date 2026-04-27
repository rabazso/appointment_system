<script setup>
import { Button } from '@components/ui/button';
import {Star} from "lucide-vue-next"
import {Card, CardContent, CardTitle, CardDescription} from '@components/ui/card'
import {ref, onMounted, computed, watch} from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getReviews, getUserAppointments, postReview } from '@/api/index';
import LeaveReviewModal from '@/components/modals/LeaveReviewModal.vue'
import AuthModal from '@/components/auth/AuthModal.vue'
import { useAuthStore } from '@stores/AuthStore.js'
import { useToastStore } from '@/stores/ToastStore.js'

const reviews = ref([])
const showLeaveReviewModal = ref(false)
const loginOpen = ref(false)
const reviewAppointments = ref([])
const reviewSubmitting = ref(false)
const reviewError = ref('')
const preselectedAppointmentId = ref('')
const auth = useAuthStore()
const toast = useToastStore()
const route = useRoute()
const router = useRouter()
const isLoggedIn = computed(() => auth.isLoggedIn)

onMounted(async ()=>{
    await loadReviews()

    if (isReviewPromptRequested()) {
        const requestedAppointmentId = route.query.appointment_id
        await openReviewModal(requestedAppointmentId ? String(requestedAppointmentId) : '')
        await clearReviewPromptQuery()
    }
})

function extractReviews(response) {
    const reviewList = response?.data?.data || []
    return reviewList
        .slice()
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 3)
}

async function loadReviews() {
    try {
        const response = await getReviews()
        reviews.value = extractReviews(response)
    } catch (error) {
        reviews.value = []
        toast.showError('Failed to load reviews.')
    }
}

async function loadReviewAppointments() {
    try {
        const response = await getUserAppointments()
        reviewAppointments.value = (response.data || [])
            .filter((appointment) => appointment.display_status === 'completed')
            .sort((a, b) => new Date(b.start_datetime) - new Date(a.start_datetime))
    } catch (error) {
        reviewAppointments.value = []
        reviewError.value = error.response?.data?.message || 'Could not load your appointments'
        toast.showError('Failed to load your appointments.')
    }
}

function isReviewPromptRequested() {
    const flag = String(route.query.openReview || '').toLowerCase()
    return flag === '1' || flag === 'true' || flag === 'yes'
}

async function clearReviewPromptQuery() {
    const nextQuery = { ...route.query }
    delete nextQuery.openReview
    delete nextQuery.appointment_id

    try {
        await router.replace({ query: nextQuery, hash: route.hash })
    } catch {
        // no-op
    }
}

async function openReviewModal(preferredAppointmentId = '') {
    preselectedAppointmentId.value = preferredAppointmentId ? String(preferredAppointmentId) : ''

    if (isLoggedIn.value) {
        reviewError.value = ''
        await loadReviewAppointments()
        showLeaveReviewModal.value = true
        return
    }

    loginOpen.value = true
}

function closeReviewModal() {
    showLeaveReviewModal.value = false
    preselectedAppointmentId.value = ''
}

function handleAuthSuccess() {
    loginOpen.value = false
    openReviewModal(preselectedAppointmentId.value)
}

async function handleReviewSubmit(payload) {
    reviewSubmitting.value = true
    reviewError.value = ''

    try {
        await postReview(payload)
        await loadReviews()
        showLeaveReviewModal.value = false
        preselectedAppointmentId.value = ''
    } catch (error) {
        reviewError.value =
            error.response?.data?.errors?.appointment_id?.[0] ||
            error.response?.data?.errors?.rating?.[0] ||
            error.response?.data?.errors?.comment?.[0] ||
            error.response?.data?.message ||
            'Could not submit your review'
        toast.showError('Failed to submit review.')
    } finally {
        reviewSubmitting.value = false
    }
}

watch(isLoggedIn, (loggedIn) => {
    if (!loggedIn) {
        showLeaveReviewModal.value = false
        reviewAppointments.value = []
        reviewError.value = ''
        preselectedAppointmentId.value = ''
    }
})
</script>
<template>
<section id="reviews" class="py-15 bg-background scroll-mt-15">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold mb-4">Customer feedback</h2>
            <p class="text-lg text-muted-foreground max-w-2xl mx-auto">We Love Our Customer Reviews! We are so thankful for the amazing and sincere feedback we have received from our customers!</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <Card v-for="review in reviews" :key="review?.id" class="border-2 border-border py-8 px-5 rounded-lg">
                <CardContent class="space-y-1.5 flex flex-col">
                    <CardTitle class="text-lg font-semibold text-primary mb-1">{{ review.customer?.name }}</CardTitle>
                    <CardDescription class="flex items-center gap-1 flex-nowrap"><Star v-for="star in review.rating" :key="star" class="w-4 h-4 text-primary fill-accent"></Star></CardDescription>
                    <CardDescription class="text-accent font-semibold text-xl min-h-6">{{ review.comment || '' }}</CardDescription>
                </CardContent>
            </Card>
        </div>
        <div class="text-center mt-50">
            <Button variant="default" class="text-lg md:text-xl font-bold w-fit px-20 py-8 mt-24" @click="openReviewModal">
                Leave a review
            </Button>
        </div>

        <LeaveReviewModal
            v-if="showLeaveReviewModal"
            :appointments="reviewAppointments"
            :loading="reviewSubmitting"
            :error-message="reviewError"
            :initial-appointment-id="preselectedAppointmentId"
            @close="closeReviewModal"
            @submit="handleReviewSubmit"
        />

        <AuthModal
            v-if="loginOpen"
            @close="loginOpen = false"
            @success="handleAuthSuccess"
        />
    </div>
</section>
</template>
