<script setup>
import { cn } from '../../lib/cn'
import Avatar from './Avatar.vue'
import Button from './Button.vue'
import { Logout3Icon } from '@solar-icons/vue/bold-duotone/logout-3'

defineProps({
  // [{ key, label, active?, icon: Component }]
  items: { type: Array, required: true },
  user: { type: Object, default: null }, // { name }
  activeColor: { type: String, default: '' },
})
const emit = defineEmits(['select', 'logout'])
</script>

<template>
  <nav
    aria-label="Navigasi utama"
    class="flex w-[280px] shrink-0 flex-col items-center justify-between self-stretch bg-white"
    :style="activeColor ? { '--sb-active': activeColor } : undefined"
  >
    <div class="flex w-full flex-col items-start gap-12">
      <div class="flex h-28 w-full items-center justify-center border-b border-neutral-300 px-10 py-10">
        <slot name="logo" />
      </div>

      <ul class="flex w-full flex-col items-start gap-2">
        <li v-for="item in items" :key="item.key" class="w-full">
          <button
            type="button"
            :aria-current="item.active ? 'page' : undefined"
            :class="
              cn(
                'flex w-full cursor-pointer items-center gap-2 px-10 py-4 transition-colors',
                item.active
                  ? 'border-r-2 border-[var(--sb-active,var(--color-primary-400))] bg-[color-mix(in_srgb,var(--sb-active,var(--color-primary-400))_10%,transparent)]'
                  : 'hover:bg-neutral-200',
              )
            "
            @click="emit('select', item.key)"
          >
            <span
              :class="cn('size-6 shrink-0 [&>svg]:size-full', item.active ? 'text-[var(--sb-active,var(--color-primary-400))]' : 'text-neutral-900')"
            >
              <component :is="item.icon" />
            </span>
            <span :class="cn('text-b2 font-bold', item.active ? 'text-[var(--sb-active,var(--color-primary-400))]' : 'text-neutral-900')">
              {{ item.label }}
            </span>
          </button>
        </li>
      </ul>
    </div>

    <div v-if="user" class="flex w-full flex-col items-center justify-center p-10">
      <div class="flex w-full items-center justify-between gap-2">
        <div class="flex min-w-0 items-center gap-2">
          <Avatar size="small" :letter="user.name.charAt(0)" />
          <span class="text-b3 truncate text-neutral-1000">{{ user.name }}</span>
        </div>
        <Button theme="secondary" variant="ghost" size="sm" aria-label="Keluar" @click="emit('logout')">
          <template #icon-left><Logout3Icon /></template>
        </Button>
      </div>
    </div>
  </nav>
</template>
