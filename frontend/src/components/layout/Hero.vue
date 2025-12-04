<template>
  <section class="relative bg-primary text-primary-foreground min-h-screen flex items-center">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-32">
      <div class="max-w-3xl">
        <h1 class="text-5xl md:text-7xl font-extrabold mb-6 text-balance leading-22">
          Book Your Fresh Cut Today
        </h1>
        <p class="text-lg md:text-xl text-primary-foreground/80 mb-8 max-w-2xl leading-relaxed">
          Our expert barbers deliver precise cuts, classic fades, and personalized grooming services designed for the modern man.
        </p>
        <div class="flex flex-col sm:flex-row gap-4">
          <Button to="/booking" class="bg-accent text-accent-foreground px-8 py-4 rounded-lg font-extrabold text-lg hover:opacity-70 transition-opacity shadow-lg">Book now</Button>
          <Button to="/learn-more" class="border-2 border-accent text-accent px-8 py-4 rounded-lg font-bold text-lg hover:bg-accent/10">Learn More</Button>
        </div>
      </div>
    </div>
  </section>
</template>
<script setup>
import Button from "@components/ui/Button.vue"
</script>