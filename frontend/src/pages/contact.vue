<script setup>
import BaseHeader from '@components/layout/BaseHeader.vue'
import ContactLayout from '@components/layout/ContactLayout.vue'
import Footer from '@components/layout/Footer.vue'
</script>

<template>
  <div class="flex min-h-screen flex-col bg-background text-foreground">
    <BaseHeader variant="background" />
    <main class="flex-grow">
      <ContactLayout />
    </main>
    <Footer variant="background" />
  </div>
</template>
