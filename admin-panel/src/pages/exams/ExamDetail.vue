<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { examsApi, type Exam } from '../../api/exams'
import { questionsApi, type Question } from '../../api/questions'
import QuestionContentView from '../../components/QuestionContentView.vue'
import QuestionOrderList from '../../components/QuestionOrderList.vue'
import Toast from '../../components/Toast.vue'
import { showToast } from '../../stores/toast'

const route = useRoute()
const router = useRouter()
const exam = ref<Exam | null>(null)
const loading = ref(true)

// Selected (ordered) questions on this page
const items = ref<Question[]>([])
// Question bank
const bank = ref<Question[]>([])
const bankSearch = ref('')
const bankLoading = ref(false)

const saving = ref(false)
const dirty = ref(false)

const DIFFICULTY_OPTIONS: Record<number, string> = {
  1: 'Başlanğıc',
  2: 'Elementar',
  3: 'Orta',
  4: 'Qabaqcıl',
  5: 'Ekspert',
}

const TYPE_OPTIONS: Record<string, string> = {
  general: 'General',
  midterm: 'Ara imtahan',
  final: 'Final',
}

const id = computed(() => Number(route.params.id))

async function loadExam() {
  loading.value = true
  try {
    const res = await examsApi.show(id.value)
    exam.value = res.data
    items.value = res.data.questions || []
  } catch {
    showToast({ type: 'error', text: 'İmtahan yüklənərkən xəta baş verdi' })
    router.push('/exams')
  } finally {
    loading.value = false
  }
}

async function loadBank(search?: string) {
  bankLoading.value = true
  try {
    const res = await questionsApi.list({
      search: search || undefined,
      per_page: 1000,
    })
    bank.value = res.data
  } catch {
    showToast({ type: 'error', text: 'Suallar yüklənərkən xəta baş verdi' })
    bank.value = []
  } finally {
    bankLoading.value = false
  }
}

let searchTimer: ReturnType<typeof setTimeout>
function onSearchInput() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => loadBank(bankSearch.value.trim() || undefined), 350)
}

function isAdded(questionId: number) {
  return items.value.some((s) => s.id === questionId)
}

function toggleAdd(q: Question) {
  if (isAdded(q.id)) return
  items.value.push(q)
  dirty.value = true
}

function removeItem(questionId: number) {
  const idx = items.value.findIndex((s) => s.id === questionId)
  if (idx !== -1) {
    items.value.splice(idx, 1)
    dirty.value = true
  }
}

function onReorder(list: Question[]) {
  items.value = list
  dirty.value = true
}

async function handleSave() {
  if (!exam.value) return
  saving.value = true
  try {
    await examsApi.update(exam.value.id, {
      name: exam.value.name,
      type: exam.value.type,
      grade_id: exam.value.grade_id,
      duration_minutes: exam.value.duration_minutes,
      passing_score: exam.value.passing_score,
      description: exam.value.description || undefined,
      question_ids: items.value.map((q) => q.id),
    })
    showToast({ type: 'success', text: 'Sual sırası saxlanıldı' })
    dirty.value = false
    await loadExam()
  } catch {
    showToast({ type: 'error', text: 'Saxlama zamanı xəta baş verdi' })
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  if (!id.value) {
    showToast({ type: 'error', text: 'İmtahan ID tapılmadı' })
    router.push('/exams')
    return
  }
  await loadExam()
  await loadBank()
})
</script>

<template>
  <Toast />

  <div v-if="loading" class="flex items-center justify-center py-20">
    <div class="h-8 w-8 animate-spin rounded-full border-2 border-indigo-600 border-t-transparent" />
  </div>

  <template v-else-if="exam">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4">
      <div class="flex items-center gap-4">
        <button
          @click="router.push('/exams')"
          class="rounded-xl border border-gray-200 bg-white p-2.5 text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-colors"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ exam.name }}</h1>
          <p class="mt-0.5 text-sm text-gray-500">
            {{ exam.grade?.name || `Sinif #${exam.grade_id}` }} · {{ exam.questions?.length || 0 }} sual
          </p>
        </div>
      </div>
      <button
        @click="handleSave"
        :disabled="saving"
        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors disabled:opacity-50"
      >
        {{ saving ? 'Saxlanılır...' : dirty ? 'Sıranı Yadda Saxla' : 'Saxla' }}
      </button>
    </div>

    <!-- Exam Info -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-4">
      <div class="rounded-xl border border-gray-200 bg-white px-4 py-3.5">
        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Növ</span>
        <p class="mt-1">
          <span :class="
            exam.type === 'final'
              ? 'bg-red-50 text-red-700'
              : exam.type === 'midterm'
              ? 'bg-amber-50 text-amber-700'
              : 'bg-blue-50 text-blue-700'
          " class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
            {{ TYPE_OPTIONS[exam.type] || exam.type }}
          </span>
        </p>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white px-4 py-3.5">
        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Sual sayı</span>
        <p class="mt-1 text-lg font-bold text-gray-900">{{ items.length }}</p>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white px-4 py-3.5">
        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Müddət</span>
        <p class="mt-1 text-sm font-medium text-gray-900">{{ exam.duration_minutes }} dəqiqə</p>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white px-4 py-3.5">
        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Keçid balı</span>
        <p class="mt-1 text-sm font-medium text-gray-900">{{ exam.passing_score }}%</p>
      </div>
    </div>

    <div v-if="exam.description" class="mb-6 rounded-xl border border-gray-200 bg-white px-4 py-3.5">
      <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Açıqlama</span>
      <p class="mt-1 text-sm text-gray-700">{{ exam.description }}</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
      <!-- Bank -->
      <div class="rounded-xl border border-gray-200 bg-white overflow-hidden lg:col-span-2">
        <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
          <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-gray-900">Sual bankı</h2>
          </div>
          <input
            v-model="bankSearch"
            @input="onSearchInput"
            type="text"
            placeholder="Sual axtar..."
            class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
          />
        </div>

        <div v-if="bankLoading" class="flex items-center justify-center p-8 text-sm text-gray-400">
          <span class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-indigo-600 border-t-transparent" />
          Yüklənir...
        </div>
        <div v-else-if="bank.length === 0" class="p-8 text-center text-sm text-gray-400">
          Sual tapılmadı
        </div>
        <div v-else class="max-h-80 overflow-y-auto divide-y divide-gray-100">
          <button
            v-for="q in bank"
            :key="q.id"
            type="button"
            :disabled="isAdded(q.id)"
            @click="toggleAdd(q)"
            class="flex w-full items-center gap-3 px-4 py-2.5 text-left hover:bg-gray-50 transition-colors disabled:opacity-40 disabled:cursor-default"
          >
            <span
              :class="isAdded(q.id) ? 'bg-green-500' : 'bg-gray-200'"
              class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-[10px] text-white"
            >
              <svg v-if="isAdded(q.id)" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </span>
            <span class="min-w-0 flex-1 flex items-center gap-2">
              <QuestionContentView :blocks="q.question" compact />
              <span v-if="!q.question?.length" class="truncate text-sm text-gray-700">Sual #{{ q.id }}</span>
            </span>
            <span class="shrink-0 text-xs text-gray-400">{{ q.type === 'open' ? 'Açıq' : 'Test' }}</span>
          </button>
        </div>
      </div>

      <!-- Selected order -->
      <div class="lg:col-span-3">
        <QuestionOrderList :questions="items" @reorder="onReorder" @remove="removeItem">
          <template #default="{ q }">
            <div class="min-w-0">
              <QuestionContentView :blocks="q.question" compact />
              <p class="mt-0.5 text-[10px] text-gray-400">Çətinlik: {{ DIFFICULTY_OPTIONS[q.difficulty_level] || q.difficulty_level }}</p>
            </div>
          </template>
        </QuestionOrderList>
        <p v-if="dirty" class="mt-2 text-sm text-amber-600">Dəyişikliklər saxlanılmayıb — "Saxla" düyməsini sıxın.</p>
      </div>
    </div>
  </template>
</template>
