<script setup lang="ts">
import { useToast } from '../stores/toast'

const toast = useToast()
</script>

<template>
  <div
    v-if="toast.message"
    :class="[
      'fixed top-4 right-4 z-[100] flex items-center gap-3 rounded-2xl border px-5 py-3.5 shadow-lg transition-all duration-300',
      toast.message.type === 'success'
        ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
        : 'bg-red-50 border-red-200 text-red-800',
      toast.visible ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-2'
    ]"
  >
    <svg
      v-if="toast.message.type === 'success'"
      class="h-5 w-5 text-emerald-500"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <svg
      v-else
      class="h-5 w-5 text-red-500"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <p class="text-sm font-medium">{{ toast.message.text }}</p>
  </div>
</template>
