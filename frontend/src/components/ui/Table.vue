<script setup>
import { cn } from '../../lib/cn'

defineProps({
  // [{ key, header, align?: 'left'|'center'|'right', width?: string }]
  columns: { type: Array, required: true },
  data: { type: Array, required: true },
  rowKey: { type: String, default: 'id' },
  emptyMessage: { type: String, default: 'Tidak ada data' },
  minWidth: { type: String, default: '720px' },
})

function cellAlign(align) {
  if (align === 'center') return 'text-center justify-center'
  if (align === 'right') return 'text-right justify-end'
  return 'text-left justify-start'
}
</script>

<template>
  <div class="overflow-hidden rounded-2 border border-neutral-600 bg-white">
    <div v-if="$slots.toolbar" class="flex flex-wrap items-center justify-between gap-3 border-b border-neutral-600 bg-neutral-400 p-3">
      <slot name="toolbar" />
    </div>

    <div class="overflow-x-auto">
      <div :style="{ minWidth }">
        <div class="flex items-center bg-neutral-400 p-3">
          <p
            v-for="col in columns"
            :key="col.key"
            :style="col.width ? { width: col.width, flexShrink: 0 } : undefined"
            :class="cn('text-b2 font-bold text-neutral-1000', !col.width && 'flex-1', cellAlign(col.align))"
          >
            {{ col.header }}
          </p>
        </div>

        <p v-if="data.length === 0" class="text-b2 p-8 text-center text-neutral-700">{{ emptyMessage }}</p>

        <template v-else>
          <div
            v-for="(row, i) in data"
            :key="row[rowKey]"
            :class="cn('flex items-center gap-2 p-3', i < data.length - 1 && 'border-b border-neutral-400')"
          >
            <div
              v-for="col in columns"
              :key="col.key"
              :style="col.width ? { width: col.width, flexShrink: 0 } : undefined"
              :class="cn('flex items-center', !col.width && 'min-w-0 flex-1', cellAlign(col.align))"
            >
              <slot :name="`cell-${col.key}`" :row="row">
                <p class="text-b2 text-neutral-1000">{{ row[col.key] ?? '' }}</p>
              </slot>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
