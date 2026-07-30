<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import { topicsApi, type Topic } from '../../api/topics'
import { lessonsApi, type Lesson } from '../../api/lessons'
import {
  questionsApi,
  type Question,
  fromContentBlock,
} from '../../api/questions'
import Table from '../../components/Table.vue'
import type { Column } from '../../components/Table.vue'
import Pagination from '../../components/Pagination.vue'
import SearchInput from '../../components/SearchInput.vue'
import Modal from '../../components/Modal.vue'
import ConfirmDialog from '../../components/ConfirmDialog.vue'
import Toast from '../../components/Toast.vue'
import ContentBlockEditor from '../../components/ContentBlockEditor.vue'
import type { ContentBlock } from '../../components/ContentBlockEditor.vue'
import { showToast } from '../../stores/toast'
import type { PaginationMeta } from '../../api/types'
import { h } from 'vue'

const topics = ref<Topic[]>([])
const selectedTopicId = ref<number | null>(null)
const lessons = ref<Lesson[]>([])
const selectedLessonId = ref<number | null>(null)
const questions = ref<Question[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(true)
const search = ref('')
const page = ref(1)

const filterType = ref('')
const filterDifficulty = ref<number | null>(null)

// Modal state
const modalOpen = ref(false)
const editingQuestion = ref<Question | null>(null)
const questionBlocks = ref<ContentBlock[]>([])
const questionType = ref<'regular' | 'open'>('regular')
const variantABlocks = ref<ContentBlock[]>([])
const variantBBlocks = ref<ContentBlock[]>([])
const variantCBlocks = ref<ContentBlock[]>([])
const variantDBlocks = ref<ContentBlock[]>([])
const variantEBlocks = ref<ContentBlock[]>([])
const rightAnswer = ref('')
const openAnswerBlocks = ref<ContentBlock[]>([])
const answerType = ref('')
const explanationBlocks = ref<ContentBlock[]>([])
const modalDifficulty = ref<number>(3)
const saving = ref(false)
const formError = ref('')

// Delete state
const deleteTarget = ref<Question | null>(null)
const deleting = ref(false)

const DIFFICULTY_OPTIONS = [
  { value: 1, label: 'Başlanğıc' },
  { value: 2, label: 'Elementar' },
  { value: 3, label: 'Orta' },
  { value: 4, label: 'Qabaqcıl' },
  { value: 5, label: 'Ekspert' },
]

const variantType = ref<ContentBlock['type']>('text')

// File upload — creates temporary input element for reliability
const pendingFiles = ref<Map<string, File>>(new Map())

function triggerFileUpload(targetRef: typeof variantABlocks, accept: string) {
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = accept
  input.style.display = 'none'
  input.addEventListener('change', () => {
    const file = input.files?.[0]
    if (!file) return
    const blobUrl = URL.createObjectURL(file)
    if (targetRef.value[0]) {
      targetRef.value = [{ ...targetRef.value[0], content: blobUrl }]
    }
    pendingFiles.value.set(blobUrl, file)
  })
  document.body.appendChild(input)
  input.click()
  setTimeout(() => document.body.removeChild(input), 2000)
}

function cleanupPendingFiles() {
  for (const blobUrl of pendingFiles.value.keys()) {
    URL.revokeObjectURL(blobUrl)
  }
  pendingFiles.value = new Map()
}

async function convertPendingFiles() {
  const map = pendingFiles.value
  if (!map.size) return
  const entries = [...map.entries()]
  const allBlocks = [
    questionBlocks, variantABlocks, variantBBlocks, variantCBlocks,
    variantDBlocks, variantEBlocks, openAnswerBlocks, explanationBlocks,
  ]
  for (const [blobUrl, file] of entries) {
    try {
      const dataUrl = await fileToDataUrl(file)
      for (const blocks of allBlocks) {
        for (const block of blocks.value) {
          if (block.content === blobUrl) block.content = dataUrl
        }
      }
    } catch { /* keep blob URL if conversion fails */ }
    URL.revokeObjectURL(blobUrl)
    map.delete(blobUrl)
  }
}

function fileToDataUrl(file: File): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(reader.result as string)
    reader.onerror = () => reject(reader.error)
    reader.readAsDataURL(file)
  })
}

const VARIANT_TYPE_OPTIONS = [
  { value: 'text' as const, label: 'Mətn' },
  { value: 'image' as const, label: 'Şəkil' },
  { value: 'audio' as const, label: 'Səs' },
]

const variantLabels = ['A', 'B', 'C', 'D', 'E']

function onVariantTypeChange(type: ContentBlock['type']) {
  cleanupPendingFiles()
  variantType.value = type
  variantABlocks.value = [{ type, content: '' }]
  variantBBlocks.value = [{ type, content: '' }]
  variantCBlocks.value = [{ type, content: '' }]
  variantDBlocks.value = [{ type, content: '' }]
  variantEBlocks.value = [{ type, content: '' }]
  rightAnswer.value = ''
}



onMounted(async () => {
  try {
    const res = await topicsApi.list({ per_page: 100 })
    topics.value = res.data
    if (topics.value.length > 0) {
      selectedTopicId.value = topics.value[0].id
    }
  } catch {
    showToast({ type: 'error', text: 'Mövzular yüklənərkən xəta baş verdi' })
  }
})

watch(selectedTopicId, async () => {
  selectedLessonId.value = null
  lessons.value = []
  if (!selectedTopicId.value) return
  try {
    const res = await lessonsApi.list(selectedTopicId.value, { per_page: 100 })
    lessons.value = res.data
    if (lessons.value.length > 0) {
      selectedLessonId.value = lessons.value[0].id
    }
  } catch {
    showToast({ type: 'error', text: 'Dərslər yüklənərkən xəta baş verdi' })
  }
})

watch(selectedLessonId, () => { page.value = 1 })
watch(search, () => { page.value = 1 })
watch([page, search, selectedLessonId, filterType, filterDifficulty], fetchQuestions)

async function fetchQuestions() {
  loading.value = true
  try {
    const params: any = {
      search: search.value || undefined,
      page: page.value,
      per_page: 15,
    }
    if (selectedLessonId.value) params.lesson_id = selectedLessonId.value
    if (filterType.value) params.type = filterType.value
    if (filterDifficulty.value) params.difficulty_level = filterDifficulty.value

    const res = await questionsApi.list(params)
    questions.value = res.data
    meta.value = res.meta
  } catch {
    showToast({ type: 'error', text: 'Suallar yüklənərkən xəta baş verdi' })
  } finally {
    loading.value = false
  }
}

function openCreate() {
  cleanupPendingFiles()
  if (!selectedLessonId.value) {
    showToast({ type: 'error', text: 'Əvvəlcə dərs seçin' })
    return
  }
  editingQuestion.value = null
  questionBlocks.value = [{ type: 'text', content: '' }]
  questionType.value = 'regular'
  variantType.value = 'text'
  variantABlocks.value = [{ type: 'text', content: '' }]
  variantBBlocks.value = [{ type: 'text', content: '' }]
  variantCBlocks.value = [{ type: 'text', content: '' }]
  variantDBlocks.value = [{ type: 'text', content: '' }]
  variantEBlocks.value = [{ type: 'text', content: '' }]
  rightAnswer.value = ''
  openAnswerBlocks.value = [{ type: 'text', content: '' }]
  answerType.value = ''
  explanationBlocks.value = []
  modalDifficulty.value = 3
  formError.value = ''
  modalOpen.value = true
}

function openEdit(q: Question) {
  cleanupPendingFiles()
  editingQuestion.value = q
  questionBlocks.value = q.question ?? []
  questionType.value = q.type
  variantABlocks.value = q.variant_a ?? []
  variantBBlocks.value = q.variant_b ?? []
  variantCBlocks.value = q.variant_c ?? []
  const vt = (q.variant_a?.[0]?.type || 'text') as ContentBlock['type']
  variantType.value = vt
  variantDBlocks.value = q.variant_d?.length ? q.variant_d : [{ type: vt, content: '' }]
  variantEBlocks.value = q.variant_e?.length ? q.variant_e : [{ type: vt, content: '' }]
  rightAnswer.value = q.right_answer || ''
  openAnswerBlocks.value = q.open_answer ?? []
  answerType.value = q.answer_type || ''
  explanationBlocks.value = q.explanation ?? []
  modalDifficulty.value = q.difficulty_level
  formError.value = ''
  modalOpen.value = true
}

function buildPayload() {
  const payload: any = {
    question: questionBlocks.value,
    type: questionType.value,
    lesson_id: editingQuestion.value?.lesson_id ?? selectedLessonId.value,
    difficulty_level: modalDifficulty.value,
  }

  if (questionType.value === 'regular') {
    payload.variant_a = variantABlocks.value
    payload.variant_b = variantBBlocks.value
    payload.variant_c = variantCBlocks.value
    if (hasContent(variantDBlocks.value)) payload.variant_d = variantDBlocks.value
    if (hasContent(variantEBlocks.value)) payload.variant_e = variantEBlocks.value
    payload.right_answer = rightAnswer.value
  } else {
    payload.open_answer = openAnswerBlocks.value
    payload.answer_type = answerType.value || 'exact'
  }

  if (explanationBlocks.value.length) {
    payload.explanation = explanationBlocks.value
  }

  return payload
}

function hasContent(blocks: ContentBlock[]): boolean {
  return blocks.some(b => b.content.trim().length > 0)
}

function validate(): string {
  if (!hasContent(questionBlocks.value)) return 'Sual mətni tələb olunur'
  if (!selectedLessonId.value && !editingQuestion.value?.lesson_id) return 'Dərs seçilməlidir'
  if (questionType.value === 'regular') {
    if (!hasContent(variantABlocks.value)) return 'Variant A tələb olunur'
    if (!hasContent(variantBBlocks.value)) return 'Variant B tələb olunur'
    if (!hasContent(variantCBlocks.value)) return 'Variant C tələb olunur'
    if (!rightAnswer.value) return 'Düzgün cavab seçilməlidir'
  } else {
    if (!hasContent(openAnswerBlocks.value)) return 'Açıq cavab tələb olunur'
  }
  return ''
}

async function handleSave() {
  const err = validate()
  if (err) {
    formError.value = err
    return
  }

  saving.value = true
  formError.value = ''
  try {
    await convertPendingFiles()
    const payload = buildPayload()

    if (editingQuestion.value) {
      await questionsApi.update(editingQuestion.value.id, payload)
      showToast({ type: 'success', text: 'Sual yeniləndi' })
    } else {
      await questionsApi.create(payload)
      showToast({ type: 'success', text: 'Sual yaradıldı' })
    }
    modalOpen.value = false
    fetchQuestions()
  } catch (err: any) {
    const errData = err?.response?.data
    const msg = errData?.errors
      ? (Object.values(errData.errors) as string[]).flat().join('; ')
      : errData?.message || 'Xəta baş verdi'
    formError.value = msg
    showToast({ type: 'error', text: msg })
  } finally {
    saving.value = false
  }
}

async function handleDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await questionsApi.delete(deleteTarget.value.id)
    showToast({ type: 'success', text: 'Sual silindi' })
    deleteTarget.value = null
    fetchQuestions()
  } catch {
    showToast({ type: 'error', text: 'Silinərkən xəta baş verdi' })
  } finally {
    deleting.value = false
  }
}

function getVariantText(q: Question, letter: string): string {
  const field = `variant_${letter.toLowerCase()}` as keyof Question
  return fromContentBlock(q[field] as any)
}

const columns: Column[] = [
  { key: 'id', label: 'ID', className: 'w-16' },
  {
    key: 'question',
    label: 'Sual',
    render: (q: any) => {
      const text = fromContentBlock(q.question)
      return text.length > 60 ? text.slice(0, 60) + '...' : text
    },
  },
  {
    key: 'lesson_name',
    label: 'Dərs',
    className: 'w-40',
    render: (q: any) => h('span', { class: 'text-sm text-slate-600' }, q.lesson_name || '—'),
  },
  {
    key: 'type',
    label: 'Tip',
    render: (q: any) =>
      h('span', {
        class: `inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
          q.type === 'regular'
            ? 'bg-blue-50 text-blue-700'
            : 'bg-amber-50 text-amber-700'
        }`,
      }, q.type === 'regular' ? 'Test' : 'Açıq'),
  },
  {
    key: 'difficulty_level',
    label: 'Çətinlik',
    render: (q: any) => {
      const opt = DIFFICULTY_OPTIONS.find((o) => o.value === q.difficulty_level)
      return h('span', { class: 'text-sm text-slate-600' }, opt?.label || q.difficulty_level)
    },
  },
  {
    key: 'right_answer',
    label: 'Cavab',
    className: 'w-16',
    render: (q: any) => {
      if (q.type === 'open') return h('span', { class: 'text-xs text-slate-400' }, '—')
      return h('span', {
        class: 'inline-flex items-center justify-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-700',
      }, q.right_answer?.toUpperCase() || '?')
    },
  },
  {
    key: 'created_at',
    label: 'Tarix',
    render: (q: any) => new Date(q.created_at).toLocaleDateString('az-AZ'),
  },
]

const availableVariants = computed(() => {
  const letters = ['a', 'b', 'c']
  if (hasContent(variantDBlocks.value)) letters.push('d')
  if (hasContent(variantEBlocks.value)) letters.push('e')
  return letters
})
</script>

<template>
  <Toast />

  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Suallar</h1>
      <p class="mt-1 text-sm text-gray-500">Dərslər üzrə test suallarını idarə edin</p>
    </div>
    <button
      @click="openCreate"
      class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Yeni sual
    </button>
  </div>

  <!-- Topic + Lesson selectors -->
  <div class="mb-5 flex flex-wrap items-end gap-3">
    <div class="w-full max-w-xs">
      <label class="block text-sm font-medium text-gray-700 mb-1.5">Mövzu seçin</label>
      <select
        v-model="selectedTopicId"
        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
      >
        <option :value="null">Mövzu seçin...</option>
        <option v-for="t in topics" :key="t.id" :value="t.id">{{ t.name }}</option>
      </select>
    </div>
    <div class="w-full max-w-xs">
      <label class="block text-sm font-medium text-gray-700 mb-1.5">Dərs seçin</label>
      <select
        v-model="selectedLessonId"
        :disabled="!selectedTopicId"
        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <option :value="null">Bütün dərslər</option>
        <option v-for="l in lessons" :key="l.id" :value="l.id">{{ l.name }}</option>
      </select>
    </div>
  </div>

  <!-- Filters -->
  <div class="mb-5 flex flex-wrap gap-3">
    <div class="max-w-xs">
      <SearchInput v-model="search" placeholder="Sual axtar..." />
    </div>
    <select
      v-model="filterType"
      class="rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
    >
      <option value="">Bütün tiplər</option>
      <option value="regular">Test</option>
      <option value="open">Açıq</option>
    </select>
    <select
      v-model="filterDifficulty"
      class="rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
    >
      <option :value="null">Bütün çətinliklər</option>
      <option v-for="opt in DIFFICULTY_OPTIONS" :key="opt.value" :value="opt.value">
        {{ opt.label }}
      </option>
    </select>
  </div>

  <Table
    :columns="columns"
    :data="questions"
    :loading="loading"
    empty-message="Heç bir sual tapılmadı"
    :on-edit="openEdit"
    :on-delete="(q: any) => deleteTarget = q"
  />

  <Pagination v-if="meta" :meta="meta" @page-change="(p: number) => page = p" />

  <!-- Create/Edit Modal -->
  <Modal
    :open="modalOpen"
    :title="editingQuestion ? 'Sualı redaktə et' : 'Yeni sual'"
    size="lg"
    @close="modalOpen = false; cleanupPendingFiles()"
  >
    <div class="space-y-4">
      <p v-if="formError" class="rounded-xl bg-red-50 border border-red-200 px-4 py-2.5 text-sm text-red-600">
        {{ formError }}
      </p>

      <!-- Lesson info -->
      <div class="rounded-xl bg-indigo-50 border border-indigo-100 px-4 py-2.5 text-sm text-indigo-700 flex items-center gap-2">
        <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
        <span>
          <strong>Dərs:</strong>
          {{ editingQuestion?.lesson_name || lessons.find(l => l.id === (editingQuestion?.lesson_id ?? selectedLessonId))?.name || 'Seçilməyib' }}
        </span>
      </div>

      <!-- Question type -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Sual növü</label>
        <div class="flex gap-2">
          <button
            type="button"
            @click="questionType = 'regular'"
            :class="[
              'rounded-xl border px-4 py-2 text-sm font-medium transition-colors',
              questionType === 'regular'
                ? 'border-indigo-300 bg-indigo-50 text-indigo-700'
                : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'
            ]"
          >
            Test sualı
          </button>
          <button
            type="button"
            @click="questionType = 'open'"
            :class="[
              'rounded-xl border px-4 py-2 text-sm font-medium transition-colors',
              questionType === 'open'
                ? 'border-indigo-300 bg-indigo-50 text-indigo-700'
                : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'
            ]"
          >
            Açıq sual
          </button>
        </div>
      </div>

      <!-- Question text -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Sual mətni</label>
        <ContentBlockEditor v-model="questionBlocks" placeholder="Sual mətnini daxil edin" />
      </div>

      <!-- Variants (regular) -->
      <template v-if="questionType === 'regular'">
        <!-- Variant type selector -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Variant növü</label>
          <div class="flex gap-2">
            <button
              v-for="opt in VARIANT_TYPE_OPTIONS"
              :key="opt.value"
              type="button"
              @click="onVariantTypeChange(opt.value)"
              :class="[
                'rounded-xl border px-4 py-2 text-sm font-medium transition-colors',
                variantType === opt.value
                  ? 'border-indigo-300 bg-indigo-50 text-indigo-700'
                  : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'
              ]"
            >
              {{ opt.label }}
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <!-- Variant A -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
              Variant A <span class="text-red-400">*</span>
            </label>
            <textarea
              v-if="variantType === 'text'"
              v-model="variantABlocks[0].content"
              rows="2"
              placeholder="Variant A"
              class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 placeholder-gray-400 resize-none transition-colors focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100"
            />
            <div v-else-if="variantType === 'image'" class="space-y-2">
              <div v-if="variantABlocks[0]?.content" class="flex items-center gap-3">
                <img :src="variantABlocks[0].content" class="h-16 w-16 rounded-xl object-cover border border-gray-200" />
                <button type="button" @click="variantABlocks = [{ type: 'image', content: '' }]" class="rounded-lg px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">Sil</button>
              </div>
              <button type="button" @click="triggerFileUpload(variantABlocks, 'image/*')" class="flex items-center justify-center w-full rounded-xl border-2 border-dashed border-gray-200 bg-white px-4 py-4 text-sm text-gray-400 hover:border-green-300 hover:text-green-500 hover:bg-green-50 transition-colors">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                {{ variantABlocks[0]?.content ? 'Şəkili dəyiş' : 'Şəkil seç' }}
              </button>
            </div>
            <div v-else-if="variantType === 'audio'" class="space-y-2">
              <div v-if="variantABlocks[0]?.content" class="flex items-center gap-3">
                <audio :src="variantABlocks[0].content" controls class="h-9 rounded-lg" />
                <button type="button" @click="variantABlocks = [{ type: 'audio', content: '' }]" class="rounded-lg px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">Sil</button>
              </div>
              <button type="button" @click="triggerFileUpload(variantABlocks, 'audio/*')" class="flex items-center justify-center w-full rounded-xl border-2 border-dashed border-gray-200 bg-white px-4 py-4 text-sm text-gray-400 hover:border-amber-300 hover:text-amber-500 hover:bg-amber-50 transition-colors">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                {{ variantABlocks[0]?.content ? 'Səsi dəyiş' : 'Səs seç' }}
              </button>
            </div>
          </div>

          <!-- Variant B -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
              Variant B <span class="text-red-400">*</span>
            </label>
            <textarea
              v-if="variantType === 'text'"
              v-model="variantBBlocks[0].content"
              rows="2"
              placeholder="Variant B"
              class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 placeholder-gray-400 resize-none transition-colors focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100"
            />
            <div v-else-if="variantType === 'image'" class="space-y-2">
              <div v-if="variantBBlocks[0]?.content" class="flex items-center gap-3">
                <img :src="variantBBlocks[0].content" class="h-16 w-16 rounded-xl object-cover border border-gray-200" />
                <button type="button" @click="variantBBlocks = [{ type: 'image', content: '' }]" class="rounded-lg px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">Sil</button>
              </div>
              <button type="button" @click="triggerFileUpload(variantBBlocks, 'image/*')" class="flex items-center justify-center w-full rounded-xl border-2 border-dashed border-gray-200 bg-white px-4 py-4 text-sm text-gray-400 hover:border-green-300 hover:text-green-500 hover:bg-green-50 transition-colors">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                {{ variantBBlocks[0]?.content ? 'Şəkili dəyiş' : 'Şəkil seç' }}
              </button>
            </div>
            <div v-else-if="variantType === 'audio'" class="space-y-2">
              <div v-if="variantBBlocks[0]?.content" class="flex items-center gap-3">
                <audio :src="variantBBlocks[0].content" controls class="h-9 rounded-lg" />
                <button type="button" @click="variantBBlocks = [{ type: 'audio', content: '' }]" class="rounded-lg px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">Sil</button>
              </div>
              <button type="button" @click="triggerFileUpload(variantBBlocks, 'audio/*')" class="flex items-center justify-center w-full rounded-xl border-2 border-dashed border-gray-200 bg-white px-4 py-4 text-sm text-gray-400 hover:border-amber-300 hover:text-amber-500 hover:bg-amber-50 transition-colors">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                {{ variantBBlocks[0]?.content ? 'Səsi dəyiş' : 'Səs seç' }}
              </button>
            </div>
          </div>

          <!-- Variant C -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
              Variant C <span class="text-red-400">*</span>
            </label>
            <textarea
              v-if="variantType === 'text'"
              v-model="variantCBlocks[0].content"
              rows="2"
              placeholder="Variant C"
              class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 placeholder-gray-400 resize-none transition-colors focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100"
            />
            <div v-else-if="variantType === 'image'" class="space-y-2">
              <div v-if="variantCBlocks[0]?.content" class="flex items-center gap-3">
                <img :src="variantCBlocks[0].content" class="h-16 w-16 rounded-xl object-cover border border-gray-200" />
                <button type="button" @click="variantCBlocks = [{ type: 'image', content: '' }]" class="rounded-lg px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">Sil</button>
              </div>
              <button type="button" @click="triggerFileUpload(variantCBlocks, 'image/*')" class="flex items-center justify-center w-full rounded-xl border-2 border-dashed border-gray-200 bg-white px-4 py-4 text-sm text-gray-400 hover:border-green-300 hover:text-green-500 hover:bg-green-50 transition-colors">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                {{ variantCBlocks[0]?.content ? 'Şəkili dəyiş' : 'Şəkil seç' }}
              </button>
            </div>
            <div v-else-if="variantType === 'audio'" class="space-y-2">
              <div v-if="variantCBlocks[0]?.content" class="flex items-center gap-3">
                <audio :src="variantCBlocks[0].content" controls class="h-9 rounded-lg" />
                <button type="button" @click="variantCBlocks = [{ type: 'audio', content: '' }]" class="rounded-lg px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">Sil</button>
              </div>
              <button type="button" @click="triggerFileUpload(variantCBlocks, 'audio/*')" class="flex items-center justify-center w-full rounded-xl border-2 border-dashed border-gray-200 bg-white px-4 py-4 text-sm text-gray-400 hover:border-amber-300 hover:text-amber-500 hover:bg-amber-50 transition-colors">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                {{ variantCBlocks[0]?.content ? 'Səsi dəyiş' : 'Səs seç' }}
              </button>
            </div>
          </div>

          <!-- Variant D -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Variant D</label>
            <textarea
              v-if="variantType === 'text'"
              v-model="variantDBlocks[0].content"
              rows="2"
              placeholder="Variant D"
              class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 placeholder-gray-400 resize-none transition-colors focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100"
            />
            <div v-else-if="variantType === 'image'" class="space-y-2">
              <div v-if="variantDBlocks[0]?.content" class="flex items-center gap-3">
                <img :src="variantDBlocks[0].content" class="h-16 w-16 rounded-xl object-cover border border-gray-200" />
                <button type="button" @click="variantDBlocks = [{ type: 'image', content: '' }]" class="rounded-lg px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">Sil</button>
              </div>
              <button type="button" @click="triggerFileUpload(variantDBlocks, 'image/*')" class="flex items-center justify-center w-full rounded-xl border-2 border-dashed border-gray-200 bg-white px-4 py-4 text-sm text-gray-400 hover:border-green-300 hover:text-green-500 hover:bg-green-50 transition-colors">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                {{ variantDBlocks[0]?.content ? 'Şəkili dəyiş' : 'Şəkil seç' }}
              </button>
            </div>
            <div v-else-if="variantType === 'audio'" class="space-y-2">
              <div v-if="variantDBlocks[0]?.content" class="flex items-center gap-3">
                <audio :src="variantDBlocks[0].content" controls class="h-9 rounded-lg" />
                <button type="button" @click="variantDBlocks = [{ type: 'audio', content: '' }]" class="rounded-lg px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">Sil</button>
              </div>
              <button type="button" @click="triggerFileUpload(variantDBlocks, 'audio/*')" class="flex items-center justify-center w-full rounded-xl border-2 border-dashed border-gray-200 bg-white px-4 py-4 text-sm text-gray-400 hover:border-amber-300 hover:text-amber-500 hover:bg-amber-50 transition-colors">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                {{ variantDBlocks[0]?.content ? 'Səsi dəyiş' : 'Səs seç' }}
              </button>
            </div>
          </div>

          <!-- Variant E -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Variant E</label>
            <textarea
              v-if="variantType === 'text'"
              v-model="variantEBlocks[0].content"
              rows="2"
              placeholder="Variant E"
              class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 placeholder-gray-400 resize-none transition-colors focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100"
            />
            <div v-else-if="variantType === 'image'" class="space-y-2">
              <div v-if="variantEBlocks[0]?.content" class="flex items-center gap-3">
                <img :src="variantEBlocks[0].content" class="h-16 w-16 rounded-xl object-cover border border-gray-200" />
                <button type="button" @click="variantEBlocks = [{ type: 'image', content: '' }]" class="rounded-lg px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">Sil</button>
              </div>
              <button type="button" @click="triggerFileUpload(variantEBlocks, 'image/*')" class="flex items-center justify-center w-full rounded-xl border-2 border-dashed border-gray-200 bg-white px-4 py-4 text-sm text-gray-400 hover:border-green-300 hover:text-green-500 hover:bg-green-50 transition-colors">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                {{ variantEBlocks[0]?.content ? 'Şəkili dəyiş' : 'Şəkil seç' }}
              </button>
            </div>
            <div v-else-if="variantType === 'audio'" class="space-y-2">
              <div v-if="variantEBlocks[0]?.content" class="flex items-center gap-3">
                <audio :src="variantEBlocks[0].content" controls class="h-9 rounded-lg" />
                <button type="button" @click="variantEBlocks = [{ type: 'audio', content: '' }]" class="rounded-lg px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">Sil</button>
              </div>
              <button type="button" @click="triggerFileUpload(variantEBlocks, 'audio/*')" class="flex items-center justify-center w-full rounded-xl border-2 border-dashed border-gray-200 bg-white px-4 py-4 text-sm text-gray-400 hover:border-amber-300 hover:text-amber-500 hover:bg-amber-50 transition-colors">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                {{ variantEBlocks[0]?.content ? 'Səsi dəyiş' : 'Səs seç' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Right answer -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Düzgün cavab</label>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="letter in availableVariants"
              :key="letter"
              type="button"
              @click="rightAnswer = letter"
              :class="[
                'inline-flex items-center justify-center rounded-xl border px-4 py-2 text-sm font-medium transition-colors min-w-[3rem]',
                rightAnswer === letter
                  ? 'border-green-300 bg-green-50 text-green-700'
                  : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'
              ]"
            >
              {{ letter.toUpperCase() }}
            </button>
          </div>
        </div>
      </template>

      <!-- Open answer -->
      <template v-if="questionType === 'open'">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Cavab</label>
          <ContentBlockEditor v-model="openAnswerBlocks" placeholder="Düzgün cavabı daxil edin" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Cavab növü</label>
          <select
            v-model="answerType"
            class="w-full max-w-xs rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
          >
            <option value="exact">Eyni (exact)</option>
            <option value="similar">Oxşar (similar)</option>
          </select>
        </div>
      </template>

      <!-- Difficulty -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Çətinlik səviyyəsi</label>
        <select
          v-model="modalDifficulty"
          class="w-full max-w-xs rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
        >
          <option v-for="opt in DIFFICULTY_OPTIONS" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>
      </div>

      <!-- Explanation -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">İzah (istəyə bağlı)</label>
        <ContentBlockEditor v-model="explanationBlocks" placeholder="Sualın izahı" />
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <button
          @click="modalOpen = false"
          class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
        >
          İmtina
        </button>
        <button
          @click="handleSave"
          :disabled="saving"
          class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 transition-colors disabled:opacity-50"
        >
          {{ saving ? 'Saxlanılır...' : editingQuestion ? 'Yadda saxla' : 'Yarat' }}
        </button>
      </div>
    </div>
  </Modal>

  <!-- Delete Confirmation -->
  <ConfirmDialog
    :open="!!deleteTarget"
    :title="'Sualı sil'"
    :message='deleteTarget ? `Bu sualı silmək istədiyinizə əminsiniz?` : ""'
    :loading="deleting"
    @confirm="handleDelete"
    @cancel="deleteTarget = null"
  />
</template>
