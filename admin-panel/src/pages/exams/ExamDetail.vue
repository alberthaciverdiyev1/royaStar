<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { examsApi, type Exam } from '../../api/exams'
import { fromContentBlock } from '../../api/questions'
import Toast from '../../components/Toast.vue'
import { showToast } from '../../stores/toast'

const route = useRoute()
const router = useRouter()
const exam = ref<Exam | null>(null)
const loading = ref(true)

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

onMounted(async () => {
  const id = Number(route.params.id)
  if (!id) {
    showToast({ type: 'error', text: 'İmtahan ID tapılmadı' })
    router.push('/exams')
    return
  }

  try {
    const res = await examsApi.show(id)
    exam.value = res.data
  } catch {
    showToast({ type: 'error', text: 'İmtahan yüklənərkən xəta baş verdi' })
    router.push('/exams')
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <Toast />

  <div v-if="loading" class="flex items-center justify-center py-20">
    <div class="h-8 w-8 animate-spin rounded-full border-2 border-indigo-600 border-t-transparent" />
  </div>

  <template v-else-if="exam">
    <!-- Header -->
    <div class="mb-6 flex items-center gap-4">
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
        <p class="mt-0.5 text-sm text-gray-500">İmtahan detalları</p>
      </div>
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
        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Sinif</span>
        <p class="mt-1 text-sm font-medium text-gray-900">{{ exam.grade?.name || `Sinif #${exam.grade_id}` }}</p>
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

    <!-- Description -->
    <div v-if="exam.description" class="mb-6 rounded-xl border border-gray-200 bg-white px-4 py-3.5">
      <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Açıqlama</span>
      <p class="mt-1 text-sm text-gray-700">{{ exam.description }}</p>
    </div>

    <!-- Questions -->
    <div>
      <h2 class="mb-3 text-lg font-semibold text-gray-900">İmtahandakı suallar ({{ exam.questions?.length || 0 }})</h2>

      <div v-if="!exam.questions?.length" class="rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/50 p-8 text-center">
        <p class="text-sm text-gray-400">İmtahanda heç bir sual yoxdur</p>
      </div>

      <div v-else class="space-y-2">
        <div
          v-for="(q, i) in exam.questions"
          :key="q.id"
          class="rounded-xl border border-gray-200 bg-white px-4 py-3"
        >
          <div class="flex items-start gap-3">
            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-600">{{ i + 1 }}</span>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900">{{ fromContentBlock(q.question) || `Sual #${q.id}` }}</p>
              <div class="mt-1 flex flex-wrap items-center gap-2">
                <span :class="q.type === 'open' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700'" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium">
                  {{ q.type === 'open' ? 'Açıq' : 'Test' }}
                </span>
                <span class="text-xs text-gray-400">Çətinlik: {{ DIFFICULTY_OPTIONS[q.difficulty_level] || q.difficulty_level }}</span>
                <span v-if="q.right_answer" class="text-xs text-gray-400">
                  Cavab: <span class="font-semibold text-green-600">{{ q.right_answer.toUpperCase() }}</span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
</template>
