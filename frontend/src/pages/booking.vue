<script setup>
    import BookingForm from '@components/layout/BookingForm.vue';
    import BaseHeader from '@components/layout/BaseHeader.vue';
</script>
<template>
    <BaseHeader variant="background"></BaseHeader>
    <div class="min-h-screen bg-background mx-auto max-w-4xl px-4 py-8">
        <div class="mb-8 text-center">
            <h1 class="mb-2 text-4xl font-bold text-foreground">Book Your Appointment</h1>
            <p class="text-lg text-muted-foreground">Choose your preferred date, barber, and service</p>
        </div>
        <BookingForm></BookingForm>
    </div>
</template>