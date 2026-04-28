<script setup>
import { ArrowUpDown, Ban, CalendarDays, ChevronLeft, ChevronRight, LayoutGrid, Rows3 } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  viewMode: { type: String, required: true },
  titleText: { type: String, required: true },
  orderBy: { type: String, default: 'newest' },
})

const emit = defineEmits(['update:viewMode', 'update:orderBy', 'prev', 'today', 'next'])

const VIEW_OPTIONS = [
  { mode: 'cancelled', label: 'Cancelled', icon: Ban },
  { mode: 'daily', label: 'Daily', icon: Rows3 },
  { mode: 'weekly', label: 'Weekly', icon: CalendarDays },
  { mode: 'monthly', label: 'Monthly', icon: LayoutGrid },
]

const ORDER_OPTIONS = [
  { value: 'newest', label: 'Latest first' },
  { value: 'oldest', label: 'Earliest first' },
]

const isActive = (mode) => props.viewMode === mode

const updateOrderBy = (value) => {
  if (!value) {
    return
  }

  emit('update:orderBy', value)
}
</script>

<template>
  <div class="border-b border-black/5 bg-white/80 px-3 py-3.5 backdrop-blur sm:px-5">
    <div class="flex min-w-0 flex-col gap-3 lg:flex-row lg:items-center lg:justify-end">
      <div class="flex min-w-0 flex-col gap-3 md:flex-row md:items-center lg:justify-end">
        <div class="flex w-full items-center rounded-2xl border border-black/10 bg-white p-1 sm:grid sm:w-auto sm:grid-cols-4">
          <Button v-for="option in VIEW_OPTIONS" :key="option.mode" type="button" variant="ghost"
            class="min-w-0 rounded-xl py-2 text-xs font-semibold shadow-none transition sm:px-4 sm:text-sm [&_p]:min-w-0"
            :class="isActive(option.mode) ? 'bg-accent text-slate-900 px-2.5 flex-[1.8] sm:flex-none'
              : 'px-1.5 flex-[0.75] sm:flex-none'"
            @click="emit('update:viewMode', option.mode)">
            <span class="inline-flex min-w-0 items-center justify-center gap-1.5 sm:gap-2">
              <component :is="option.icon" class="h-4 w-4" />
              <span class="truncate" :class="isActive(option.mode) ? 'inline font-semibold' : 'hidden sm:inline'">
                {{ option.label }}
              </span>
            </span>
          </Button>
        </div>

        <div
          v-if="props.viewMode !== 'cancelled'"
          class="grid w-full grid-cols-[2.5rem_minmax(0,1fr)_2.5rem] items-center gap-2 rounded-2xl border border-black/5 bg-white px-2 py-2 sm:min-h-[3.5rem] sm:w-auto sm:min-w-[22rem] sm:grid-cols-[2.5rem_minmax(10rem,1fr)_auto_2.5rem] sm:px-3">
          <Button type="button" variant="ghost" size="icon-lg" 
            class="rounded-xl text-slate-500 shadow-none hover:bg-slate-100 hover:text-slate-900"
            @click="emit('prev')">
            <ChevronLeft class="h-4 w-4" />
          </Button>
          <div class="min-w-0 truncate text-center text-sm font-semibold text-slate-800">
            {{ props.titleText }}
          </div>
          <Button type="button" variant="ghost" size="icon-lg"
            class="rounded-xl text-slate-500 shadow-none hover:bg-slate-100 hover:text-slate-900 sm:order-4"
            @click="emit('next')">
            <ChevronRight class="h-4 w-4" />
          </Button>
          <Button type="button" variant="ghost"
            class="col-span-3 rounded-xl px-3 py-2 text-sm font-semibold text-slate-600 shadow-none hover:bg-slate-100 hover:text-slate-900 sm:order-3 sm:col-span-1"
            @click="emit('today')">
            Today
          </Button>
        </div>

        <div v-else
          class="flex w-full items-center gap-2 rounded-2xl border border-black/5 bg-white px-3 py-2 sm:min-h-[3.5rem] sm:w-auto sm:min-w-[22rem]">
          <ArrowUpDown class="ml-2 h-4 w-4 shrink-0 text-slate-500" />
          <span class="text-sm font-semibold text-slate-700">Order by</span>
          <Select :model-value="props.orderBy" @update:model-value="updateOrderBy">
            <SelectTrigger
              class="h-8 min-w-0 flex-1 border-0 bg-transparent px-0 py-0 text-sm font-medium text-slate-700 shadow-none focus-visible:ring-0"
            >
              <SelectValue placeholder="Order" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="option in ORDER_OPTIONS" :key="option.value" :value="option.value">
                {{ option.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
    </div>
  </div>
</template>
