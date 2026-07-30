<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { dashboardApi, type DashboardStats } from '../api/dashboard'
import { showToast } from '../stores/toast'
import Toast from '../components/Toast.vue'

const auth = useAuthStore()
const data = ref<DashboardStats | null>(null)
const loading = ref(true)

onMounted(() => {
  dashboardApi
    .stats()
    .then((res) => { data.value = res.data })
    .catch(() => showToast({ type: 'error', text: 'Statistikalar yüklənərkən xəta baş verdi' }))
    .finally(() => { loading.value = false })
})

// ── Derived ──
const totalContent = computed(() => {
  const d = data.value
  if (!d) return 0
  return d.topics + d.lessons + d.questions + d.quizzes + d.exams
})

const totalActivity = computed(() => {
  const d = data.value
  if (!d) return 0
  return d.total_quiz_attempts + d.total_exam_attempts + d.total_reviews
})

const userTypeColors: Record<string, string> = {
  student: '#6366f1',
  teacher: '#8b5cf6',
  admin: '#f59e0b',
  school: '#06b6d4',
  parent: '#10b981',
}

const userTypeLabels: Record<string, string> = {
  student: 'Tələbə',
  teacher: 'Müəllim',
  admin: 'Admin',
  school: 'Məktəb',
  parent: 'Valideyn',
}

const userTypeTotal = computed(() => {
  return data.value?.user_type_distribution.reduce((s, i) => s + i.count, 0) || 0
})

const donutGradient = computed(() => {
  const items = data.value?.user_type_distribution || []
  if (!items.length || !userTypeTotal.value) return ''
  let accumulated = 0
  const parts = items.map((item) => {
    const pct = (item.count / userTypeTotal.value) * 100
    const start = accumulated
    accumulated += pct
    const color = userTypeColors[item.type] || '#94a3b8'
    return `${color} ${start}% ${accumulated}%`
  })
  return `conic-gradient(${parts.join(', ')})`
})

const maxSignup = computed(() => {
  const vals = data.value?.weekly_signups.map((s) => s.count) || []
  return Math.max(...vals, 1)
})

const userTypeIcon: Record<string, string> = {
  student: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z',
  teacher: 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342',
  admin: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
}

function dayLabel(d: string) {
  const map: Record<string, string> = { Mon: 'Be', Tue: 'Ça', Wed: 'Ç', Thu: 'C', Fri: 'Cü', Sat: 'Ş', Sun: 'B' }
  return map[d] || d
}

function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString('az-AZ', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <Toast />

  <!-- Welcome header -->
  <div class="mb-8">
    <div class="flex items-center gap-4">
      <div
        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 text-xl font-bold text-white shadow-md border-2 border-indigo-200 overflow-hidden"
      >
        <img v-if="auth.user?.avatar && (auth.user.avatar.includes('/') || auth.user.avatar.includes('http'))" :src="auth.user.avatar" class="w-full h-full object-cover" />
        <span v-else>{{ (auth.user?.name?.charAt(0) || 'A').toUpperCase() }}</span>
      </div>
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">
          Xoş gəldiniz, {{ auth.user?.name || 'Admin' }}!
        </h1>
        <p class="mt-1 text-sm text-gray-500">
          RoyaStars admin panelinə xoş gəlmisiniz. Aşağıdan sistem statistikalarını görə bilərsiniz.
        </p>
      </div>
    </div>
  </div>

  <!-- ── Stat cards ── -->
  <div class="mb-8">
    <div class="flex items-center gap-2 mb-4">
      <span class="material-symbols-outlined !text-xl text-indigo-600">monitoring</span>
      <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500">Ümumi Baxış</h2>
    </div>
    <div class="grid gap-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
      <!-- Primary stats -->
      <div class="relative rounded-2xl border border-gray-200 bg-white p-4 shadow-sm overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-indigo-500 to-indigo-600" />
        <div v-if="loading" class="space-y-2">
          <div class="h-3 w-16 animate-pulse rounded bg-gray-100" />
          <div class="h-7 w-12 animate-pulse rounded bg-gray-100" />
        </div>
        <template v-else>
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Şəhərlər</p>
          <p class="mt-1 text-2xl font-bold text-gray-900 tabular-nums">{{ data?.cities?.toLocaleString() || '0' }}</p>
        </template>
      </div>

      <div class="relative rounded-2xl border border-gray-200 bg-white p-4 shadow-sm overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-emerald-500 to-emerald-600" />
        <div v-if="loading" class="space-y-2">
          <div class="h-3 w-16 animate-pulse rounded bg-gray-100" />
          <div class="h-7 w-12 animate-pulse rounded bg-gray-100" />
        </div>
        <template v-else>
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Siniflər</p>
          <p class="mt-1 text-2xl font-bold text-gray-900 tabular-nums">{{ data?.grades?.toLocaleString() || '0' }}</p>
        </template>
      </div>

      <div class="relative rounded-2xl border border-gray-200 bg-white p-4 shadow-sm overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-violet-500 to-violet-600" />
        <div v-if="loading" class="space-y-2">
          <div class="h-3 w-16 animate-pulse rounded bg-gray-100" />
          <div class="h-7 w-12 animate-pulse rounded bg-gray-100" />
        </div>
        <template v-else>
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Mövzular</p>
          <p class="mt-1 text-2xl font-bold text-gray-900 tabular-nums">{{ data?.topics?.toLocaleString() || '0' }}</p>
        </template>
      </div>

      <div class="relative rounded-2xl border border-gray-200 bg-white p-4 shadow-sm overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-sky-500 to-sky-600" />
        <div v-if="loading" class="space-y-2">
          <div class="h-3 w-16 animate-pulse rounded bg-gray-100" />
          <div class="h-7 w-12 animate-pulse rounded bg-gray-100" />
        </div>
        <template v-else>
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Dərslər</p>
          <p class="mt-1 text-2xl font-bold text-gray-900 tabular-nums">{{ data?.lessons?.toLocaleString() || '0' }}</p>
        </template>
      </div>

      <div class="relative rounded-2xl border border-gray-200 bg-white p-4 shadow-sm overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-amber-500 to-amber-600" />
        <div v-if="loading" class="space-y-2">
          <div class="h-3 w-16 animate-pulse rounded bg-gray-100" />
          <div class="h-7 w-12 animate-pulse rounded bg-gray-100" />
        </div>
        <template v-else>
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Suallar</p>
          <p class="mt-1 text-2xl font-bold text-gray-900 tabular-nums">{{ data?.questions?.toLocaleString() || '0' }}</p>
        </template>
      </div>

      <div class="relative rounded-2xl border border-gray-200 bg-white p-4 shadow-sm overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-rose-500 to-rose-600" />
        <div v-if="loading" class="space-y-2">
          <div class="h-3 w-16 animate-pulse rounded bg-gray-100" />
          <div class="h-7 w-12 animate-pulse rounded bg-gray-100" />
        </div>
        <template v-else>
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Quizlər</p>
          <p class="mt-1 text-2xl font-bold text-gray-900 tabular-nums">{{ data?.quizzes?.toLocaleString() || '0' }}</p>
        </template>
      </div>

      <div class="relative rounded-2xl border border-gray-200 bg-white p-4 shadow-sm overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-cyan-500 to-cyan-600" />
        <div v-if="loading" class="space-y-2">
          <div class="h-3 w-16 animate-pulse rounded bg-gray-100" />
          <div class="h-7 w-12 animate-pulse rounded bg-gray-100" />
        </div>
        <template v-else>
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">İmtahanlar</p>
          <p class="mt-1 text-2xl font-bold text-gray-900 tabular-nums">{{ data?.exams?.toLocaleString() || '0' }}</p>
        </template>
      </div>

      <div class="relative rounded-2xl border border-gray-200 bg-white p-4 shadow-sm overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-purple-500 to-purple-600" />
        <div v-if="loading" class="space-y-2">
          <div class="h-3 w-16 animate-pulse rounded bg-gray-100" />
          <div class="h-7 w-12 animate-pulse rounded bg-gray-100" />
        </div>
        <template v-else>
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Tələbələr</p>
          <p class="mt-1 text-2xl font-bold text-gray-900 tabular-nums">{{ data?.students?.toLocaleString() || '0' }}</p>
        </template>
      </div>

      <div class="relative rounded-2xl border border-gray-200 bg-white p-4 shadow-sm overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-orange-500 to-orange-600" />
        <div v-if="loading" class="space-y-2">
          <div class="h-3 w-16 animate-pulse rounded bg-gray-100" />
          <div class="h-7 w-12 animate-pulse rounded bg-gray-100" />
        </div>
        <template v-else>
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Müəllimlər</p>
          <p class="mt-1 text-2xl font-bold text-gray-900 tabular-nums">{{ data?.teachers?.toLocaleString() || '0' }}</p>
        </template>
      </div>

      <div class="relative rounded-2xl border border-gray-200 bg-white p-4 shadow-sm overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-teal-500 to-teal-600" />
        <div v-if="loading" class="space-y-2">
          <div class="h-3 w-16 animate-pulse rounded bg-gray-100" />
          <div class="h-7 w-12 animate-pulse rounded bg-gray-100" />
        </div>
        <template v-else>
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">İstifadəçilər</p>
          <p class="mt-1 text-2xl font-bold text-gray-900 tabular-nums">{{ data?.users?.toLocaleString() || '0' }}</p>
        </template>
      </div>

      <div class="relative rounded-2xl border border-gray-200 bg-white p-4 shadow-sm overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-yellow-500 to-yellow-600" />
        <div v-if="loading" class="space-y-2">
          <div class="h-3 w-16 animate-pulse rounded bg-gray-100" />
          <div class="h-7 w-12 animate-pulse rounded bg-gray-100" />
        </div>
        <template v-else>
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Gözləyən</p>
          <div class="mt-1 flex items-center gap-2">
            <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ data?.pending_users || '0' }}</p>
            <span v-if="(data?.pending_users ?? 0) > 0" class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">Yeni!</span>
          </div>
        </template>
      </div>

      <div class="relative rounded-2xl border border-gray-200 bg-white p-4 shadow-sm overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-pink-500 to-pink-600" />
        <div v-if="loading" class="space-y-2">
          <div class="h-3 w-16 animate-pulse rounded bg-gray-100" />
          <div class="h-7 w-12 animate-pulse rounded bg-gray-100" />
        </div>
        <template v-else>
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Rəylər</p>
          <p class="mt-1 text-2xl font-bold text-gray-900 tabular-nums">{{ data?.total_reviews?.toLocaleString() || '0' }}</p>
          <p v-if="(data?.average_rating ?? 0) > 0" class="mt-0.5 text-xs text-amber-500 font-semibold">
            ★ {{ data?.average_rating }} / 5
          </p>
        </template>
      </div>
    </div>
  </div>

  <!-- ── Engagement row ── -->
  <div class="mb-8 grid gap-4 grid-cols-2 sm:grid-cols-4">
    <div class="rounded-2xl border border-gray-100 bg-gradient-to-br from-indigo-50 to-white p-5 shadow-sm">
      <div class="flex items-center gap-2 text-indigo-600 mb-2">
        <span class="material-symbols-outlined !text-xl">psychology</span>
        <span class="text-[10px] font-bold uppercase tracking-wider">Quiz cəhdləri</span>
      </div>
      <div v-if="loading" class="h-6 w-12 animate-pulse rounded bg-indigo-100" />
      <p v-else class="text-xl font-bold text-gray-900 tabular-nums">{{ data?.total_quiz_attempts?.toLocaleString() || '0' }}</p>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm">
      <div class="flex items-center gap-2 text-amber-600 mb-2">
        <span class="material-symbols-outlined !text-xl">assignment</span>
        <span class="text-[10px] font-bold uppercase tracking-wider">İmtahan cəhdləri</span>
      </div>
      <div v-if="loading" class="h-6 w-12 animate-pulse rounded bg-amber-100" />
      <p v-else class="text-xl font-bold text-gray-900 tabular-nums">{{ data?.total_exam_attempts?.toLocaleString() || '0' }}</p>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm">
      <div class="flex items-center gap-2 text-emerald-600 mb-2">
        <span class="material-symbols-outlined !text-xl">stars</span>
        <span class="text-[10px] font-bold uppercase tracking-wider">Ümumi XP</span>
      </div>
      <div v-if="loading" class="h-6 w-12 animate-pulse rounded bg-emerald-100" />
      <p v-else class="text-xl font-bold text-gray-900 tabular-nums">{{ data?.total_xp?.toLocaleString() || '0' }}</p>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-gradient-to-br from-rose-50 to-white p-5 shadow-sm">
      <div class="flex items-center gap-2 text-rose-600 mb-2">
        <span class="material-symbols-outlined !text-xl">trending_up</span>
        <span class="text-[10px] font-bold uppercase tracking-wider">Bu həftə</span>
      </div>
      <div v-if="loading" class="h-6 w-12 animate-pulse rounded bg-rose-100" />
      <p v-else class="text-xl font-bold text-gray-900 tabular-nums">
        +{{ data?.new_users_week?.toLocaleString() || '0' }}
        <span class="text-xs font-medium text-rose-500 ml-1">yeni istifadəçi</span>
      </p>
    </div>
  </div>

  <!-- ── Charts row ── -->
  <div class="mb-8 grid gap-6 lg:grid-cols-2">
    <!-- User type distribution donut -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
      <div class="flex items-center gap-2 mb-5">
        <span class="material-symbols-outlined !text-xl text-indigo-600">pie_chart</span>
        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">İstifadəçi Paylanması</h3>
      </div>
      <div v-if="loading" class="flex justify-center py-6">
        <div class="h-40 w-40 animate-pulse rounded-full bg-gray-100" />
      </div>
      <div v-else-if="data?.user_type_distribution.length" class="flex flex-col sm:flex-row items-center gap-6">
        <!-- Donut -->
        <div class="relative shrink-0">
          <div
            class="h-40 w-40 rounded-full shadow-inner"
            :style="{ background: donutGradient }"
          />
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="h-20 w-20 rounded-full bg-white flex items-center justify-center shadow-sm">
              <span class="text-2xl font-bold text-gray-700 tabular-nums">{{ userTypeTotal }}</span>
            </div>
          </div>
        </div>
        <!-- Legend -->
        <div class="flex-1 space-y-3 w-full">
          <div
            v-for="item in data.user_type_distribution"
            :key="item.type"
            class="flex items-center justify-between text-sm"
          >
            <div class="flex items-center gap-2">
              <span
                class="inline-block h-3 w-3 rounded-full shrink-0"
                :style="{ background: userTypeColors[item.type] || '#94a3b8' }"
              />
              <svg v-if="userTypeIcon[item.type]" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="userTypeIcon[item.type]" />
              </svg>
              <span class="font-medium text-gray-700">{{ userTypeLabels[item.type] || item.type }}</span>
            </div>
            <div class="flex items-center gap-3">
              <span class="font-bold text-gray-900 tabular-nums">{{ item.count }}</span>
              <span class="text-xs text-gray-400 w-10 text-right tabular-nums">
                {{ ((item.count / userTypeTotal) * 100).toFixed(0) }}%
              </span>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-6 text-sm text-gray-400">Məlumat yoxdur</div>
    </div>

    <!-- Weekly signups bar chart -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
      <div class="flex items-center gap-2 mb-5">
        <span class="material-symbols-outlined !text-xl text-emerald-600">bar_chart</span>
        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">Son 7 Gündə Qeydiyyat</h3>
      </div>
      <div v-if="loading" class="flex items-end justify-between gap-2 py-6" style="height: 180px">
        <div v-for="i in 7" :key="i" class="flex-1 h-24 animate-pulse rounded bg-gray-100" />
      </div>
      <div v-else-if="data?.weekly_signups.length" class="space-y-2">
        <div class="flex items-end justify-between gap-2" style="height: 160px">
          <div
            v-for="day in data.weekly_signups"
            :key="day.date"
            class="flex-1 flex flex-col items-center gap-1"
          >
            <span class="text-[10px] font-bold text-gray-400 tabular-nums">{{ day.count }}</span>
            <div
              class="w-full rounded-lg transition-all duration-500 ease-out"
              :style="{
                height: Math.max((day.count / maxSignup) * 130, 4) + 'px',
                background: day.count > 0
                  ? 'linear-gradient(180deg, #6366f1 0%, #818cf8 100%)'
                  : '#f1f5f9'
              }"
            />
            <span class="text-[10px] font-semibold text-gray-400 mt-1">{{ dayLabel(day.date) }}</span>
          </div>
        </div>
        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
          <div class="flex items-center gap-4 text-xs">
            <span class="text-gray-500">
              <span class="font-bold text-gray-700">Bugün:</span>
              <span class="font-bold text-indigo-600 ml-1">{{ data?.new_users_today || 0 }}</span>
            </span>
            <span class="text-gray-500">
              <span class="font-bold text-gray-700">Bu həftə:</span>
              <span class="font-bold text-indigo-600 ml-1">{{ data?.new_users_week || 0 }}</span>
            </span>
            <span class="text-gray-500">
              <span class="font-bold text-gray-700">Bu ay:</span>
              <span class="font-bold text-indigo-600 ml-1">{{ data?.new_users_month || 0 }}</span>
            </span>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-6 text-sm text-gray-400">Məlumat yoxdur</div>
    </div>
  </div>

  <!-- ── Bottom row: Recent users + Top students ── -->
  <div class="mb-8 grid gap-6 lg:grid-cols-2">
    <!-- Recent users -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
      <div class="flex items-center gap-2 mb-4">
        <span class="material-symbols-outlined !text-xl text-cyan-600">person_add</span>
        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">Son Qeydiyyatlar</h3>
      </div>
      <div v-if="loading" class="space-y-3">
        <div v-for="i in 4" :key="i" class="flex items-center gap-3">
          <div class="h-8 w-8 animate-pulse rounded-full bg-gray-100" />
          <div class="flex-1 space-y-1">
            <div class="h-3 w-24 animate-pulse rounded bg-gray-100" />
            <div class="h-2.5 w-32 animate-pulse rounded bg-gray-100" />
          </div>
        </div>
      </div>
      <div v-else-if="data?.recent_users.length" class="space-y-0">
        <div
          v-for="(u, i) in data.recent_users"
          :key="u.id"
          class="flex items-center gap-3 py-2.5"
          :class="i < data.recent_users.length - 1 ? 'border-b border-gray-50' : ''"
        >
          <div
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white"
            :class="{
              'bg-indigo-500': u.type === 'student',
              'bg-violet-500': u.type === 'teacher',
              'bg-amber-500': u.type === 'admin',
              'bg-cyan-500': u.type === 'school',
              'bg-emerald-500': u.type === 'parent',
            }"
          >
            {{ (u.name?.charAt(0) || '?').toUpperCase() }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 truncate">{{ u.name }}</p>
            <p class="text-xs text-gray-400 truncate">{{ u.email }}</p>
          </div>
          <div class="text-right">
            <span
              class="inline-block rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
              :class="{
                'bg-indigo-50 text-indigo-700': u.type === 'student',
                'bg-violet-50 text-violet-700': u.type === 'teacher',
                'bg-amber-50 text-amber-700': u.type === 'admin',
                'bg-cyan-50 text-cyan-700': u.type === 'school',
                'bg-emerald-50 text-emerald-700': u.type === 'parent',
              }"
            >
              {{ userTypeLabels[u.type] || u.type }}
            </span>
            <p class="text-[10px] text-gray-400 mt-0.5">{{ formatDate(u.created_at) }}</p>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-6 text-sm text-gray-400">Hələ qeydiyyat yoxdur</div>
    </div>

    <!-- Top students by XP -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
      <div class="flex items-center gap-2 mb-4">
        <span class="material-symbols-outlined !text-xl text-amber-500" style="font-variation-settings:'FILL' 1">emoji_events</span>
        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">Ən Yaxşı Tələbələr</h3>
      </div>
      <div v-if="loading" class="space-y-3">
        <div v-for="i in 4" :key="i" class="flex items-center gap-3">
          <div class="h-8 w-8 animate-pulse rounded-full bg-gray-100" />
          <div class="flex-1 space-y-1">
            <div class="h-3 w-24 animate-pulse rounded bg-gray-100" />
            <div class="h-2.5 w-16 animate-pulse rounded bg-gray-100" />
          </div>
        </div>
      </div>
      <div v-else-if="data?.top_students.length" class="space-y-0">
        <div
          v-for="(s, i) in data.top_students"
          :key="s.id"
          class="flex items-center gap-3 py-3"
          :class="i < data.top_students.length - 1 ? 'border-b border-gray-50' : ''"
        >
          <!-- Rank badge -->
          <div
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-black"
            :class="{
              'bg-amber-100 text-amber-700': i === 0,
              'bg-gray-100 text-gray-500': i === 1,
              'bg-orange-50 text-orange-600': i === 2,
              'bg-indigo-50 text-indigo-500': i > 2,
            }"
          >
            {{ i + 1 }}
          </div>
          <!-- Avatar -->
          <div
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-amber-600 text-sm font-bold text-white shadow-sm"
          >
            {{ (s.name?.charAt(0) || '?').toUpperCase() }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 truncate">{{ s.name }}</p>
            <p class="text-xs text-gray-400 truncate">{{ s.email }}</p>
          </div>
          <!-- Points -->
          <div class="flex items-center gap-1">
            <span class="material-symbols-outlined !text-lg text-amber-500" style="font-variation-settings:'FILL' 1">stars</span>
            <span class="text-sm font-bold text-gray-900 tabular-nums">{{ s.total_points?.toLocaleString() || '0' }}</span>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-6 text-sm text-gray-400">Hələ məlumat yoxdur</div>
    </div>
  </div>

  <!-- ── Quick links ── -->
  <div class="mb-8 grid gap-5 sm:grid-cols-2">
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
      <div class="flex items-center gap-3">
        <div class="rounded-xl bg-indigo-50 p-2.5 ring-1 ring-indigo-600/20">
          <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
        </div>
        <div>
          <h2 class="text-sm font-semibold text-gray-900">Tez giriş</h2>
          <p class="text-xs text-gray-500">Sol menyudan modullara keçid edin</p>
        </div>
      </div>
      <div class="mt-4 flex flex-wrap gap-2">
        <router-link
          v-for="btn in [
            { to: '/cities', label: 'Şəhərlər', color: 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100' },
            { to: '/grades', label: 'Siniflər', color: 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' },
            { to: '/lessons', label: 'Dərslər', color: 'bg-violet-50 text-violet-700 hover:bg-violet-100' },
            { to: '/users/pending', label: 'Gözləyən', color: 'bg-amber-50 text-amber-700 hover:bg-amber-100' },
            { to: '/lesson-reviews', label: 'Rəylər', color: 'bg-pink-50 text-pink-700 hover:bg-pink-100' },
          ]"
          :key="btn.to"
          :to="btn.to"
          :class="`rounded-lg px-3 py-1.5 text-xs font-medium transition-colors ${btn.color}`"
        >
          {{ btn.label }}
        </router-link>
      </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
      <div class="flex items-center gap-3">
        <div class="rounded-xl bg-violet-50 p-2.5 ring-1 ring-violet-600/20">
          <svg class="h-5 w-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
        </div>
        <div>
          <h2 class="text-sm font-semibold text-gray-900">Sistem məlumatı</h2>
          <p class="text-xs text-gray-500">Backend API və panel versiyası</p>
        </div>
      </div>
      <div class="mt-4 flex items-center gap-4 text-sm text-gray-500">
        <span>Panel v1.0 — Vue.js + Laravel 12</span>
        <span class="inline-flex items-center gap-1">
          <span class="inline-block h-2 w-2 rounded-full bg-emerald-400 animate-pulse" />
          <span class="text-xs font-medium text-emerald-600">API online</span>
        </span>
      </div>
    </div>
  </div>
</template>
