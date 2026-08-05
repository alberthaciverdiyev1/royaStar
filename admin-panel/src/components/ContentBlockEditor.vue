<script setup lang="ts">
import { nextTick } from 'vue'

export interface ContentBlock {
  type: 'text' | 'image' | 'audio'
  content: string
}

const props = defineProps<{
  modelValue: ContentBlock[]
  placeholder?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [blocks: ContentBlock[]]
}>()

function updateBlock(index: number, block: ContentBlock) {
  const next = [...props.modelValue]
  next[index] = block
  emit('update:modelValue', next)
}

function removeBlock(index: number) {
  const next = props.modelValue.filter((_, i) => i !== index)
  emit('update:modelValue', next)
}

async function addBlock(type: ContentBlock['type']) {
  const block: ContentBlock = type === 'text'
    ? { type: 'text', content: '' }
    : { type, content: '' }
  emit('update:modelValue', [...props.modelValue, block])

  if (type === 'image' || type === 'audio') {
    // After the block renders, bring it into view so the upload label is
    // visible (it may be below the fold). Clicking the label then opens the
    // native file picker (the input is wrapped inside the label).
    await nextTick()
    const items = document.querySelectorAll('.content-block-item')
    items[items.length - 1]?.scrollIntoView({ block: 'center', behavior: 'smooth' })
  }
}

function handleFileUpload(event: Event, blockIndex: number) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = (e) => {
    const dataUrl = e.target?.result as string
    const next = [...props.modelValue]
    next[blockIndex] = { ...next[blockIndex], content: dataUrl }
    emit('update:modelValue', next)
  }
  reader.readAsDataURL(file)
  // Reset so picking the SAME file again still fires the change event.
  input.value = ''
}

const typeLabels: Record<ContentBlock['type'], string> = {
  text: 'Mətn',
  image: 'Şəkil',
  audio: 'Səs',
}

const typeColors: Record<ContentBlock['type'], string> = {
  text: 'bg-blue-50 border-blue-200 text-blue-700',
  image: 'bg-green-50 border-green-200 text-green-700',
  audio: 'bg-amber-50 border-amber-200 text-amber-700',
}

const typeLabelColors: Record<ContentBlock['type'], string> = {
  text: 'bg-blue-100 text-blue-800',
  image: 'bg-green-100 text-green-800',
  audio: 'bg-amber-100 text-amber-800',
}
</script>

<template>
  <div class="space-y-2">
    <!-- Existing blocks -->
    <div
      v-for="(block, i) in modelValue"
      :key="i"
      :data-block-index="i"
      class="content-block-item"
      :class="['rounded-xl border p-3 transition-colors', typeColors[block.type]]"
    >
      <div class="flex items-center justify-between mb-2">
        <span
          :class="['inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium', typeLabelColors[block.type]]"
        >
          {{ typeLabels[block.type] }}
        </span>
        <button
          type="button"
          @click="removeBlock(i)"
          class="rounded-md p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Text editor -->
      <textarea
        v-if="block.type === 'text'"
        :value="block.content"
        @input="updateBlock(i, { ...block, content: ($event.target as HTMLTextAreaElement).value })"
        rows="2"
        :placeholder="placeholder || 'Mətni daxil edin'"
        class="w-full rounded-lg border border-inherit bg-white/80 py-2 px-3 text-sm text-gray-900 placeholder-gray-400 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 resize-none"
      />

      <!-- Image: upload input always on top, preview always below -->
      <div v-else-if="block.type === 'image'" class="space-y-2">
        <label
          class="flex cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-inherit bg-white/60 px-4 py-4 text-sm text-gray-500 hover:bg-white/90 transition-colors"
        >
          <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          {{ block.content ? 'Şəkili dəyiş' : 'Şəkil seçin' }}
          <input
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleFileUpload($event, i)"
          />
        </label>
        <!-- Preview below the input -->
        <div class="rounded-lg border border-inherit bg-white/80 p-2">
          <img
            v-if="block.content"
            :src="block.content"
            alt="uploaded"
            class="max-h-40 w-full rounded-lg object-contain"
          />
          <div
            v-else
            class="flex h-28 items-center justify-center text-xs text-gray-400"
          >
            Şəkil seçildikdə burada görünəcək
          </div>
        </div>
      </div>

      <!-- Audio: upload input always on top, player preview below -->
      <div v-else-if="block.type === 'audio'" class="space-y-2">
        <label
          class="flex cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-inherit bg-white/60 px-4 py-4 text-sm text-gray-500 hover:bg-white/90 transition-colors"
        >
          <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
          </svg>
          {{ block.content ? 'Səsi dəyiş' : 'Səs faylı seçin' }}
          <input
            type="file"
            accept="audio/*"
            class="hidden"
            @change="handleFileUpload($event, i)"
          />
        </label>
        <!-- Preview below the input -->
        <div
          v-if="block.content"
          class="rounded-lg border border-inherit bg-white/80 p-2"
        >
          <audio :src="block.content" controls class="w-full h-10" />
        </div>
        <div
          v-else
          class="flex h-12 items-center justify-center rounded-lg border border-dashed border-inherit text-xs text-gray-400"
        >
          Səs seçildikdə burada çalınacaq
        </div>
      </div>
    </div>

    <!-- Add block buttons -->
    <div v-if="!modelValue.length" class="flex flex-wrap gap-2">
      <button
        type="button"
        @click="addBlock('text')"
        class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-500 hover:border-blue-300 hover:text-blue-600 hover:bg-blue-50 transition-colors"
      >
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Mətn əlavə et
      </button>
      <button
        type="button"
        @click="addBlock('image')"
        class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-500 hover:border-green-300 hover:text-green-600 hover:bg-green-50 transition-colors"
      >
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        Şəkil əlavə et
      </button>
      <button
        type="button"
        @click="addBlock('audio')"
        class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-500 hover:border-amber-300 hover:text-amber-600 hover:bg-amber-50 transition-colors"
      >
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
        </svg>
        Səs əlavə et
      </button>
    </div>

    <!-- Add more button when blocks exist -->
    <div v-if="modelValue.length > 0" class="flex flex-wrap gap-1.5">
      <button
        type="button"
        @click="addBlock('text')"
        class="rounded-lg border border-dashed border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-400 hover:text-blue-500 hover:border-blue-300 hover:bg-blue-50 transition-colors"
      >
        + Mətn
      </button>
      <button
        type="button"
        @click="addBlock('image')"
        class="rounded-lg border border-dashed border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-400 hover:text-green-500 hover:border-green-300 hover:bg-green-50 transition-colors"
      >
        + Şəkil
      </button>
      <button
        type="button"
        @click="addBlock('audio')"
        class="rounded-lg border border-dashed border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-400 hover:text-amber-500 hover:border-amber-300 hover:bg-amber-50 transition-colors"
      >
        + Səs
      </button>
    </div>
  </div>
</template>
