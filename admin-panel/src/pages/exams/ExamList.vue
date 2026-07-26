<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { gradesApi, type Grade } from '../../api/grades'
import { subjectsApi, type Subject } from '../../api/subjects'
import { topicsApi, type Topic } from '../../api/topics'
import { lessonsApi, type Lesson } from '../../api/lessons'
import { questionsApi, type Question } from '../../api/questions'
import { examsApi, type Exam, type ExamFormData } from '../../api/exams'
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
const filterGrades = ref<Grade[]>([])
const filterGradeId = ref<number | null>(null)
const filterType = ref('')

// Exam data
const exams = ref<Exam[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(true)
const search = ref('')
const page = ref(1)

const router = useRouter()

// Modal state
const modalOpen = ref(false)
const editingExam = ref<Exam | null>(null)
const modalName = ref('')
const modalType = ref('general')
const modalGradeId = ref<number | null>(null)
const modalDuration = ref(60)
const modalPassingScore = ref(50)
const modalDescription = ref('')
const selectedQuestionIds = ref<number[]>([])
const saving = ref(false)
const formError = ref('')

// Modal cascading selects
const modalSubjects = ref<Subject[]>([])
const modalSubjectId = ref<number | null>(null)
const modalTopics = ref<Topic[]>([])
const modalTopicId = ref<number | null>(null)
const modalLessons = ref<Lesson[]>([])
const modalLessonId = ref<number | null>(null)
const showAllQuestions = ref(false)
const loadingAllQuestions = ref(false)

// Available questions for selected lesson in modal
const availableQuestions = ref<Question[]>([])

// Delete state
const deleteTarget = ref<Exam | null>(null)
const deleting = ref(false)

// --- Init ---
onMounted(async () => {
  try {
    const [gradesRes, subjectsRes] = await Promise.all([
      gradesApi.list({ per_page: 100 }),
      subjectsApi.list({ per_page: 100 }),
    ])
    filterGrades.value = gradesRes.data
    modalSubjects.value = subjectsRes.data
    fetchExams()
  } catch {
    showToast({ type: 'error', text: 'Məlumatlar yüklənərkən xəta baş verdi' })
  }
})

// --- Filter ---
watch(filterGradeId, () => { page.value = 1 })
watch(search, () => { page.value = 1 })
watch(filterType, () => { page.value = 1 })
watch([page, search, filterGradeId, filterType], fetchExams)

async function fetchExams() {
  loading.value = true
  try {
    const params: any = {
      search: search.value || undefined,
      page: page.value,
      per_page: 15,
    }
    if (filterGradeId.value) params.grade_id = filterGradeId.value
    if (filterType.value) params.type = filterType.value

    const res = await examsApi.list(params)
    exams.value = res.data
    meta.value = res.meta
  } catch {
    showToast({ type: 'error', text: 'İmtahanlar yüklənərkən xəta baş verdi' })
  } finally {
    loading.value = false
  }
}

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
  if (id) await loadQuestionsForLesson(id)
})

async function loadQuestionsForLesson(lessonId: number) {
  showAllQuestions.value = false
  try {
    const res = await questionsApi.list({ lesson_id: lessonId, per_page: 500 })
    availableQuestions.value = res.data
  } catch {
    availableQuestions.value = []
  }
}

async function loadAllQuestions() {
  showAllQuestions.value = true
  loadingAllQuestions.value = true
  try {
    const res = await questionsApi.list({ per_page: 1000 })
    availableQuestions.value = res.data
  } catch {
    showToast({ type: 'error', text: 'Suallar yüklənərkən xəta baş verdi' })
    availableQuestions.value = []
  } finally {
    loadingAllQuestions.value = false
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
  editingExam.value = null
  modalName.value = ''
  modalType.value = 'general'
  modalGradeId.value = null
  modalDuration.value = 60
  modalPassingScore.value = 50
  modalDescription.value = ''
  modalSubjectId.value = null
  modalTopicId.value = null
  modalLessonId.value = null
  modalTopics.value = []
  modalLessons.value = []
  selectedQuestionIds.value = []
  availableQuestions.value = []
  showAllQuestions.value = false
  formError.value = ''
  modalOpen.value = true
}

async function openEdit(exam: Exam) {
  editingExam.value = exam
  modalName.value = exam.name
  modalType.value = exam.type
  modalGradeId.value = exam.grade_id
  modalDuration.value = exam.duration_minutes
  modalPassingScore.value = exam.passing_score
  modalDescription.value = exam.description || ''
  modalSubjectId.value = null
  modalTopicId.value = null
  modalLessonId.value = null
  modalTopics.value = []
  modalLessons.value = []
  showAllQuestions.value = false

  try {
    const res = await examsApi.show(exam.id)
    selectedQuestionIds.value = res.data.questions?.map((q) => q.id) || []
  } catch {
    selectedQuestionIds.value = []
  }

  formError.value = ''
  modalOpen.value = true
}

function validate(): string {
  if (!modalName.value.trim()) return 'İmtahan adı tələb olunur'
  if (!modalGradeId.value) return 'Sinif seçilməlidir'
  if (!modalDuration.value || modalDuration.value < 1) return 'Müddət daxil edilməlidir'
  if (modalPassingScore.value < 0 || modalPassingScore.value > 100) return 'Keçid balı 0-100 arasında olmalıdır'
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
    const payload: ExamFormData = {
      name: modalName.value,
      type: modalType.value,
      grade_id: modalGradeId.value!,
      duration_minutes: modalDuration.value,
      passing_score: modalPassingScore.value,
      description: modalDescription.value || undefined,
      question_ids: selectedQuestionIds.value,
    }

    if (editingExam.value) {
      await examsApi.update(editingExam.value.id, payload)
      showToast({ type: 'success', text: 'İmtahan yeniləndi' })
    } else {
      await examsApi.create(payload)
      showToast({ type: 'success', text: 'İmtahan yaradıldı' })
    }
    modalOpen.value = false
    fetchExams()
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
    await examsApi.delete(deleteTarget.value.id)
    showToast({ type: 'success', text: 'İmtahan silindi' })
    deleteTarget.value = null
    fetchExams()
  } catch {
    showToast({ type: 'error', text: 'Silinərkən xəta baş verdi' })
  } finally {
    deleting.value = false
  }
}

const TYPE_OPTIONS: Record<string, string> = {
  general: 'General',
  midterm: 'Ara imtahan',
  final: 'Final',
}

const eyeIcon = `<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>`

const columns: Column[] = [
  { key: 'id', label: 'ID', className: 'w-16' },
  { key: 'name', label: 'Ad' },
  {
    key: 'grade_id',
    label: 'Sinif',
    render: (e: any) => e.grade?.name || `Sinif #${e.grade_id}`,
  },
  {
    key: 'type',
    label: 'Tip',
    render: (e: any) =>
      h('span', {
        class: `inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
          e.type === 'final'
            ? 'bg-red-50 text-red-700'
            : e.type === 'midterm'
            ? 'bg-amber-50 text-amber-700'
            : 'bg-blue-50 text-blue-700'
        }`,
      }, TYPE_OPTIONS[e.type] || e.type),
  },
  {
    key: 'duration_minutes',
    label: 'Müddət',
    render: (e: any) => `${e.duration_minutes} dəq`,
  },
  {
    key: 'passing_score',
    label: 'Keçid balı',
    render: (e: any) => `${e.passing_score}%`,
  },
  {
    key: 'total_questions',
    label: 'Suallar',
    className: 'w-20',
    render: (e: any) =>
      h('span', {
        class: 'inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600',
      }, `${e.total_questions} sual`),
  },
  {
    key: 'created_at',
    label: 'Tarix',
    render: (e: any) => new Date(e.created_at).toLocaleDateString('az-AZ'),
  },
  {
    key: 'actions',
    label: '',
    className: 'w-16',
    render: (e: any) =>
      h('button', {
        class: 'rounded-lg p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors',
        title: 'Detallar',
        onClick: () => router.push(`/exams/${e.id}`),
        innerHTML: eyeIcon,
      }),
  },
]
</script>

<template>
  <Toast />

  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">İmtahanlar</h1>
      <p class="mt-1 text-sm text-gray-500">İmtahanları idarə edin</p>
    </div>
    <button
      @click="openCreate"
      class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Yeni imtahan
    </button>
  </div>

  <!-- Filter selectors -->
  <div class="mb-5 flex flex-wrap items-end gap-3">
    <div class="max-w-xs">
      <SearchInput v-model="search" placeholder="İmtahan axtar..." />
    </div>
    <select
      v-model="filterGradeId"
      class="rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
    >
      <option :value="null">Bütün siniflər</option>
      <option v-for="g in filterGrades" :key="g.id" :value="g.id">{{ g.name }}</option>
    </select>
    <select
      v-model="filterType"
      class="rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
    >
      <option value="">Bütün tiplər</option>
      <option value="general">General</option>
      <option value="midterm">Ara imtahan</option>
      <option value="final">Final</option>
    </select>
  </div>

  <Table
    :columns="columns"
    :data="exams"
    :loading="loading"
    empty-message="Heç bir imtahan tapılmadı"
    :on-edit="openEdit"
    :on-delete="(e: any) => deleteTarget = e"
  />

  <Pagination v-if="meta" :meta="meta" @page-change="(p: number) => page = p" />

  <!-- Create/Edit Modal -->
  <Modal
    :open="modalOpen"
    :title="editingExam ? 'İmtahan redaktə et' : 'Yeni imtahan'"
    size="lg"
    @close="modalOpen = false"
  >
    <div class="space-y-4">
      <p v-if="formError" class="rounded-xl bg-red-50 border border-red-200 px-4 py-2.5 text-sm text-red-600">
        {{ formError }}
      </p>

      <!-- Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">İmtahan adı</label>
        <input
          v-model="modalName"
          type="text"
          placeholder="İmtahan adını daxil edin"
          class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 placeholder-gray-400 transition-colors focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100"
        />
      </div>

      <!-- Type -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">İmtahan növü</label>
        <select
          v-model="modalType"
          class="w-full max-w-xs rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
        >
          <option value="general">General</option>
          <option value="midterm">Ara imtahan</option>
          <option value="final">Final</option>
        </select>
      </div>

      <!-- Grade -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Sinif</label>
        <select
          v-model="modalGradeId"
          class="w-full max-w-xs rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
        >
          <option :value="null">Sinif seçin</option>
          <option v-for="g in filterGrades" :key="g.id" :value="g.id">{{ g.name }}</option>
        </select>
      </div>

      <!-- Duration & Passing Score -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Müddət (dəqiqə)</label>
          <input
            v-model.number="modalDuration"
            type="number"
            min="1"
            max="600"
            class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 transition-colors focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Keçid balı (%)</label>
          <input
            v-model.number="modalPassingScore"
            type="number"
            min="0"
            max="100"
            class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 transition-colors focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100"
          />
        </div>
      </div>

      <!-- Description -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Açıqlama (istəyə bağlı)</label>
        <textarea
          v-model="modalDescription"
          rows="2"
          placeholder="İmtahan haqqında qısa məlumat"
          class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 placeholder-gray-400 transition-colors focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100"
        />
      </div>

      <!-- Question cascade -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <label class="block text-sm font-medium text-gray-700">Suallar</label>
          <button
            v-if="!showAllQuestions"
            @click="loadAllQuestions"
            class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors"
          >
            Bütün sualları yüklə
          </button>
          <button
            v-else
            @click="showAllQuestions = false; modalSubjectId = null; modalTopicId = null; modalLessonId = null; availableQuestions = []"
            class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors"
          >
            Fənn / dərs seç
          </button>
        </div>

        <!-- Cascade filters (hidden in all-questions mode) -->
        <div v-if="!showAllQuestions" class="flex flex-wrap items-end gap-3">
          <select
            v-model="modalSubjectId"
            :disabled="showAllQuestions"
            class="w-full max-w-xs rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
          >
            <option :value="null">Fən seçin</option>
            <option v-for="s in modalSubjects" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
          <select
            v-model="modalTopicId"
            :disabled="!modalSubjectId || showAllQuestions"
            class="w-full max-w-xs rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <option :value="null">Mövzu seçin</option>
            <option v-for="t in modalTopics" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>
          <select
            v-model="modalLessonId"
            :disabled="!modalTopicId || showAllQuestions"
            class="w-full max-w-xs rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <option :value="null">Dərs seçin</option>
            <option v-for="l in modalLessons" :key="l.id" :value="l.id">{{ l.name }}</option>
          </select>
        </div>
      </div>

      <!-- Questions list -->
      <div v-if="modalLessonId || showAllQuestions">
        <div v-if="loadingAllQuestions" class="flex items-center justify-center py-8">
          <div class="h-6 w-6 animate-spin rounded-full border-2 border-indigo-600 border-t-transparent" />
        </div>
        <div v-else-if="availableQuestions.length === 0" class="rounded-xl border border-dashed border-gray-200 bg-gray-50/50 p-4 text-center text-sm text-gray-400">
          Heç bir sual tapılmadı
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
        <p class="mt-1.5 text-xs text-gray-400">{{ selectedQuestionIds.length }} sual seçildi</p>
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
          {{ saving ? 'Saxlanılır...' : editingExam ? 'Yadda saxla' : 'Yarat' }}
        </button>
      </div>
    </div>
  </Modal>

  <!-- Delete Confirmation -->
  <ConfirmDialog
    :open="!!deleteTarget"
    title="İmtahan sil"
    :message='deleteTarget ? `"${deleteTarget.name}" imtahanını silmək istədiyinizə əminsiniz?` : ""'
    :loading="deleting"
    @confirm="handleDelete"
    @cancel="deleteTarget = null"
  />
</template>
