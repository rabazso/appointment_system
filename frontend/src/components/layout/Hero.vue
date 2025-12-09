<template>
  <section class="relative bg-primary text-primary-foreground min-h-[93vh] flex items-center">
    <div class="relative mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-32 max-w-3xl">
        <h1 class="text-5xl md:text-7xl font-extrabold mb-6 text-balance leading-22">
          Book Your Fresh Cut Today
        </h1>
        <p class="text-lg md:text-xl text-primary-foreground/80 mb-8 max-w-2xl leading-relaxed">
          Our expert barbers deliver precise cuts, classic fades, and personalized grooming services designed for the modern man.
        </p>
        <div class="flex flex-col sm:flex-row gap-4">
          <Button as-child class="px-7 py-7 text-lg" to="/booking">
            Book Now
          </Button>
          <Button as-child variant="outline" class="px-7 py-7 text-lg" to="/learn-more">
            Learn More
          </Button>
        </div>
      </div>
  </section>
</template>
<script setup>
import {Button} from '@components/ui/button'
</script>