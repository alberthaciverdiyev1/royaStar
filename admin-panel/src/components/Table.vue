<script setup lang="ts">
import type { VNode } from 'vue'
import TableCell from './TableCell'

export interface Column {
  key: string
  label: string
  className?: string
  render?: (item: any) => string | number | VNode
}

const props = withDefaults(defineProps<{
  columns: Column[]
  data: any[]
  loading?: boolean
  emptyMessage?: string
  onEdit?: ((item: any) => void) | null
  onDelete?: ((item: any) => void) | null
}>(), {
  loading: false,
  emptyMessage: 'Məlumat tapılmadı',
  onEdit: null,
  onDelete: null,
})

const hasActions = props.onEdit !== null || props.onDelete !== null
</script>

<template>
  <!-- Loading state -->
  <div
    v-if="loading"
    class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm"
  >
    <div class="divide-y divide-gray-50">
      <div
        v-for="i in 5"
        :key="i"
        class="flex gap-4 p-4"
      >
        <div
          v-for="col in columns"
          :key="col.key"
          class="h-5 flex-1 animate-pulse rounded bg-gray-100"
        />
        <div
          v-if="hasActions"
          class="h-5 w-20 animate-pulse rounded bg-gray-100"
        />
      </div>
    </div>
  </div>

  <!-- Empty state -->
  <div
    v-else-if="data.length === 0"
    class="flex flex-col items-center justify-center rounded-2xl border border-gray-100 bg-white px-6 py-12 shadow-sm"
  >
    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-50">
      <svg class="h-7 w-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
      </svg>
    </div>
    <p class="mt-4 text-sm text-gray-400">{{ emptyMessage }}</p>
  </div>

  <!-- Table -->
  <div
    v-else
    class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm"
  >
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-50">
        <thead>
          <tr class="bg-gray-50/80">
            <th
              v-for="col in columns"
              :key="col.key"
              :class="col.className || ''"
              class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500"
            >
              {{ col.label }}
            </th>
            <th
              v-if="hasActions"
              class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500"
            >
              Əməliyyatlar
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr
            v-for="item in data"
            :key="item.id"
            class="transition-colors hover:bg-gray-50/50"
          >
            <td
              v-for="col in columns"
              :key="col.key"
              :class="col.className || ''"
              class="whitespace-nowrap px-5 py-4 text-sm text-gray-700"
            >
              <TableCell
                v-if="col.render"
                :render="col.render"
                :item="item"
              />
              <template v-else>
                {{ item[col.key] }}
              </template>
            </td>
            <td
              v-if="hasActions"
              class="whitespace-nowrap px-5 py-4 text-right"
            >
              <div class="flex items-center justify-end gap-1">
                <button
                  v-if="props.onEdit"
                  @click="props.onEdit(item)"
                  class="rounded-lg p-2 text-gray-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  title="Redaktə et"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button
                  v-if="props.onDelete"
                  @click="props.onDelete(item)"
                  class="rounded-lg p-2 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors"
                  title="Sil"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
