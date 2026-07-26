<script setup lang="ts">
import type { PaginationMeta } from '../api/types'

defineProps<{
  meta: PaginationMeta
}>()

const emit = defineEmits<{
  pageChange: [page: number]
}>()

function getPages(meta: PaginationMeta): (number | '...')[] {
  const pages: (number | '...')[] = []
  for (let i = 1; i <= meta.last_page; i++) {
    if (
      i === 1 ||
      i === meta.last_page ||
      (i >= meta.current_page - 1 && i <= meta.current_page + 1)
    ) {
      pages.push(i)
    } else if (pages[pages.length - 1] !== '...') {
      pages.push('...')
    }
  }
  return pages
}
</script>

<template>
  <div
    v-if="meta.last_page > 1"
    class="flex items-center justify-between border-t border-gray-100 px-4 py-3 sm:px-6"
  >
    <p class="text-sm text-gray-500">
      {{ meta.from }}–{{ meta.to }} / {{ meta.total }}
    </p>
    <nav class="flex items-center gap-1">
      <button
        :disabled="meta.current_page === 1"
        @click="emit('pageChange', meta.current_page - 1)"
        class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>

      <template v-for="(page, idx) in getPages(meta)" :key="idx">
        <span v-if="page === '...'" class="px-2 text-sm text-gray-400">...</span>
        <button
          v-else
          @click="emit('pageChange', page)"
          :class="[
            'min-w-[2rem] rounded-lg px-2 py-1.5 text-sm font-medium transition-colors',
            page === meta.current_page
              ? 'bg-indigo-50 text-indigo-700'
              : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'
          ]"
        >
          {{ page }}
        </button>
      </template>

      <button
        :disabled="meta.current_page === meta.last_page"
        @click="emit('pageChange', meta.current_page + 1)"
        class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </nav>
  </div>
</template>
