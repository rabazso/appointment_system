<script setup>
import { reactiveOmit } from "@vueuse/core";
import { CalendarCellTrigger, useForwardProps } from "reka-ui";
import { cn } from "@/lib/utils";
import { buttonVariants } from '@/components/ui/button';

const props = defineProps({
  day: { type: null, required: true },
  month: { type: null, required: true },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false, default: "button" },
  class: { type: null, required: false },
  onUnavailableClick: { type: Function, required: false },
});

const delegatedProps = reactiveOmit(props, "class", "onUnavailableClick");

const forwardedProps = useForwardProps(delegatedProps);

function handleClick(event) {
  const target = event?.currentTarget
  const isUnavailable = Boolean(target?.dataset?.unavailable !== undefined)

  if (!isUnavailable || !props.onUnavailableClick) {
    return
  }

  event.preventDefault()
  props.onUnavailableClick(props.day)
}
</script>

<template>
  <CalendarCellTrigger
    data-slot="calendar-cell-trigger"
    :class="
      cn(
        buttonVariants({ variant: 'ghost' }),
        'size-8 p-0 font-normal aria-selected:opacity-100 cursor-default',
        '[&[data-today]:not([data-selected])]:bg-primary [&[data-today]:not([data-selected])]:text-primary-foreground',
        // Selected
        'data-[selected]:bg-accent data-[selected]:text-accent-foreground data-[selected]:opacity-100 data-[selected]:hover:bg-accent data-[selected]:hover:text-accent-foreground data-[selected]:focus:bg-accent data-[selected]:focus:text-accent-foreground',
        // Disabled
        'data-[disabled]:text-muted-foreground data-[disabled]:opacity-50',
        // Unavailable
        'data-[unavailable]:text-destructive-foreground data-[unavailable]:line-through',
        // Outside months
        'data-[outside-view]:text-muted-foreground',
        props.class,
      )
    "
    v-bind="forwardedProps"
    @click="handleClick"
  >
    <slot />
  </CalendarCellTrigger>
</template>
