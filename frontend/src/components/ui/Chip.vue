<script setup>
import { computed } from 'vue'
import { cva } from 'class-variance-authority'
import { cn } from '../../lib/cn'

const props = defineProps({
  variant: { type: String, default: 'solid' }, // solid | tint | outline
  status: { type: String, default: 'default' }, // default | primary | secondary | error | warning | success
  size: { type: String, default: 'md' }, // sm | md
})

// Outline memakai inset box-shadow, bukan border asli, supaya tinggi chip
// tidak bertambah 2px dibanding varian solid/tint.
const chipVariants = cva('inline-flex items-center rounded-full', {
  variants: {
    variant: { solid: '', tint: '', outline: '' },
    status: { default: '', primary: '', secondary: '', error: '', warning: '', success: '' },
    size: { sm: 'px-1 py-[3px]', md: 'p-1' },
  },
  compoundVariants: [
    { variant: 'solid', status: 'default', class: 'bg-neutral-600 text-neutral-900' },
    { variant: 'solid', status: 'primary', class: 'bg-primary-400 text-neutral-300' },
    { variant: 'solid', status: 'secondary', class: 'bg-secondary-400 text-neutral-300' },
    { variant: 'solid', status: 'error', class: 'bg-red-100 text-neutral-300' },
    { variant: 'solid', status: 'warning', class: 'bg-yellow-200 text-neutral-300' },
    { variant: 'solid', status: 'success', class: 'bg-green-200 text-neutral-300' },

    { variant: 'tint', status: 'default', class: 'bg-neutral-600 text-neutral-900' },
    { variant: 'tint', status: 'primary', class: 'bg-primary-400/10 text-primary-400' },
    { variant: 'tint', status: 'secondary', class: 'bg-secondary-400/10 text-secondary-400' },
    { variant: 'tint', status: 'error', class: 'bg-red-100/10 text-red-100' },
    { variant: 'tint', status: 'warning', class: 'bg-yellow-200/10 text-yellow-200' },
    { variant: 'tint', status: 'success', class: 'bg-green-200/10 text-green-200' },

    { variant: 'outline', status: 'default', class: 'text-neutral-900 shadow-[inset_0_0_0_1px_var(--color-neutral-700)]' },
    { variant: 'outline', status: 'primary', class: 'text-primary-400 shadow-[inset_0_0_0_1px_var(--color-primary-400)]' },
    { variant: 'outline', status: 'secondary', class: 'text-secondary-400 shadow-[inset_0_0_0_1px_var(--color-secondary-400)]' },
    { variant: 'outline', status: 'error', class: 'text-red-100 shadow-[inset_0_0_0_1px_var(--color-red-100)]' },
    { variant: 'outline', status: 'warning', class: 'text-yellow-200 shadow-[inset_0_0_0_1px_var(--color-yellow-200)]' },
    { variant: 'outline', status: 'success', class: 'text-green-200 shadow-[inset_0_0_0_1px_var(--color-green-200)]' },
  ],
  defaultVariants: { variant: 'solid', status: 'default', size: 'md' },
})

const textPaddingBySize = { sm: 'px-1.5', md: 'px-1.5 py-[3px]' }

const classes = computed(() =>
  cn(chipVariants({ variant: props.variant, status: props.status, size: props.size })),
)
</script>

<template>
  <span :class="classes">
    <span :class="cn('text-b4 whitespace-nowrap', textPaddingBySize[size])"><slot /></span>
  </span>
</template>
