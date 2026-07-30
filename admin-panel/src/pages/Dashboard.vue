<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { dashboardApi, type DashboardStats } from '../api/dashboard'
import { showToast } from '../stores/toast'
import Toast from '../components/Toast.vue'

const auth = useAuthStore()
const data = ref<DashboardStats | null>(null)
const loading = ref(true)

type StatColor = 'indigo' | 'emerald' | 'violet' | 'amber' | 'rose' | 'cyan'

interface StatDef {
  label: string
  key: keyof DashboardStats
  color: StatColor
  icon: string
}

const stats: StatDef[] = [
  { label: 'Şəhərlər', key: 'cities', color: 'indigo', icon: 'building' },
  { label: 'Siniflər', key: 'grades', color: 'emerald', icon: 'school' },
  { label: 'Dərslər', key: 'lessons', color: 'violet', icon: 'book' },
  { label: 'Tələbələr', key: 'students', color: 'amber', icon: 'users' },
  { label: 'Müəllimlər', key: 'teachers', color: 'rose', icon: 'teacher' },
  { label: 'İstifadəçilər', key: 'users', color: 'cyan', icon: 'person' },
]

const gradientMap: Record<StatColor, string> = {
  indigo: 'from-indigo-500 to-indigo-600',
  emerald: 'from-emerald-500 to-emerald-600',
  violet: 'from-violet-500 to-violet-600',
  amber: 'from-amber-500 to-amber-600',
  rose: 'from-rose-500 to-rose-600',
  cyan: 'from-cyan-500 to-cyan-600',
}

const bgMap: Record<StatColor, string> = {
  indigo: 'bg-indigo-50',
  emerald: 'bg-emerald-50',
  violet: 'bg-violet-50',
  amber: 'bg-amber-50',
  rose: 'bg-rose-50',
  cyan: 'bg-cyan-50',
}

const ringMap: Record<StatColor, string> = {
  indigo: 'ring-indigo-600/20',
  emerald: 'ring-emerald-600/20',
  violet: 'ring-violet-600/20',
  amber: 'ring-amber-600/20',
  rose: 'ring-rose-600/20',
  cyan: 'ring-cyan-600/20',
}

onMounted(() => {
  dashboardApi
    .stats()
    .then((res) => { data.value = res.data })
    .catch(() => showToast({ type: 'error', text: 'Statistikalar yüklənərkən xəta baş verdi' }))
    .finally(() => { loading.value = false })
})

function StatIcon({ icon }: { icon: string }) {
  switch (icon) {
    case 'building':
      return 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
    case 'school':
      return 'M12 14l9-5-9-5-9 5 9 5z'
    case 'book':
      return 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'
    case 'users':
      return 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z'
    case 'teacher':
      return 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342'
    case 'person':
      return 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'
    default:
      return ''
  }
}
</script>

<template>
  <Toast />

  <!-- Welcome section -->
  <div class="mb-8">
    <div class="flex items-center gap-4">
      <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 text-lg font-bold text-white shadow-sm border border-indigo-200 overflow-hidden">
        <img v-if="auth.user?.avatar && (auth.user.avatar.includes('/') || auth.user.avatar.includes('http'))" :src="auth.user.avatar" class="w-full h-full object-cover" />
        <span v-else-if="auth.user?.avatar" class="text-xl select-none">{{ auth.user.avatar }}</span>
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

  <!-- Stats grid -->
  <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
    <div
      v-for="stat in stats"
      :key="stat.key"
      class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:shadow-md hover:border-gray-200"
    >
      <!-- Gradient top accent -->
      <div
        :class="`absolute top-0 left-0 right-0 h-1 bg-gradient-to-r ${gradientMap[stat.color]}`"
      />

      <div
        :class="`mb-3 inline-flex rounded-xl ${bgMap[stat.color]} p-2.5 ring-1 ${ringMap[stat.color]}`"
      >
        <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="StatIcon({ icon: stat.icon })" />
        </svg>
      </div>

      <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
        {{ stat.label }}
      </p>

      <div v-if="loading" class="mt-1.5 h-7 w-16 animate-pulse rounded-md bg-gray-100" />
      <p v-else class="mt-1.5 text-2xl font-bold text-gray-900 tabular-nums">
        {{ (data ? data[stat.key] : 0)?.toLocaleString() || '0' }}
      </p>
    </div>
  </div>

  <!-- Quick links -->
  <div class="mt-8 grid gap-5 sm:grid-cols-2">
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
            { to: '/students', label: 'Tələbələr', color: 'bg-amber-50 text-amber-700 hover:bg-amber-100' },
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
      <div class="mt-4 space-y-1.5 text-sm text-gray-500">
        <p>Panel v1.0 — Vue.js + Laravel 12</p>
        <p>API endpoint: <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600 font-mono">/api</code></p>
      </div>
    </div>
  </div>
</template>
