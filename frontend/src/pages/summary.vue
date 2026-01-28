<script setup>
    import BaseHeader from '@components/layout/BaseHeader.vue';
    import Button from '@components/ui/button/Button.vue';
</script>
<template>
    <BaseHeader variant="background"></BaseHeader>
    <div class="min-h-screen bg-background mx-auto max-w-4xl px-4 py-8 md:py-16">
         <div class="mb-8 text-center">
            <h1 class="mb-2 text-4xl font-bold text-foreground md:text-5xl">Your reservation was successful</h1>
        </div>
        <Card class="border-accent/30 bg-accent/10" >
            <CardHeader>
              <CardTitle>Booking Summary</CardTitle>
            </CardHeader>
            <CardContent class="space-y-7">
              <div class="flex justify-between"><p class="text-muted-foreground">Service:</p></div>
              <div class="flex justify-between"><p class="text-muted-foreground">Barber:</p></div>
              <div class="flex justify-between"><p class="text-muted-foreground">Date:</p></div>
              <div class="flex justify-between"><p class="text-muted-foreground">Time:</p></div>
              <div class="flex justify-between"><p class="text-muted-foreground">Total:</p></div>
            </CardContent>
        </Card>
        <Button class="px-7 py-7 text-lg my-30 block mx-auto w-xl md:w-3xl">
            Book your appointment
        </Button>
       
    </div>

</template>