<script setup>
import { cn } from "@/lib/utils";

const props = defineProps({
  class: { type: null, required: false },
  to: {type: String, required:false},
});
</script>

<template>
  <router-link v-if="props.to" :to="props.to" class="block">
  <div
    data-slot="card"
    :class="
      cn(
        'bg-background text-card-foreground flex flex-col gap-6 rounded-xl py-6',
        props.class,
      )
    "
  >
    <slot />
  </div>
  </router-link>
  <div v-else
    data-slot="card"
    :class="
      cn(
        'bg-background text-card-foreground flex flex-col gap-6 rounded-xl py-6',
        props.class,
      )
    "
  >
    <slot />
  </div>
</template>
