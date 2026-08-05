<script setup lang="ts">
interface VariantBlock {
  type: string
  content: string
}

const props = defineProps<{
  modelValue: VariantBlock
  variantType: 'text' | 'image' | 'audio'
}>()

const emit = defineEmits<{
  'update:modelValue': [block: VariantBlock]
}>()

function handleFileUpload(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = (e) => {
    const dataUrl = e.target?.result as string
    emit('update:modelValue', { ...props.modelValue, content: dataUrl })
  }
  reader.readAsDataURL(file)
  // Reset so picking the SAME file again still fires the change event.
  input.value = ''
}

function clear() {
  emit('update:modelValue', { ...props.modelValue, content: '' })
}

const isImage = () => props.variantType === 'image'
</script>

<template>
  <div class="space-y-2">
    <!-- Upload input is wrapped inside the label → native file picker on click. -->
    <label
      class="flex cursor-pointer items-center justify-center rounded-xl border-2 border-dashed border-gray-200 bg-white px-4 py-4 text-sm text-gray-400 transition-colors"
      :class="isImage()
        ? 'hover:border-green-300 hover:text-green-500 hover:bg-green-50'
        : 'hover:border-amber-300 hover:text-amber-500 hover:bg-amber-50'"
    >
      <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path
          v-if="isImage()"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="1.5"
          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
        />
        <path
          v-else
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="1.5"
          d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"
        />
      </svg>
      {{ modelValue.content
        ? (isImage() ? 'Şəkili dəyiş' : 'Səsi dəyiş')
        : (isImage() ? 'Şəkil seç' : 'Səs seç') }}
      <input
        type="file"
        :accept="isImage() ? 'image/*' : 'audio/*'"
        class="hidden"
        @change="handleFileUpload"
      />
    </label>

    <!-- Preview below the input -->
    <div class="rounded-lg border border-gray-200 bg-white p-2">
      <img
        v-if="isImage() && modelValue.content"
        :src="modelValue.content"
        alt="preview"
        class="max-h-32 w-full rounded-lg object-contain"
      />
      <div
        v-else-if="isImage()"
        class="flex h-24 items-center justify-center text-xs text-gray-400"
      >
        Şəkil seçildikdə burada görünəcək
      </div>
      <audio
        v-else-if="modelValue.content"
        :src="modelValue.content"
        controls
        class="w-full h-9 rounded-lg"
      />
      <div
        v-else
        class="flex h-10 items-center justify-center text-xs text-gray-400"
      >
        Səs seçildikdə burada çalınacaq
      </div>
    </div>

    <button
      v-if="modelValue.content"
      type="button"
      @click="clear"
      class="text-xs font-medium text-red-500 hover:text-red-700 transition-colors"
    >
      Sil
    </button>
  </div>
</template>
