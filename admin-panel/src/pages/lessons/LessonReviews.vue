<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { lessonReviewsApi, type LessonReview } from '../../api/lesson-reviews'
import Table from '../../components/Table.vue'
import type { Column } from '../../components/Table.vue'
import Pagination from '../../components/Pagination.vue'
import Toast from '../../components/Toast.vue'
import { showToast } from '../../stores/toast'
import type { PaginationMeta } from '../../api/types'
import { h } from 'vue'

const reviews = ref<LessonReview[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(true)
const page = ref(1)
const selectedReview = ref<LessonReview | null>(null)

async function fetchReviews() {
  loading.value = true
  try {
    const res = await lessonReviewsApi.list({ page: page.value, per_page: 20 })
    reviews.value = res.data
    meta.value = res.meta
  } catch {
    showToast({ type: 'error', text: 'Rəylər yüklənərkən xəta baş verdi' })
  } finally {
    loading.value = false
  }
}

watch(page, fetchReviews)
onMounted(fetchReviews)

function viewDetail(review: LessonReview) {
  selectedReview.value = review
}

const columns: Column[] = [
  { key: 'id', label: 'ID', className: 'w-16' },
  {
    key: 'user',
    label: 'Şagird',
    render: (r: LessonReview) => h('div', { class: 'flex items-center gap-2' }, [
      h('div', {
        class: 'flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700 shrink-0',
      }, (r.user.name?.charAt(0) || '?').toUpperCase()),
      h('span', { class: 'font-medium text-gray-900' }, r.user.name),
    ]),
  },
  {
    key: 'lesson',
    label: 'Dərs',
    render: (r: LessonReview) => h('span', { class: 'text-gray-700' }, r.lesson.name),
  },
  {
    key: 'rating',
    label: 'Reytinq',
    className: 'w-28',
    render: (r: LessonReview) => {
      if (!r.rating) return h('span', { class: 'text-xs text-gray-400' }, '—')
      return h('div', { class: 'flex items-center gap-0.5' }, Array.from({ length: 5 }, (_, i) =>
        h('svg', {
          class: `h-4 w-4 ${i < r.rating! ? 'text-amber-400' : 'text-gray-200'}`,
          fill: 'currentColor',
          viewBox: '0 0 20 20',
        }, [
          h('path', { d: 'M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z' }),
        ])
      ))
    },
  },
  {
    key: 'review',
    label: 'Rəy',
    render: (r: LessonReview) => {
      if (!r.review) return h('span', { class: 'text-xs text-gray-400' }, '—')
      const text = r.review.length > 80 ? r.review.slice(0, 80) + '...' : r.review
      return h('span', { class: 'text-sm text-gray-600' }, text)
    },
  },
  {
    key: 'created_at',
    label: 'Tarix',
    className: 'w-28',
    render: (r: LessonReview) => h('span', { class: 'text-sm text-gray-500' }, new Date(r.created_at).toLocaleDateString('az-AZ')),
  },
  {
    key: 'actions',
    label: '',
    className: 'w-16',
    render: (r: LessonReview) => h('button', {
      class: 'text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors',
      onClick: () => viewDetail(r),
    }, 'Bax'),
  },
]
</script>

<template>
  <Toast />

  <div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Dərs Rəyləri</h1>
    <p class="mt-1 text-sm text-gray-500">Şagirdlərin dərslərə verdiyi reytinq və rəylər</p>
  </div>

  <Table
    :columns="columns"
    :data="reviews"
    :loading="loading"
    empty-message="Hələ heç bir rəy yoxdur"
  />

  <Pagination v-if="meta" :meta="meta" @page-change="(p: number) => page = p" />

  <!-- Detail Modal -->
  <Teleport to="body">
    <div
      v-if="selectedReview"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
      <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="selectedReview = null" />
      <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-bold text-gray-900">Rəy Detalları</h3>
          <button
            @click="selectedReview = null"
            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div v-if="selectedReview" class="space-y-4">
          <!-- Student info -->
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">
              {{ (selectedReview.user.name?.charAt(0) || '?').toUpperCase() }}
            </div>
            <div>
              <p class="font-semibold text-gray-900">{{ selectedReview.user.name }}</p>
              <p class="text-sm text-gray-500">{{ selectedReview.user.email }}</p>
            </div>
          </div>

          <!-- Lesson -->
          <div class="rounded-xl bg-gray-50 p-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Dərs</p>
            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ selectedReview.lesson.name }}</p>
          </div>

          <!-- Rating -->
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1.5">Reytinq</p>
            <div class="flex items-center gap-0.5">
              <svg
                v-for="i in 5"
                :key="i"
                :class="`h-6 w-6 ${i <= (selectedReview.rating || 0) ? 'text-amber-400' : 'text-gray-200'}`"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              <span v-if="!selectedReview.rating" class="text-sm text-gray-400 ml-2">Reytinq verilməyib</span>
            </div>
          </div>

          <!-- Review text -->
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1.5">Rəy</p>
            <div class="rounded-xl bg-gray-50 p-4">
              <p v-if="selectedReview.review" class="text-sm text-gray-700 whitespace-pre-wrap">{{ selectedReview.review }}</p>
              <p v-else class="text-sm text-gray-400 italic">Rəy yazılmayıb</p>
            </div>
          </div>

          <!-- Date -->
          <p class="text-xs text-gray-400">
            {{ new Date(selectedReview.created_at).toLocaleDateString('az-AZ', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
          </p>
        </div>

        <div class="mt-6 flex justify-end">
          <button
            @click="selectedReview = null"
            class="rounded-xl bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors"
          >
            Bağla
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
