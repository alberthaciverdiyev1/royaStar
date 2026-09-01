<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { topicsApi, type Topic } from '../../api/topics'
import { lessonsApi, type Lesson } from '../../api/lessons'
import {
  questionsApi,
  type Question,
  fromContentBlock,
} from '../../api/questions'
import Table from '../../components/Table.vue'
import type { Column } from '../../components/Table.vue'
import QuestionContentView from '../../components/QuestionContentView.vue'
import Pagination from '../../components/Pagination.vue'
import SearchInput from '../../components/SearchInput.vue'
import ConfirmDialog from '../../components/ConfirmDialog.vue'
import Toast from '../../components/Toast.vue'
import { showToast } from '../../stores/toast'
import type { PaginationMeta } from '../../api/types'
import { h } from 'vue'

const router = useRouter()

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

function goCreate() {
  router.push({ name: 'QuestionNew' })
}

function goEdit(q: Question) {
  router.push({ name: 'QuestionEdit', params: { id: q.id } })
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
    render: (q: any) =>
      h(QuestionContentView, { blocks: q.question, compact: true }),
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
</script>

<template>
  <Toast />

  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Suallar</h1>
      <p class="mt-1 text-sm text-gray-500">Dərslər üzrə test suallarını idarə edin</p>
    </div>
    <button
      @click="goCreate"
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
    :on-edit="goEdit"
    :on-delete="(q: any) => deleteTarget = q"
  />

  <Pagination v-if="meta" :meta="meta" @page-change="(p: number) => page = p" />

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
