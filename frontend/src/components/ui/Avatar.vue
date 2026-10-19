<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: { type: String, default: 'medium' }, // tiny | small | medium | large | giant | xl | xxl | xxxl
  letter: { type: String, default: 'A' },
  status: { type: Boolean, default: false },
})

const sizeConfig = {
  tiny: { box: 24, letter: 12, dot: 8 },
  small: { box: 32, letter: 16, dot: 10 },
  medium: { box: 40, letter: 18, dot: 12 },
  large: { box: 48, letter: 24, dot: 12 },
  giant: { box: 56, letter: 28, dot: 16 },
  xl: { box: 64, letter: 32, dot: 16 },
  xxl: { box: 80, letter: 48, dot: 20 },
  xxxl: { box: 96, letter: 48, dot: 24 },
}

const config = computed(() => sizeConfig[props.size])
</script>

<template>
  <div
    class="relative shrink-0 rounded-full bg-secondary-500"
    :style="{ width: config.box + 'px', height: config.box + 'px' }"
  >
    <span
      class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 font-sans font-bold whitespace-nowrap text-white"
      :style="{ fontSize: config.letter + 'px', lineHeight: 1 }"
    >
      {{ letter.toUpperCase() }}
    </span>
    <span
      v-if="status"
      class="absolute right-0 bottom-0 rounded-full bg-green-200 ring-2 ring-white"
      :style="{ width: config.dot + 'px', height: config.dot + 'px' }"
    />
  </div>
</template>
