<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { quizzesApi, type Quiz } from '../../api/quizzes'
import { questionsApi, type Question } from '../../api/questions'
import QuestionContentView from '../../components/QuestionContentView.vue'
import QuestionOrderList from '../../components/QuestionOrderList.vue'
import Toast from '../../components/Toast.vue'
import { showToast } from '../../stores/toast'

const route = useRoute()
const router = useRouter()
const quiz = ref<Quiz | null>(null)
const loading = ref(true)

// Selected (ordered) questions on this page
const items = ref<Question[]>([])
// Question bank to pick from
const bank = ref<Question[]>([])
const bankMode = ref<'lesson' | 'all'>('lesson')
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

const id = computed(() => Number(route.params.id))

async function loadQuiz() {
  loading.value = true
  try {
    const res = await quizzesApi.show(id.value)
    quiz.value = res.data
    items.value = res.data.questions || []
  } catch {
    showToast({ type: 'error', text: 'Quiz yüklənərkən xəta baş verdi' })
    router.push('/quizzes')
  } finally {
    loading.value = false
  }
}

async function loadBank() {
  if (!quiz.value) return
  bankLoading.value = true
  bank.value = []
  try {
    const params: any =
      bankMode.value === 'lesson'
        ? { lesson_id: quiz.value.lesson_id, per_page: 500 }
        : { per_page: 1000 }
    const res = await questionsApi.list(params)
    bank.value = res.data
  } catch {
    showToast({ type: 'error', text: 'Suallar yüklənərkən xəta baş verdi' })
    bank.value = []
  } finally {
    bankLoading.value = false
  }
}

function switchBank(mode: 'lesson' | 'all') {
  if (bankMode.value === mode) return
  bankMode.value = mode
  loadBank()
}

function isAdded(id: number) {
  return items.value.some((s) => s.id === id)
}

function toggleAdd(q: Question) {
  const idx = items.value.findIndex((s) => s.id === q.id)
  if (idx === -1) {
    items.value.push(q)
    dirty.value = true
  }
}

function removeItem(id: number) {
  const idx = items.value.findIndex((s) => s.id === id)
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
  if (!quiz.value) return
  saving.value = true
  try {
    await quizzesApi.update(quiz.value.id, {
      name: quiz.value.name,
      type: quiz.value.type,
      lesson_id: quiz.value.lesson_id,
      question_ids: items.value.map((q) => q.id),
    })
    showToast({ type: 'success', text: 'Sual sırası saxlanıldı' })
    dirty.value = false
    await loadQuiz()
  } catch {
    showToast({ type: 'error', text: 'Saxlama zamanı xəta baş verdi' })
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  if (!id.value) {
    showToast({ type: 'error', text: 'Quiz ID tapılmadı' })
    router.push('/quizzes')
    return
  }
  await loadQuiz()
  await loadBank()
})
</script>

<template>
  <Toast />

  <div v-if="loading" class="flex items-center justify-center py-20">
    <div class="h-8 w-8 animate-spin rounded-full border-2 border-indigo-600 border-t-transparent" />
  </div>

  <template v-else-if="quiz">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4">
      <div class="flex items-center gap-4">
        <button
          @click="router.push('/quizzes')"
          class="rounded-xl border border-gray-200 bg-white p-2.5 text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-colors"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ quiz.name }}</h1>
          <p class="mt-0.5 text-sm text-gray-500">
            {{ quiz.lesson?.name || `Dərs #${quiz.lesson_id}` }} · {{ quiz.questions?.length || 0 }} sual
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

    <!-- Info -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
      <div class="rounded-xl border border-gray-200 bg-white px-4 py-3.5">
        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Növ</span>
        <p class="mt-1">
          <span :class="quiz.type === 'general' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700'" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
            {{ quiz.type === 'general' ? 'General' : 'Mövzu əsaslı' }}
          </span>
        </p>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white px-4 py-3.5">
        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Sual sayı</span>
        <p class="mt-1 text-lg font-bold text-gray-900">{{ items.length }}</p>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white px-4 py-3.5">
        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Sıralama</span>
        <p class="mt-1 text-sm font-medium text-gray-900">Sürükləyərək dəyişin</p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
      <!-- Bank -->
      <div class="rounded-xl border border-gray-200 bg-white overflow-hidden lg:col-span-2">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 bg-gray-50">
          <h2 class="text-base font-semibold text-gray-900">Sual bankı</h2>
          <div class="flex gap-1.5">
            <button
              @click="switchBank('lesson')"
              :class="bankMode === 'lesson' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200'"
              class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
            >
              Bu dərs
            </button>
            <button
              @click="switchBank('all')"
              :class="bankMode === 'all' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200'"
              class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
            >
              Bütün suallar
            </button>
          </div>
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
