<script setup>
import { computed } from 'vue'
import { cn } from '../../lib/cn'

const props = defineProps({
  value: { type: [String, Number], required: true },
  label: { type: String, required: true },
  variant: { type: String, default: 'neutral' }, // neutral | color
  status: { type: String, default: 'secondary' }, // primary | secondary | error | warning | success
})

const statusBg = {
  primary: 'bg-primary-400',
  secondary: 'bg-secondary-400',
  error: 'bg-red-100',
  warning: 'bg-yellow-200',
  success: 'bg-green-200',
}

const isColor = computed(() => props.variant === 'color')
</script>

<template>
  <div :class="cn('flex flex-1 items-center gap-[7px] rounded-4 p-2', isColor ? statusBg[status] : 'bg-white')">
    <span :class="cn('size-12 shrink-0 [&>svg]:size-full', isColor ? 'text-white' : 'text-secondary-400')">
      <slot name="icon" />
    </span>
    <div v-if="isColor" class="flex flex-col gap-0.5">
      <p class="text-b4 text-neutral-300">{{ label }}</p>
      <p class="text-b1 font-bold text-white">{{ value }}</p>
    </div>
    <div v-else class="flex flex-col gap-0.5">
      <p class="text-b1 font-bold text-neutral-900">{{ value }}</p>
      <p class="text-b4 text-neutral-800">{{ label }}</p>
    </div>
  </div>
</template>
