<script setup>
import { computed } from 'vue'
import { ArrowRight } from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import { Button } from '@components/ui/button'
import { useCarousel } from './useCarousel'

const props = defineProps({
  variant: { type: String, required: false, default: 'outline' },
  size: { type: String, required: false, default: 'icon' },
  wrapAround: { type: Boolean, required: false, default: false },
  class: { type: null, required: false },
})

const { orientation, canScrollNext, scrollNext, carouselApi } = useCarousel()
const isDisabled = computed(() => !canScrollNext.value && !props.wrapAround)

function handleNextClick() {
  if (canScrollNext.value) {
    scrollNext()
    return
  }

  if (!props.wrapAround || !carouselApi.value) return
  carouselApi.value.scrollTo(0)
}
</script>

<template>
  <Button
    data-slot="carousel-next"
    :disabled="isDisabled"
    :class="cn(
      'absolute size-8 rounded-full',
      orientation === 'horizontal'
        ? 'top-1/2 -right-12 -translate-y-1/2'
        : '-bottom-12 left-1/2 -translate-x-1/2 rotate-90',
      props.class,
    )"
    :variant="props.variant"
    :size="props.size"
    @click="handleNextClick"
  >
    <slot>
      <ArrowRight />
      <span class="sr-only">Next Slide</span>
    </slot>
  </Button>
</template>
