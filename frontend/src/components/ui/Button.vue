<script setup>
import { computed } from 'vue'
import { cva } from 'class-variance-authority'
import { cn } from '../../lib/cn'

const props = defineProps({
  theme: { type: String, default: 'primary' }, // primary | secondary | neutral | error
  variant: { type: String, default: 'solid' }, // solid | outline | ghost | link
  size: { type: String, default: 'md' }, // sm | md | lg
  type: { type: String, default: 'button' },
})

const buttonVariants = cva(
  'inline-flex shrink-0 items-center justify-center gap-2 whitespace-nowrap font-bold transition-colors ' +
    'cursor-pointer disabled:pointer-events-none focus-visible:outline-none',
  {
    variants: {
      theme: { primary: '', secondary: '', neutral: '', error: '' },
      variant: {
        solid: 'border-0 rounded-2',
        outline: 'border border-solid rounded-2 bg-transparent',
        ghost: 'rounded-2 bg-transparent border-0',
        link: 'bg-transparent border-0 p-0 rounded-none',
      },
      size: {
        sm: 'text-xs leading-[18px]',
        md: 'text-sm leading-[21px]',
        lg: 'text-sm leading-[21px]',
      },
    },
    compoundVariants: [
      { variant: ['solid', 'outline', 'ghost'], size: 'sm', class: 'p-2' },
      { variant: ['solid', 'outline', 'ghost'], size: 'md', class: 'p-3' },
      { variant: ['solid', 'outline', 'ghost'], size: 'lg', class: 'p-4 rounded-3' },
      { variant: 'link', size: 'sm', class: 'h-9 gap-1' },
      { variant: 'link', size: 'md', class: 'h-11 gap-1' },
      { variant: 'link', size: 'lg', class: 'h-[52px] gap-1' },

      {
        theme: 'primary', variant: 'solid',
        class:
          'bg-primary-400 border-primary-500 text-white hover:bg-primary-500 hover:border-primary-500 ' +
          'focus-visible:ring-4 focus-visible:ring-primary-400/10 ' +
          'disabled:bg-disabled-fill disabled:border-transparent disabled:text-primary-400/10',
      },
      {
        theme: 'primary', variant: 'outline',
        class:
          'border-primary-500 text-primary-400 hover:bg-primary-400/10 ' +
          'focus-visible:ring-4 focus-visible:ring-primary-400/10 ' +
          'disabled:border-disabled-stroke disabled:text-primary-400/10',
      },
      {
        theme: 'primary', variant: 'ghost',
        class:
          'text-primary-400 hover:bg-primary-400/10 focus-visible:ring-4 focus-visible:ring-primary-400/10 ' +
          'disabled:text-primary-400/10',
      },
      { theme: 'primary', variant: 'link', class: 'text-primary-400 hover:underline disabled:text-primary-400/10' },

      {
        theme: 'secondary', variant: 'solid',
        class:
          'bg-secondary-400 border-secondary-500 text-white hover:bg-secondary-500 hover:border-secondary-500 ' +
          'focus-visible:ring-4 focus-visible:ring-secondary-400/10 ' +
          'disabled:bg-disabled-fill disabled:border-transparent disabled:text-secondary-400/10',
      },
      {
        theme: 'secondary', variant: 'outline',
        class:
          'border-secondary-500 text-secondary-400 hover:bg-secondary-400/10 ' +
          'focus-visible:ring-4 focus-visible:ring-secondary-400/10 ' +
          'disabled:border-disabled-stroke disabled:text-secondary-400/10',
      },
      {
        theme: 'secondary', variant: 'ghost',
        class:
          'text-secondary-400 hover:bg-secondary-400/10 focus-visible:ring-4 focus-visible:ring-secondary-400/10 ' +
          'disabled:text-secondary-400/10',
      },
      { theme: 'secondary', variant: 'link', class: 'text-secondary-400 hover:underline disabled:text-secondary-400/10' },

      {
        theme: 'neutral', variant: 'solid',
        class:
          'border border-solid bg-white border-border-default text-neutral-800 hover:bg-disabled-fill ' +
          'focus-visible:ring-4 focus-visible:ring-neutral-800/10 ' +
          'disabled:bg-disabled-fill disabled:border-disabled-stroke disabled:text-neutral-800/30',
      },
      {
        theme: 'neutral', variant: 'outline',
        class:
          'border-border-default text-neutral-800 hover:bg-disabled-fill ' +
          'focus-visible:ring-4 focus-visible:ring-neutral-800/10 ' +
          'disabled:border-disabled-stroke disabled:text-neutral-800/30',
      },
      {
        theme: 'neutral', variant: 'ghost',
        class:
          'text-neutral-800 hover:bg-disabled-fill focus-visible:ring-4 focus-visible:ring-neutral-800/10 ' +
          'disabled:text-neutral-800/30',
      },
      { theme: 'neutral', variant: 'link', class: 'text-neutral-800 hover:underline disabled:text-neutral-800/30' },

      {
        theme: 'error', variant: 'solid',
        class:
          'bg-red-100 border-red-200 text-white hover:bg-red-200 hover:border-red-200 ' +
          'focus-visible:ring-4 focus-visible:ring-red-100/10 ' +
          'disabled:bg-disabled-fill disabled:border-transparent disabled:text-red-100/10',
      },
      {
        theme: 'error', variant: 'outline',
        class:
          'border-red-100 text-red-100 hover:bg-red-100/10 focus-visible:ring-4 focus-visible:ring-red-100/10 ' +
          'disabled:border-disabled-stroke disabled:text-red-100/10',
      },
      {
        theme: 'error', variant: 'ghost',
        class:
          'text-red-100 hover:bg-red-100/10 focus-visible:ring-4 focus-visible:ring-red-100/10 ' +
          'disabled:text-red-100/10',
      },
      { theme: 'error', variant: 'link', class: 'text-red-100 hover:underline disabled:text-red-100/10' },
    ],
    defaultVariants: { theme: 'primary', variant: 'solid', size: 'md' },
  },
)

const classes = computed(() =>
  cn(buttonVariants({ theme: props.theme, variant: props.variant, size: props.size })),
)
</script>

<template>
  <button :type="type" :class="classes">
    <span v-if="$slots['icon-left']" class="size-5 shrink-0 [&>svg]:size-full">
      <slot name="icon-left" />
    </span>
    <slot />
    <span v-if="$slots['icon-right']" class="size-5 shrink-0 [&>svg]:size-full">
      <slot name="icon-right" />
    </span>
  </button>
</template>
