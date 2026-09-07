<script setup lang="ts">
import { ref } from 'vue'
import type { Question } from '../api/questions'

const props = defineProps<{
  questions: Question[]
}>()

const emit = defineEmits<{
  reorder: [list: Question[]]
  remove: [id: number]
}>()

const dragIndex = ref<number | null>(null)
const overIndex = ref<number | null>(null)

function onDragStart(i: number) {
  dragIndex.value = i
}

function onDragOver(e: DragEvent, i: number) {
  e.preventDefault()
  overIndex.value = i
  if (e.dataTransfer) e.dataTransfer.dropEffect = 'move'
}

function onDrop(i: number) {
  const from = dragIndex.value
  if (from === null || from === undefined || from === i) {
    dragIndex.value = null
    overIndex.value = null
    return
  }
  const arr = [...props.questions]
  const [moved] = arr.splice(from, 1)
  arr.splice(i, 0, moved)
  emit('reorder', arr)
  dragIndex.value = null
  overIndex.value = null
}

function onDragEnd() {
  dragIndex.value = null
  overIndex.value = null
}
</script>

<template>
  <div class="rounded-xl border border-indigo-100 bg-indigo-50/40 overflow-hidden">
    <div class="flex items-center justify-between border-b border-indigo-100 bg-indigo-50 px-4 py-2">
      <span class="text-xs font-semibold text-indigo-700 uppercase tracking-wider">
        Seçilmiş suallar (sıra) — drag ilə sıralayın
      </span>
      <span class="inline-flex rounded-full bg-white border border-indigo-200 px-2 py-0.5 text-xs font-bold text-indigo-700">
        {{ props.questions.length }} sual
      </span>
    </div>

    <div v-if="props.questions.length === 0" class="px-4 py-6 text-center text-sm text-indigo-300">
      Hələ sual seçilməyib
    </div>

    <div v-else class="divide-y divide-indigo-100/70 max-h-[26rem] overflow-y-auto">
      <div
        v-for="(q, i) in props.questions"
        :key="q.id"
        draggable="true"
        @dragstart="onDragStart(i)"
        @dragover="onDragOver($event, i)"
        @drop.prevent="onDrop(i)"
        @dragend="onDragEnd"
        :class="[
          'flex items-center gap-3 px-3 py-3 transition-colors',
          dragIndex === i ? 'opacity-40' : 'hover:bg-white',
          overIndex === i && dragIndex !== i && dragIndex !== null
            ? 'bg-indigo-200/60'
            : '',
        ]"
      >
        <span class="cursor-grab shrink-0 select-none text-indigo-400 active:cursor-grabbing text-xl leading-none" title="Sürüklə">⠿</span>
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">
          {{ i + 1 }}
        </span>
        <div class="min-w-0 flex-1">
          <slot :q="q">
            <p class="truncate text-sm text-gray-800">{{ (q.question as any)?.[0]?.content || `Sual #${q.id}` }}</p>
          </slot>
        </div>
        <span :class="q.type === 'open' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700'" class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium">
          {{ q.type === 'open' ? 'Açıq' : 'Test' }}
        </span>
        <button
          type="button"
          @click.stop="emit('remove', q.id)"
          class="shrink-0 rounded-lg p-1 text-gray-300 hover:bg-red-50 hover:text-red-500 transition-colors"
          title="Çıxar"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>
