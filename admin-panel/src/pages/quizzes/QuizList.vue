<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { subjectsApi, type Subject } from '../../api/subjects'
import { topicsApi, type Topic } from '../../api/topics'
import { lessonsApi, type Lesson } from '../../api/lessons'
import { questionsApi, type Question } from '../../api/questions'
import { quizzesApi, type Quiz, type QuizFormData } from '../../api/quizzes'
import Table from '../../components/Table.vue'
import type { Column } from '../../components/Table.vue'
import Pagination from '../../components/Pagination.vue'
import SearchInput from '../../components/SearchInput.vue'
import Modal from '../../components/Modal.vue'
import ConfirmDialog from '../../components/ConfirmDialog.vue'
import Toast from '../../components/Toast.vue'
import { showToast } from '../../stores/toast'
import type { PaginationMeta } from '../../api/types'
import { h } from 'vue'

// Filter state
const subjects = ref<Subject[]>([])
const filterSubjectId = ref<number | null>(null)
const filterTopics = ref<Topic[]>([])
const filterTopicId = ref<number | null>(null)
const filterLessons = ref<Lesson[]>([])
const filterLessonId = ref<number | null>(null)

// Quiz data
const quizzes = ref<Quiz[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(true)
const search = ref('')
const page = ref(1)
const filterType = ref('')

const router = useRouter()

// Modal state
const modalOpen = ref(false)
const editingQuiz = ref<Quiz | null>(null)
const modalName = ref('')
const modalType = ref('topic_based')
const modalLessonId = ref<number | null>(null)
const selectedQuestionIds = ref<number[]>([])
const saving = ref(false)
const formError = ref('')

// Modal subject → topic → lesson cascading selects
const modalSubjects = ref<Subject[]>([])
const modalSubjectId = ref<number | null>(null)
const modalTopics = ref<Topic[]>([])
const modalTopicId = ref<number | null>(null)
const modalLessons = ref<Lesson[]>([])

// Available questions for selected lesson in modal
const availableQuestions = ref<Question[]>([])

// Delete state
const deleteTarget = ref<Quiz | null>(null)
const deleting = ref(false)

// --- Init ---
onMounted(async () => {
  try {
    const [subRes, modalSubRes] = await Promise.all([
      subjectsApi.list({ per_page: 100 }),
      subjectsApi.list({ per_page: 100 }),
    ])
    subjects.value = subRes.data
    modalSubjects.value = modalSubRes.data
    fetchQuizzes()
  } catch {
    showToast({ type: 'error', text: 'Fənlər yüklənərkən xəta baş verdi' })
  }
})

// --- Filter cascading ---
watch(filterSubjectId, async () => {
  filterTopicId.value = null
  filterTopics.value = []
  filterLessonId.value = null
  filterLessons.value = []
  page.value = 1
  if (!filterSubjectId.value) return
  try {
    const res = await topicsApi.list(filterSubjectId.value, { per_page: 100 })
    filterTopics.value = res.data
  } catch { /* ignore */ }
})

watch(filterTopicId, async () => {
  filterLessonId.value = null
  filterLessons.value = []
  page.value = 1
  if (!filterTopicId.value) return
  try {
    const res = await lessonsApi.list(filterTopicId.value, { per_page: 100 })
    filterLessons.value = res.data
  } catch { /* ignore */ }
})

watch(filterLessonId, () => { page.value = 1 })
watch(search, () => { page.value = 1 })
watch(filterType, () => { page.value = 1 })
watch([page, search, filterLessonId, filterType], fetchQuizzes)

// --- Modal cascading ---
watch(modalSubjectId, async () => {
  modalTopicId.value = null
  modalTopics.value = []
  modalLessonId.value = null
  modalLessons.value = []
  if (!modalSubjectId.value) return
  try {
    const res = await topicsApi.list(modalSubjectId.value, { per_page: 100 })
    modalTopics.value = res.data
  } catch { /* ignore */ }
})

watch(modalTopicId, async () => {
  modalLessonId.value = null
  modalLessons.value = []
  if (!modalTopicId.value) return
  try {
    const res = await lessonsApi.list(modalTopicId.value, { per_page: 100 })
    modalLessons.value = res.data
  } catch { /* ignore */ }
})

watch(modalLessonId, async (id) => {
  if (id) {
    await loadQuestionsForLesson(id)
  }
})

async function loadQuestionsForLesson(lessonId: number) {
  try {
    const res = await questionsApi.list({ lesson_id: lessonId, per_page: 500 })
    availableQuestions.value = res.data
  } catch {
    availableQuestions.value = []
  }
}

async function fetchQuizzes() {
  loading.value = true
  try {
    const params: any = {
      search: search.value || undefined,
      page: page.value,
      per_page: 15,
    }
    if (filterLessonId.value) params.lesson_id = filterLessonId.value
    if (filterType.value) params.type = filterType.value

    const res = await quizzesApi.list(params)
    quizzes.value = res.data
    meta.value = res.meta
  } catch {
    showToast({ type: 'error', text: 'Quizlər yüklənərkən xəta baş verdi' })
  } finally {
    loading.value = false
  }
}

function toggleQuestion(id: number) {
  const idx = selectedQuestionIds.value.indexOf(id)
  if (idx === -1) {
    selectedQuestionIds.value.push(id)
  } else {
    selectedQuestionIds.value.splice(idx, 1)
  }
}

function openCreate() {
  editingQuiz.value = null
  modalName.value = ''
  modalType.value = 'topic_based'
  modalSubjectId.value = null
  modalTopicId.value = null
  modalLessonId.value = null
  modalTopics.value = []
  modalLessons.value = []
  selectedQuestionIds.value = []
  availableQuestions.value = []
  formError.value = ''
  modalOpen.value = true
}

async function openEdit(quiz: Quiz) {
  editingQuiz.value = quiz
  modalName.value = quiz.name
  modalType.value = quiz.type
  modalLessonId.value = quiz.lesson_id
  modalSubjectId.value = null
  modalTopicId.value = null
  modalTopics.value = []
  modalLessons.value = []

  // Load full quiz data to get questions
  try {
    const res = await quizzesApi.show(quiz.id)
    const full = res.data
    selectedQuestionIds.value = full.questions?.map((q) => q.id) || []
  } catch {
    selectedQuestionIds.value = []
  }

  // Load available questions for the lesson
  if (quiz.lesson_id) {
    await loadQuestionsForLesson(quiz.lesson_id)
  }

  formError.value = ''
  modalOpen.value = true
}

function validate(): string {
  if (!modalName.value.trim()) return 'Quiz adı tələb olunur'
  if (!modalLessonId.value) return 'Dərs seçilməlidir'
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
    const payload: QuizFormData = {
      name: modalName.value,
      type: modalType.value,
      lesson_id: modalLessonId.value!,
      question_ids: selectedQuestionIds.value,
    }

    if (editingQuiz.value) {
      await quizzesApi.update(editingQuiz.value.id, payload)
      showToast({ type: 'success', text: 'Quiz yeniləndi' })
    } else {
      await quizzesApi.create(payload)
      showToast({ type: 'success', text: 'Quiz yaradıldı' })
    }
    modalOpen.value = false
    fetchQuizzes()
  } catch {
    showToast({ type: 'error', text: 'Xəta baş verdi' })
  } finally {
    saving.value = false
  }
}

async function handleDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await quizzesApi.delete(deleteTarget.value.id)
    showToast({ type: 'success', text: 'Quiz silindi' })
    deleteTarget.value = null
    fetchQuizzes()
  } catch {
    showToast({ type: 'error', text: 'Silinərkən xəta baş verdi' })
  } finally {
    deleting.value = false
  }
}

const columns: Column[] = [
  { key: 'id', label: 'ID', className: 'w-16' },
  { key: 'name', label: 'Ad' },
  {
    key: 'lesson_id',
    label: 'Dərs',
    render: (q: any) => q.lesson?.name || `Dərs #${q.lesson_id}`,
  },
  {
    key: 'type',
    label: 'Tip',
    render: (q: any) =>
      h('span', {
        class: `inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
          q.type === 'general'
            ? 'bg-purple-50 text-purple-700'
            : 'bg-blue-50 text-blue-700'
        }`,
      }, q.type === 'general' ? 'General' : 'Mövzu əsaslı'),
  },
  {
    key: 'questions',
    label: 'Suallar',
    className: 'w-20',
    render: (q: any) => {
      const count = q.questions?.length || 0
      return h('span', {
        class: 'inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600',
      }, `${count} sual`)
    },
  },
  {
    key: 'created_at',
    label: 'Tarix',
    render: (q: any) => new Date(q.created_at).toLocaleDateString('az-AZ'),
  },
  {
    key: 'actions',
    label: '',
    className: 'w-16',
    render: (q: any) =>
      h('button', {
        class: 'rounded-lg p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors',
        title: 'Detallar',
        onClick: () => router.push(`/quizzes/${q.id}`),
        innerHTML: '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>',
      }),
  },
]
</script>

<template>
  <Toast />

  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Quizlər</h1>
      <p class="mt-1 text-sm text-gray-500">Quizləri idarə edin</p>
    </div>
    <button
      @click="openCreate"
      class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Yeni quiz
    </button>
  </div>

  <!-- Filter selectors (optional) -->
  <div class="mb-5 flex flex-wrap items-end gap-3">
    <div class="w-full max-w-xs">
      <label class="block text-sm font-medium text-gray-700 mb-1.5">Fən (filtr)</label>
      <select
        v-model="filterSubjectId"
        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
      >
        <option :value="null">Bütün fənlər</option>
        <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
      </select>
    </div>
    <div class="w-full max-w-xs">
      <label class="block text-sm font-medium text-gray-700 mb-1.5">Mövzu (filtr)</label>
      <select
        v-model="filterTopicId"
        :disabled="!filterSubjectId"
        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <option :value="null">Bütün mövzular</option>
        <option v-for="t in filterTopics" :key="t.id" :value="t.id">{{ t.name }}</option>
      </select>
    </div>
    <div class="w-full max-w-xs">
      <label class="block text-sm font-medium text-gray-700 mb-1.5">Dərs (filtr)</label>
      <select
        v-model="filterLessonId"
        :disabled="!filterTopicId"
        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <option :value="null">Bütün dərslər</option>
        <option v-for="l in filterLessons" :key="l.id" :value="l.id">{{ l.name }}</option>
      </select>
    </div>
    <div class="max-w-xs">
      <SearchInput v-model="search" placeholder="Quiz axtar..." />
    </div>
    <select
      v-model="filterType"
      class="rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
    >
      <option value="">Bütün tiplər</option>
      <option value="topic_based">Mövzu əsaslı</option>
      <option value="general">General</option>
    </select>
  </div>

  <Table
    :columns="columns"
    :data="quizzes"
    :loading="loading"
    empty-message="Heç bir quiz tapılmadı"
    :on-edit="openEdit"
    :on-delete="(q: any) => deleteTarget = q"
  />

  <Pagination v-if="meta" :meta="meta" @page-change="(p: number) => page = p" />

  <!-- Create/Edit Modal -->
  <Modal
    :open="modalOpen"
    :title="editingQuiz ? 'Quiz redaktə et' : 'Yeni quiz'"
    size="lg"
    @close="modalOpen = false"
  >
    <div class="space-y-4">
      <p v-if="formError" class="rounded-xl bg-red-50 border border-red-200 px-4 py-2.5 text-sm text-red-600">
        {{ formError }}
      </p>

      <!-- Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Quiz adı</label>
        <input
          v-model="modalName"
          type="text"
          placeholder="Quiz adını daxil edin"
          class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 placeholder-gray-400 transition-colors focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100"
        />
      </div>

      <!-- Type -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Quiz növü</label>
        <select
          v-model="modalType"
          class="w-full max-w-xs rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
        >
          <option value="topic_based">Mövzu əsaslı</option>
          <option value="general">General</option>
        </select>
      </div>

      <!-- Lesson selectors for modal -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Dərs</label>
        <div class="flex flex-wrap items-end gap-3">
          <select
            v-model="modalSubjectId"
            class="w-full max-w-xs rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
          >
            <option :value="null">Fən seçin</option>
            <option v-for="s in modalSubjects" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
          <select
            v-model="modalTopicId"
            :disabled="!modalSubjectId"
            class="w-full max-w-xs rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <option :value="null">Mövzu seçin</option>
            <option v-for="t in modalTopics" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>
          <select
            v-model="modalLessonId"
            :disabled="!modalTopicId"
            class="w-full max-w-xs rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <option :value="null">Dərs seçin</option>
            <option v-for="l in modalLessons" :key="l.id" :value="l.id">{{ l.name }}</option>
          </select>
        </div>
      </div>

      <!-- Questions -->
      <div v-if="modalLessonId">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Suallar ({{ selectedQuestionIds.length }} seçildi)
        </label>
        <div v-if="availableQuestions.length === 0" class="rounded-xl border border-dashed border-gray-200 bg-gray-50/50 p-4 text-center text-sm text-gray-400">
          Bu dərsdə heç bir sual tapılmadı
        </div>
        <div v-else class="max-h-60 overflow-y-auto rounded-xl border border-gray-200 divide-y divide-gray-100">
          <label
            v-for="q in availableQuestions"
            :key="q.id"
            class="flex items-center gap-3 px-3 py-2.5 hover:bg-gray-50 cursor-pointer transition-colors"
          >
            <input
              type="checkbox"
              :checked="selectedQuestionIds.includes(q.id)"
              @change="toggleQuestion(q.id)"
              class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            />
            <span class="text-sm text-gray-700 flex-1 min-w-0 truncate">
              {{ q.question?.[0]?.content || `Sual #${q.id}` }}
            </span>
            <span class="shrink-0 text-xs text-gray-400">{{ q.type === 'open' ? 'Açıq' : 'Test' }}</span>
          </label>
        </div>
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
          {{ saving ? 'Saxlanılır...' : editingQuiz ? 'Yadda saxla' : 'Yarat' }}
        </button>
      </div>
    </div>
  </Modal>

  <!-- Delete Confirmation -->
  <ConfirmDialog
    :open="!!deleteTarget"
    :title="'Quiz sil'"
    :message='deleteTarget ? `"${deleteTarget.name}" quizini silmək istədiyinizə əminsiniz?` : ""'
    :loading="deleting"
    @confirm="handleDelete"
    @cancel="deleteTarget = null"
  />
</template>
