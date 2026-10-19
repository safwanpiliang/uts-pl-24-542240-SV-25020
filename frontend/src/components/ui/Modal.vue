<script setup>
import { nextTick, onBeforeUnmount, ref, watch } from 'vue'
import { cn } from '../../lib/cn'

const props = defineProps({
  open: { type: Boolean, required: true },
  title: { type: String, required: true },
  description: { type: String, default: '' },
  buttonLayout: { type: String, default: 'horizontal' }, // horizontal | vertical
  panelClass: { type: String, default: 'w-[350px]' },
})
const emit = defineEmits(['close'])

const panel = ref(null)

function onKeydown(event) {
  if (event.key === 'Escape') emit('close')
}

watch(
  () => props.open,
  async (isOpen) => {
    if (isOpen) {
      document.body.style.overflow = 'hidden'
      document.addEventListener('keydown', onKeydown)
      await nextTick()
      panel.value?.querySelector('input, textarea, button')?.focus()
    } else {
      document.body.style.overflow = ''
      document.removeEventListener('keydown', onKeydown)
    }
  },
)

onBeforeUnmount(() => {
  document.body.style.overflow = ''
  document.removeEventListener('keydown', onKeydown)
})
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-neutral-1000/50 p-4"
      @mousedown.self="emit('close')"
    >
      <div
        ref="panel"
        role="dialog"
        aria-modal="true"
        :aria-label="title"
        :class="cn('my-auto flex max-w-full flex-col gap-6 rounded-3 border-[1.5px] border-neutral-300 bg-neutral-200 p-6', panelClass)"
      >
        <div class="flex flex-col items-center gap-1 text-center">
          <h2 class="text-h7 font-bold text-neutral-1000">{{ title }}</h2>
          <p v-if="description" class="text-b3 text-neutral-800">{{ description }}</p>
        </div>

        <slot />

        <div v-if="$slots.actions" :class="cn('flex w-full gap-4', buttonLayout === 'vertical' ? 'flex-col' : '[&>*]:flex-1')">
          <slot name="actions" />
        </div>
      </div>
    </div>
  </Teleport>
</template>
