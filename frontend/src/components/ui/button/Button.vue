<script setup>
import { Primitive } from "reka-ui";
import { cn } from "@/lib/utils";
import { buttonVariants } from ".";
import { useRouter } from "vue-router"
import { router } from "@/router/index";

const props = defineProps({
  variant: { type: null, required: false },
  size: { type: null, required: false },
  class: { type: null, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false, default: "button" },
  to: {type: String, required:false}
});

const handleClick = () => {
  if(props.to){
    router.push(props.to)
  }
}
</script>

<template>
  <Primitive
    data-slot="button"
    :as="as"
    :as-child="asChild"
    :class="cn('cursor-pointer', buttonVariants({ variant, size }), props.class)"
    @click="handleClick"
  >
    <p>
      <slot />
    </p>
  </Primitive>
</template>
