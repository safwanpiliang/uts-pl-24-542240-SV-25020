<script setup>
import { computed, useId } from 'vue'
import { cva } from 'class-variance-authority'
import { cn } from '../../lib/cn'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  helperText: { type: String, default: '' },
  size: { type: String, default: 'lg' }, // md | lg
  variant: { type: String, default: 'outline' }, // outline | filled
  status: { type: String, default: 'default' }, // default | success | warning | error
  multiline: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  id: { type: String, default: '' },
})
const emit = defineEmits(['update:modelValue'])

const autoId = useId()
const inputId = computed(() => props.id || autoId)

const wrapperVariants = cva(
  'flex gap-3 border-[1.5px] border-solid transition-colors focus-within:border-primary-400 ' +
    'has-[:disabled]:!border-neutral-400 has-[:disabled]:bg-neutral-200',
  {
    variants: {
      size: { md: 'px-3 py-2 rounded-2', lg: 'p-3 rounded-3' },
      variant: {
        outline: 'bg-transparent border-neutral-500 hover:border-neutral-700',
        filled: 'bg-neutral-200 border-neutral-500 hover:border-neutral-700',
      },
      status: {
        default: '',
        success: 'border-green-200 focus-within:border-green-200',
        warning: 'border-yellow-200 focus-within:border-yellow-200',
        error: 'border-red-200 focus-within:border-red-200',
      },
    },
    defaultVariants: { size: 'lg', variant: 'outline', status: 'default' },
  },
)

const helperTextColor = {
  default: 'text-neutral-700',
  success: 'text-green-200',
  warning: 'text-yellow-200',
  error: 'text-red-200',
}

const wrapperClass = computed(() =>
  cn(
    wrapperVariants({ size: props.size, variant: props.variant, status: props.status }),
    props.multiline ? 'items-start' : 'items-center',
  ),
)
const fieldClass = computed(() =>
  cn(
    'min-w-0 flex-1 bg-transparent outline-none placeholder:text-neutral-700',
    props.size === 'md' ? 'text-b3' : 'text-b2',
    props.multiline && 'resize-none',
    props.disabled ? 'text-neutral-600 placeholder:text-neutral-600' : 'text-neutral-1000',
  ),
)
</script>

<template>
  <div class="flex w-full flex-col gap-2">
    <label
      v-if="label"
      :for="inputId"
      :class="cn('text-b2', disabled ? 'text-neutral-900' : 'text-neutral-1000')"
    >
      {{ label }}
    </label>

    <div :class="wrapperClass">
      <span v-if="$slots['left-icon']" class="size-6 shrink-0 text-neutral-700 [&>svg]:size-full">
        <slot name="left-icon" />
      </span>

      <textarea
        v-if="multiline"
        :id="inputId"
        :value="modelValue"
        :disabled="disabled"
        :class="fieldClass"
        v-bind="$attrs"
        @input="emit('update:modelValue', $event.target.value)"
      />
      <input
        v-else
        :id="inputId"
        :value="modelValue"
        :disabled="disabled"
        :class="fieldClass"
        v-bind="$attrs"
        @input="emit('update:modelValue', $event.target.value)"
      />

      <span v-if="$slots['right-icon']" class="size-6 shrink-0 text-neutral-700 [&>svg]:size-full">
        <slot name="right-icon" />
      </span>
    </div>

    <p
      v-if="helperText"
      :class="cn('text-b3', disabled ? 'text-neutral-600' : helperTextColor[status] ?? helperTextColor.default)"
    >
      {{ helperText }}
    </p>
  </div>
</template>
