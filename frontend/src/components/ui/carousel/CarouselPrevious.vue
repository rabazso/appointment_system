<script setup>
import { computed } from 'vue'
import { ArrowLeft } from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import { Button } from '@components/ui/button'
import { useCarousel } from './useCarousel'

const props = defineProps({
  variant: { type: String, required: false, default: 'outline' },
  size: { type: String, required: false, default: 'icon' },
  wrapAround: { type: Boolean, required: false, default: false },
  class: { type: null, required: false },
})

const { orientation, canScrollPrev, scrollPrev, carouselApi } = useCarousel()
const isDisabled = computed(() => !canScrollPrev.value && !props.wrapAround)

function handlePrevClick() {
  if (canScrollPrev.value) {
    scrollPrev()
    return
  }

  if (!props.wrapAround || !carouselApi.value) return
  const lastIndex = carouselApi.value.scrollSnapList().length - 1
  if (lastIndex >= 0) {
    carouselApi.value.scrollTo(lastIndex)
  }
}
</script>

<template>
  <Button
    data-slot="carousel-previous"
    :disabled="isDisabled"
    :class="cn(
      'absolute size-8 rounded-full',
      orientation === 'horizontal'
        ? 'top-1/2 -left-12 -translate-y-1/2'
        : '-top-12 left-1/2 -translate-x-1/2 rotate-90',
      props.class,
    )"
    :variant="props.variant"
    :size="props.size"
    @click="handlePrevClick"
  >
    <slot>
      <ArrowLeft />
      <span class="sr-only">Previous Slide</span>
    </slot>
  </Button>
</template>
