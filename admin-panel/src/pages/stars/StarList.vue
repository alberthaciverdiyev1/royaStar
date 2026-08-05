<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import {
  starsApi, type Star,
  CATEGORY_LABELS, CATEGORY_COLORS, GROUP_LABELS,
} from '../../api/stars'
import Toast from '../../components/Toast.vue'
import { showToast } from '../../stores/toast'

const stars = ref<Star[]>([])
const loading = ref(true)
const saving = ref<Record<number, boolean>>({})
const savingToggle = ref<Record<number, boolean>>({})
const dirtyPoints = ref<Record<number, number>>({})

const groupedStars = computed(() => {
  const groups: Record<string, Star[]> = {}
  for (const s of stars.value) {
    const cat = s.category || 'other'
    if (!groups[cat]) groups[cat] = []
    groups[cat].push(s)
  }
  return groups
})

const categoryOrder = ['engagement', 'learning', 'achievement']

onMounted(fetchStars)

async function fetchStars() {
  loading.value = true
  try {
    const res = await starsApi.list()
    stars.value = res.data
    // init dirty points
    for (const s of res.data) {
      dirtyPoints.value[s.id] = s.point
    }
  } catch {
    showToast({ type: 'error', text: 'Ulduzlar yüklənərkən xəta baş verdi' })
  } finally {
    loading.value = false
  }
}

function isDirty(star: Star): boolean {
  return dirtyPoints.value[star.id] !== star.point
}

function resetPoint(star: Star) {
  dirtyPoints.value[star.id] = star.point_default > 0 ? star.point_default : star.point
}

function clampPoint(star: Star, val: number): number {
  if (star.point_max && val > star.point_max) return star.point_max
  if (val < star.point_min) return star.point_min
  return val
}

function onPointInput(star: Star, val: string) {
  const num = parseInt(val) || 0
  dirtyPoints.value[star.id] = num
}

function onPointBlur(star: Star) {
  dirtyPoints.value[star.id] = clampPoint(star, dirtyPoints.value[star.id])
}

async function savePoint(star: Star) {
  const newPoint = dirtyPoints.value[star.id]
  if (newPoint === star.point) return
  saving.value[star.id] = true
  try {
    const res = await starsApi.update(star.id, { point: newPoint })
    star.point = res.data.point
    dirtyPoints.value[star.id] = res.data.point
    showToast({ type: 'success', text: `"${star.name || star.type}" üçün bal yeniləndi: ${res.data.point}` })
  } catch {
    dirtyPoints.value[star.id] = star.point
    showToast({ type: 'error', text: 'Bal yenilənərkən xəta baş verdi' })
  } finally {
    saving.value[star.id] = false
  }
}

async function toggleActive(star: Star) {
  savingToggle.value[star.id] = true
  try {
    const res = await starsApi.update(star.id, { is_active: !star.is_active })
    star.is_active = res.data.is_active
    showToast({
      type: 'success',
      text: `"${star.name || star.type}" ${star.is_active ? 'aktiv edildi' : 'deaktiv edildi'}`,
    })
  } catch {
    showToast({ type: 'error', text: 'Status dəyişdirilərkən xəta baş verdi' })
  } finally {
    savingToggle.value[star.id] = false
  }
}

function nameFallback(star: Star): string {
  return star.name || star.type
}

function descFallback(star: Star): string {
  return star.description || ''
}
</script>

<template>
  <Toast />

  <div class="mb-6 flex flex-col gap-2">
    <h1 class="text-2xl font-bold text-gray-900">Ulduz Sistemi ⭐</h1>
    <p class="text-sm text-gray-500">
      Bütün ulduz növləri əvvəlcədən təyin edilib. Admin yalnız qazanılacaq balları və aktivlik statusunu dəyişə bilər.
    </p>
  </div>

  <!-- Loading -->
  <div v-if="loading" class="flex items-center justify-center py-20">
    <div class="h-8 w-8 animate-spin rounded-full border-4 border-indigo-200 border-t-indigo-600" />
  </div>

  <!-- Content -->
  <div v-else class="space-y-8">

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
      <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Ümumi növ</p>
        <p class="mt-1 text-2xl font-bold text-gray-900">{{ stars.length }}</p>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Aktiv</p>
        <p class="mt-1 text-2xl font-bold text-green-600">{{ stars.filter(s => s.is_active).length }}</p>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Deaktiv</p>
        <p class="mt-1 text-2xl font-bold text-gray-400">{{ stars.filter(s => !s.is_active).length }}</p>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Cəmi (default)</p>
        <p class="mt-1 text-2xl font-bold text-amber-600">
          {{ stars.reduce((t, s) => t + (s.is_active ? s.point : 0), 0) }}
        </p>
      </div>
    </div>

    <!-- Grouped Cards -->
    <div v-for="cat in categoryOrder" :key="cat">
      <div v-if="groupedStars[cat]?.length" class="space-y-3">
        <h2 class="text-lg font-semibold text-gray-800">
          {{ CATEGORY_LABELS[cat] || cat }}
        </h2>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
          <div
            v-for="star in groupedStars[cat]"
            :key="star.id"
            :class="[
              'group relative rounded-xl border p-4 transition-all duration-200',
              star.is_active
                ? 'border-gray-200 bg-white hover:shadow-md hover:border-gray-300'
                : 'border-gray-100 bg-gray-50 opacity-60',
            ]"
          >
            <!-- Inactive badge -->
            <div
              v-if="!star.is_active"
              class="absolute -top-2 right-3 rounded-full bg-gray-200 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-500"
            >
              Deaktiv
            </div>

            <!-- Header -->
            <div class="flex items-start gap-3">
              <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-50 text-lg ring-1 ring-gray-200">
                {{ star.icon || '⭐' }}
              </span>
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                  <h3 class="truncate text-sm font-semibold text-gray-900">
                    {{ nameFallback(star) }}
                  </h3>
                  <span
                    :class="[
                      'inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-medium',
                      CATEGORY_COLORS[star.category || ''] || 'bg-gray-50 text-gray-600 border-gray-200',
                    ]"
                  >
                    {{ star.group ? (GROUP_LABELS[star.group] || star.group) : '' }}
                  </span>
                </div>
                <p v-if="descFallback(star)" class="mt-0.5 text-xs text-gray-500 line-clamp-1">
                  {{ descFallback(star) }}
                </p>
              </div>
            </div>

            <!-- Point Editor -->
            <div class="mt-4 flex items-center gap-3">
              <div class="flex-1">
                <label class="block text-[10px] font-medium uppercase tracking-wide text-gray-400 mb-1">
                  Bal dəyəri
                </label>
                <div class="flex items-center gap-2">
                  <input
                    type="number"
                    :min="star.point_min"
                    :max="star.point_max ?? undefined"
                    :disabled="!star.is_active"
                    :value="dirtyPoints[star.id] ?? star.point"
                    @input="onPointInput(star, ($event.target as HTMLInputElement).value)"
                    @blur="onPointBlur(star)"
                    class="w-24 rounded-lg border border-gray-200 bg-white py-1.5 px-2.5 text-sm font-semibold text-gray-900 transition-colors focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
                  />
                  <span class="text-[11px] text-gray-400">
                    {{ star.point_min }}-{{ star.point_max ?? '∞' }}
                  </span>

                  <!-- Preset quick buttons -->
                  <div class="flex gap-1">
                    <button
                      v-for="p in [1, 5, 10, 20, 50]"
                      :key="p"
                      @click="dirtyPoints[star.id] = clampPoint(star, p)"
                      :disabled="!star.is_active"
                      :class="[
                        'rounded px-2 py-1 text-[10px] font-medium transition-colors disabled:opacity-40 disabled:cursor-not-allowed',
                        dirtyPoints[star.id] === p
                          ? 'bg-indigo-100 text-indigo-700'
                          : 'bg-gray-50 text-gray-500 hover:bg-gray-100',
                      ]"
                    >
                      {{ p }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex flex-col gap-1.5">
                <!-- Save button -->
                <button
                  v-if="isDirty(star)"
                  @click="savePoint(star)"
                  :disabled="saving[star.id]"
                  class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-indigo-700 disabled:opacity-50"
                >
                  <svg v-if="saving[star.id]" class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                  </svg>
                  <span v-else>Yadda saxla</span>
                </button>

                <!-- Reset to default -->
                <button
                  v-if="star.point_default > 0 && star.point !== star.point_default"
                  @click="resetPoint(star)"
                  :disabled="!star.is_active"
                  class="rounded-lg border border-gray-200 px-3 py-1.5 text-[10px] font-medium text-gray-500 transition-colors hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
                >
                  Default: {{ star.point_default }}
                </button>
              </div>
            </div>

            <!-- Range indicator bar -->
            <div class="mt-3 h-1.5 w-full rounded-full bg-gray-100 overflow-hidden">
              <div
                class="h-full rounded-full transition-all duration-300"
                :class="[
                  star.is_active ? 'bg-amber-400' : 'bg-gray-200',
                ]"
                :style="{ width: star.point_max ? Math.min(100, ((dirtyPoints[star.id] ?? star.point) / star.point_max) * 100) + '%' : '0%' }"
              />
            </div>

            <!-- Footer -->
            <div class="mt-2 flex items-center justify-between">
              <span class="text-[10px] text-gray-400">{{ star.type }}</span>

              <!-- Active toggle -->
              <button
                @click="toggleActive(star)"
                :disabled="savingToggle[star.id]"
                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                :class="[star.is_active ? 'bg-green-500' : 'bg-gray-200']"
              >
                <span
                  class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                  :class="[star.is_active ? 'translate-x-4' : 'translate-x-0']"
                />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>
