import { createInjectionState } from '@vueuse/core'
import emblaCarouselVue from 'embla-carousel-vue'
import { onMounted, onUnmounted, ref } from 'vue'

const [useProvideCarousel, useInjectCarousel] = createInjectionState((props, emits) => {
  const [emblaNode, emblaApi] = emblaCarouselVue(
    {
      ...(props.opts ?? {}),
      axis: props.orientation === 'vertical' ? 'y' : 'x',
    },
    props.plugins ?? [],
  )

  function scrollPrev() {
    emblaApi.value?.scrollPrev()
  }

  function scrollNext() {
    emblaApi.value?.scrollNext()
  }

  const canScrollNext = ref(false)
  const canScrollPrev = ref(false)

  function onSelect(api) {
    canScrollNext.value = api?.canScrollNext() || false
    canScrollPrev.value = api?.canScrollPrev() || false
  }

  onMounted(() => {
    if (!emblaApi.value) return

    emblaApi.value.on('init', onSelect)
    emblaApi.value.on('reInit', onSelect)
    emblaApi.value.on('select', onSelect)
    onSelect(emblaApi.value)

    emits('init-api', emblaApi.value)
  })

  onUnmounted(() => {
    if (!emblaApi.value) return
    emblaApi.value.off('init', onSelect)
    emblaApi.value.off('reInit', onSelect)
    emblaApi.value.off('select', onSelect)
  })

  return {
    carouselRef: emblaNode,
    carouselApi: emblaApi,
    canScrollPrev,
    canScrollNext,
    scrollPrev,
    scrollNext,
    orientation: props.orientation ?? 'horizontal',
  }
})

function useCarousel() {
  const carouselState = useInjectCarousel()

  if (!carouselState) {
    throw new Error('useCarousel must be used within a <Carousel />')
  }

  return carouselState
}

export { useCarousel, useProvideCarousel }

