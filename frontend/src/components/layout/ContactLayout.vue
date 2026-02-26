<script setup>
import { Clock3, Mail, MapPin, Phone } from 'lucide-vue-next'

const address = 'Budapest, Clark Ádám tér'
const encodedAddress = encodeURIComponent(address)
const mapURL = `https://maps.google.com/maps?q=${encodedAddress}&t=&z=15&ie=UTF8&iwloc=&output=embed`
</script>

<template>
  <section class="mx-auto max-w-6xl px-4 py-8 md:py-16">
    <div class="mb-8 text-center md:mb-12">
      <h1 class="mb-3 text-4xl font-bold text-foreground md:text-5xl">Contact Us</h1>
      <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
        Have a question, need help with a booking, or want to reach the shop directly? We are here to help.
      </p>
    </div>

    <div class="grid gap-8 lg:grid-cols-2">
      <div class="rounded-xl border bg-background p-6 shadow-sm md:p-8">
        <h2 class="mb-6 text-2xl font-semibold text-foreground">Get In Touch</h2>

        <div class="space-y-6">
          <div class="flex items-start gap-4">
            <div class="rounded-lg bg-accent/20 p-2">
              <MapPin class="h-5 w-5 text-primary" />
            </div>
            <div>
              <p class="font-semibold text-foreground">Address</p>
              <p class="text-muted-foreground">Budapest 1234 Barber utca 67</p>
            </div>
          </div>

          <div class="flex items-start gap-4">
            <div class="rounded-lg bg-accent/20 p-2">
              <Phone class="h-5 w-5 text-primary" />
            </div>
            <div>
              <p class="font-semibold text-foreground">Phone</p>
              <a href="tel:012345678" class="text-muted-foreground transition-colors hover:text-foreground">
                012345678
              </a>
            </div>
          </div>

          <div class="flex items-start gap-4">
            <div class="rounded-lg bg-accent/20 p-2">
              <Mail class="h-5 w-5 text-primary" />
            </div>
            <div>
              <p class="font-semibold text-foreground">Email</p>
              <a href="mailto:barber@shop.com" class="text-muted-foreground transition-colors hover:text-foreground">
                barber@shop.com
              </a>
            </div>
          </div>

          <div class="flex items-start gap-4">
            <div class="rounded-lg bg-accent/20 p-2">
              <Clock3 class="h-5 w-5 text-primary" />
            </div>
            <div>
              <p class="font-semibold text-foreground">Opening Hours</p>
              <p class="text-muted-foreground">Mon-Fri: 9am-8pm</p>
              <p class="text-muted-foreground">Sat: 10am-5pm</p>
              <p class="text-muted-foreground">Sun: Closed</p>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-xl border bg-background p-2 shadow-sm">
        <iframe
          :src="mapURL"
          title="Barbershop location map"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          class="h-[360px] w-full rounded-lg md:h-[420px]"
        ></iframe>
      </div>
    </div>
  </section>
</template>
