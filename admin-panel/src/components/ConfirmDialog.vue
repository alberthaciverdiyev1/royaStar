<script setup lang="ts">
import Modal from './Modal.vue'

withDefaults(defineProps<{
  open: boolean
  title?: string
  message?: string
  confirmText?: string
  cancelText?: string
  loading?: boolean
  variant?: 'danger' | 'warning'
}>(), {
  title: 'Təsdiqlə',
  message: 'Bu əməliyyatı yerinə yetirmək istədiyinizə əminsiniz?',
  confirmText: 'Təsdiqlə',
  cancelText: 'İmtina',
  loading: false,
  variant: 'danger',
})

const emit = defineEmits<{
  confirm: []
  cancel: []
}>()
</script>

<template>
  <Modal :open="open" :title="title" size="sm" @close="emit('cancel')">
    <p class="text-sm text-gray-600">{{ message }}</p>
    <div class="mt-6 flex justify-end gap-3">
      <button
        @click="emit('cancel')"
        :disabled="loading"
        class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors disabled:opacity-50"
      >
        {{ cancelText }}
      </button>
      <button
        @click="emit('confirm')"
        :disabled="loading"
        :class="[
          'rounded-xl px-5 py-2.5 text-sm font-medium text-white transition-colors disabled:opacity-50',
          variant === 'danger'
            ? 'bg-red-600 hover:bg-red-700'
            : 'bg-amber-600 hover:bg-amber-700'
        ]"
      >
        {{ loading ? 'Gözləyin...' : confirmText }}
      </button>
    </div>
  </Modal>
</template>
