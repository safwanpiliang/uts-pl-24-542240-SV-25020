<script setup>
import { computed } from 'vue'
import { cva } from 'class-variance-authority'
import { cn } from '../../lib/cn'

const props = defineProps({
  variant: { type: String, default: 'outline' }, // filled | outline
  status: { type: String, default: 'default' }, // default | secondary | success | warning | error
  title: { type: String, default: '' },
  description: { type: String, default: '' },
})

const alertVariants = cva('flex items-start gap-4 rounded-3 p-4', {
  variants: {
    variant: { filled: '', outline: 'border-[1.5px] border-solid' },
    status: { default: '', secondary: '', success: '', warning: '', error: '' },
  },
  compoundVariants: [
    { variant: 'filled', status: 'default', class: 'bg-primary-400' },
    { variant: 'outline', status: 'default', class: 'border-primary-500 bg-primary-400/10' },
    { variant: 'filled', status: 'secondary', class: 'bg-secondary-400' },
    { variant: 'outline', status: 'secondary', class: 'border-secondary-500 bg-secondary-400/10' },
    { variant: 'filled', status: 'success', class: 'bg-green-200' },
    { variant: 'outline', status: 'success', class: 'border-green-200 bg-green-200/10' },
    { variant: 'filled', status: 'warning', class: 'bg-yellow-200' },
    { variant: 'outline', status: 'warning', class: 'border-yellow-200 bg-yellow-200/10' },
    { variant: 'filled', status: 'error', class: 'bg-red-200' },
    { variant: 'outline', status: 'error', class: 'border-red-200 bg-red-100/10' },
  ],
  defaultVariants: { variant: 'outline', status: 'default' },
})

const outlineAccentText = {
  default: 'text-primary-400',
  secondary: 'text-secondary-400',
  success: 'text-green-200',
  warning: 'text-yellow-200',
  error: 'text-red-200',
}

const isFilled = computed(() => props.variant === 'filled')
const classes = computed(() => cn(alertVariants({ variant: props.variant, status: props.status })))
const iconClass = computed(() =>
  cn('size-6 shrink-0 [&>svg]:size-full', isFilled.value ? 'text-white' : outlineAccentText[props.status]),
)
</script>

<template>
  <div role="alert" :class="classes">
    <span v-if="$slots.icon" :class="iconClass"><slot name="icon" /></span>
    <div class="flex min-w-0 flex-1 flex-col items-start gap-4">
      <div class="flex w-full flex-col items-start gap-1">
        <p v-if="title" :class="cn('text-b2 w-full font-bold', isFilled ? 'text-white' : 'text-neutral-1000')">
          {{ title }}
        </p>
        <p v-if="description" :class="cn('text-b3 w-full font-normal', isFilled ? 'text-neutral-300' : 'text-neutral-800')">
          {{ description }}
        </p>
      </div>
      <div v-if="$slots.actions" class="flex items-center gap-4"><slot name="actions" /></div>
    </div>
  </div>
</template>
