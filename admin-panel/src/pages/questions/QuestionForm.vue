<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { topicsApi, type Topic } from '../../api/topics'
import { lessonsApi, type Lesson } from '../../api/lessons'
import {
  questionsApi,
  flattenBlocks,
  type Question,
} from '../../api/questions'
import Toast from '../../components/Toast.vue'
import ContentBlockEditor from '../../components/ContentBlockEditor.vue'
import type { ContentBlock } from '../../components/ContentBlockEditor.vue'
import VariantMediaUpload from '../../components/VariantMediaUpload.vue'
import { showToast } from '../../stores/toast'

const route = useRoute()
const router = useRouter()

const editingId = computed(() => route.params.id ? Number(route.params.id) : null)

const topics = ref<Topic[]>([])
const selectedTopicId = ref<number | null>(null)
const lessons = ref<Lesson[]>([])
const selectedLessonId = ref<number | null>(null)
const loading = ref(true)
const skipLessonWatch = ref(false)

// Form state
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
const explanationVideoUrl = ref('')
const difficulty = ref<number>(3)
const saving = ref(false)
const formError = ref('')

const DIFFICULTY_OPTIONS = [
  { value: 1, label: 'Başlanğıc' },
  { value: 2, label: 'Elementar' },
  { value: 3, label: 'Orta' },
  { value: 4, label: 'Qabaqcıl' },
  { value: 5, label: 'Ekspert' },
]

const VARIANT_TYPE_OPTIONS = [
  { value: 'text' as const, label: 'Mətn' },
  { value: 'image' as const, label: 'Şəkil' },
  { value: 'audio' as const, label: 'Səs' },
]

const variantType = ref<ContentBlock['type']>('text')

function resetForm() {
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
  explanationVideoUrl.value = ''
  difficulty.value = 3
  formError.value = ''
}

function populateForm(q: Question) {
  questionBlocks.value = flattenBlocks(q.question)
  if (!questionBlocks.value.length) questionBlocks.value = [{ type: 'text', content: '' }]
  questionType.value = q.type
  const flatA = flattenBlocks(q.variant_a)
  const flatB = flattenBlocks(q.variant_b)
  const flatC = flattenBlocks(q.variant_c)
  const flatD = flattenBlocks(q.variant_d)
  const flatE = flattenBlocks(q.variant_e)
  variantABlocks.value = flatA.length ? flatA : [{ type: 'text', content: '' }]
  variantBBlocks.value = flatB.length ? flatB : [{ type: 'text', content: '' }]
  variantCBlocks.value = flatC.length ? flatC : [{ type: 'text', content: '' }]
  const vt = (variantABlocks.value[0]?.type || 'text') as ContentBlock['type']
  variantType.value = vt
  variantDBlocks.value = flatD.length ? flatD : [{ type: vt, content: '' }]
  variantEBlocks.value = flatE.length ? flatE : [{ type: vt, content: '' }]
  rightAnswer.value = q.right_answer || ''
  openAnswerBlocks.value = flattenBlocks(q.open_answer)
  answerType.value = q.answer_type || ''
  explanationBlocks.value = flattenBlocks(q.explanation)
  explanationVideoUrl.value = q.explanation_video_url || ''
  difficulty.value = q.difficulty_level
}

async function loadLessons(topicId: number) {
  const res = await lessonsApi.list(topicId, { per_page: 100 })
  lessons.value = res.data
}

function onVariantTypeChange(type: ContentBlock['type']) {
  variantType.value = type
  variantABlocks.value = [{ type, content: '' }]
  variantBBlocks.value = [{ type, content: '' }]
  variantCBlocks.value = [{ type, content: '' }]
  variantDBlocks.value = [{ type, content: '' }]
  variantEBlocks.value = [{ type, content: '' }]
  rightAnswer.value = ''
}

watch(selectedTopicId, async () => {
  if (skipLessonWatch.value) return
  selectedLessonId.value = null
  lessons.value = []
  if (!selectedTopicId.value) return
  await loadLessons(selectedTopicId.value)
  if (!editingId.value && lessons.value.length) {
    selectedLessonId.value = lessons.value[0].id
  }
})

onMounted(async () => {
  loading.value = true
  try {
    const res = await topicsApi.list({ per_page: 100 })
    topics.value = res.data

    if (editingId.value) {
      const qRes = await questionsApi.show(editingId.value)
      const q = qRes.data
      // Select the lesson's topic if known, else fall back to first topic.
      skipLessonWatch.value = true
      const topicId = q.topic_id ?? topics.value[0]?.id ?? null
      if (topicId) {
        selectedTopicId.value = topicId
        await loadLessons(topicId)
      }
      selectedLessonId.value = q.lesson_id
      populateForm(q)
      skipLessonWatch.value = false
    } else {
      // New question: start empty — the admin picks the lesson from within the form.
      resetForm()
      selectedTopicId.value = null
      selectedLessonId.value = null
      lessons.value = []
    }
  } catch (err: any) {
    showToast({ type: 'error', text: editingId.value ? 'Sual yüklənərkən xəta baş verdi' : 'Məlumat yüklənərkən xəta baş verdi' })
    if (editingId.value) router.push('/questions')
  } finally {
    loading.value = false
  }
})

function hasContent(blocks: ContentBlock[]): boolean {
  return blocks.some(b => b.content.trim().length > 0)
}

function buildPayload() {
  const payload: any = {
    question: questionBlocks.value,
    type: questionType.value,
    lesson_id: selectedLessonId.value,
    difficulty_level: difficulty.value,
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

  if (explanationVideoUrl.value.trim()) {
    payload.explanation_video_url = explanationVideoUrl.value.trim()
  }

  return payload
}

function validate(): string {
  if (!hasContent(questionBlocks.value)) return 'Sual mətni tələb olunur'
  if (!selectedLessonId.value) return 'Dərs seçilməlidir'
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
    const payload = buildPayload()

    if (editingId.value) {
      await questionsApi.update(editingId.value, payload)
      showToast({ type: 'success', text: 'Sual yeniləndi' })
    } else {
      await questionsApi.create(payload)
      showToast({ type: 'success', text: 'Sual yaradıldı' })
    }
    router.push('/questions')
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
    <div class="flex items-center gap-3">
      <button
        @click="router.push('/questions')"
        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 transition-colors"
        title="Geri"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ editingId ? 'Sualı redaktə et' : 'Yeni sual' }}</h1>
        <p class="mt-1 text-sm text-gray-500">
          {{ editingId ? 'Mövcud sualın məlumatlarını dəyişin' : 'Dərs üçün yeni test sualı yaradın' }}
        </p>
      </div>
    </div>
  </div>

  <div v-if="loading" class="space-y-4">
    <div class="h-64 animate-pulse rounded-2xl bg-gray-100" />
  </div>

  <form v-else class="w-full max-w-7xl space-y-5" @submit.prevent="handleSave">
    <p v-if="formError" class="rounded-xl bg-red-50 border border-red-200 px-4 py-2.5 text-sm text-red-600">
      {{ formError }}
    </p>

    <!-- Lesson info -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm space-y-4">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Mövzu</label>
          <select
            v-model="selectedTopicId"
            class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
          >
            <option :value="null">Mövzu seçin...</option>
            <option v-for="t in topics" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Dərs <span class="text-red-400">*</span>
          </label>
          <select
            v-model="selectedLessonId"
            :disabled="!selectedTopicId"
            class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <option :value="null">Dərs seçin...</option>
            <option v-for="l in lessons" :key="l.id" :value="l.id">{{ l.name }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Question type -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm space-y-4">
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
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
          Sual mətni <span class="text-red-400">*</span>
        </label>
        <ContentBlockEditor v-model="questionBlocks" placeholder="Sual mətnini daxil edin" />
      </div>
    </div>

    <!-- Variants (regular) -->
    <div v-if="questionType === 'regular'" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm space-y-4">
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

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
          <VariantMediaUpload
            v-else
            :model-value="variantABlocks[0]"
            :variant-type="variantType"
            @update:model-value="variantABlocks[0] = $event"
          />
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
          <VariantMediaUpload
            v-else
            :model-value="variantBBlocks[0]"
            :variant-type="variantType"
            @update:model-value="variantBBlocks[0] = $event"
          />
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
          <VariantMediaUpload
            v-else
            :model-value="variantCBlocks[0]"
            :variant-type="variantType"
            @update:model-value="variantCBlocks[0] = $event"
          />
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
          <VariantMediaUpload
            v-else
            :model-value="variantDBlocks[0]"
            :variant-type="variantType"
            @update:model-value="variantDBlocks[0] = $event"
          />
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
          <VariantMediaUpload
            v-else
            :model-value="variantEBlocks[0]"
            :variant-type="variantType"
            @update:model-value="variantEBlocks[0] = $event"
          />
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
    </div>

    <!-- Open answer -->
    <div v-if="questionType === 'open'" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
          Cavab <span class="text-red-400">*</span>
        </label>
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
    </div>

    <!-- Difficulty + Explanation -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Çətinlik səviyyəsi</label>
        <select
          v-model="difficulty"
          class="w-full max-w-xs rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
        >
          <option v-for="opt in DIFFICULTY_OPTIONS" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">İzah (istəyə bağlı)</label>
        <ContentBlockEditor v-model="explanationBlocks" placeholder="Sualın izahı" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
          İzah videosu URL <span class="text-gray-400 font-normal">(istəyə bağlı)</span>
        </label>
        <input
          v-model="explanationVideoUrl"
          type="url"
          placeholder="https://www.youtube.com/watch?v=..."
          class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 placeholder-gray-400 transition-colors focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100"
        />
        <p class="mt-1 text-xs text-gray-400">
          YouTube və ya birbaşa video linki. Şagird sualı cavabladıqdan sonra bu video göstərilir.
        </p>
      </div>
    </div>

    <div class="flex justify-end gap-3 pt-2">
      <button
        type="button"
        @click="router.push('/questions')"
        class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
      >
        İmtina
      </button>
      <button
        type="submit"
        :disabled="saving"
        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 transition-colors disabled:opacity-50"
      >
        {{ saving ? 'Saxlanılır...' : editingId ? 'Yadda saxla' : 'Yarat' }}
      </button>
    </div>
  </form>
</template>
