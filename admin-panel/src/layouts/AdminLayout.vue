<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute, RouterView } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import NavIcon from '../components/NavIcon.vue'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const sidebarOpen = ref(false)

interface NavItem {
  to: string
  label: string
  icon: string
  exact?: boolean
}

const navItems: NavItem[] = [
  { to: '/', label: 'Dashboard', icon: 'dashboard', exact: true },
  { to: '/cities', label: 'Şəhərlər', icon: 'city' },
  { to: '/grades', label: 'Siniflər', icon: 'grade' },
  { to: '/subjects', label: 'Fənlər', icon: 'subject' },
  { to: '/topics', label: 'Mövzular', icon: 'topic' },
  { to: '/lessons', label: 'Dərslər', icon: 'lesson' },
  { to: '/questions', label: 'Suallar', icon: 'question' },
  { to: '/students', label: 'Tələbələr', icon: 'student' },
  { to: '/quizzes', label: 'Quizlər', icon: 'quiz' },
  { to: '/exams', label: 'İmtahanlar', icon: 'exam' },
  { to: '/stars', label: 'Ulduzlar', icon: 'star' },
  { to: '/payments', label: 'Ödənişlər', icon: 'payment' },
]

function isActive(item: NavItem): boolean {
  if (item.exact) return route.path === item.to
  return route.path.startsWith(item.to)
}

function handleLogout() {
  auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="flex h-screen bg-slate-50">
    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-20 bg-black/40 backdrop-blur-sm lg:hidden"
      @click="sidebarOpen = false"
    />

    <!-- Sidebar -->
    <aside
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
      class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col bg-white shadow-lg shadow-slate-200/50 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0"
    >
      <!-- Logo -->
      <div class="flex h-16 items-center gap-2.5 border-b border-slate-100 px-6">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-700 text-sm font-bold text-white shadow-sm">
          R
        </div>
        <div>
          <span class="text-base font-bold text-slate-900">RoyaStars</span>
          <span class="ml-2 rounded-md bg-indigo-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-indigo-600">
            Admin
          </span>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4">
        <router-link
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          @click="sidebarOpen = false"
          :class="[
            'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150',
            isActive(item)
              ? 'bg-indigo-50 text-indigo-700 shadow-sm'
              : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
          ]"
        >
          <NavIcon :icon="item.icon" :active="isActive(item)" />
          {{ item.label }}
        </router-link>
      </nav>

      <!-- User info -->
      <div class="border-t border-slate-100 px-4 py-4">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 text-sm font-semibold text-white shadow-sm">
            {{ (auth.user?.name?.charAt(0) || 'A').toUpperCase() }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="truncate text-sm font-semibold text-slate-900">{{ auth.user?.name || 'Admin' }}</p>
            <p class="truncate text-xs text-slate-400">{{ auth.user?.email || '' }}</p>
          </div>
          <button
            @click="handleLogout"
            class="shrink-0 rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors"
            title="Çıxış"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main area -->
    <div class="flex flex-1 flex-col overflow-hidden">
      <!-- Top bar (mobile) -->
      <header class="flex h-16 items-center gap-3 border-b border-slate-200 bg-white px-4 lg:hidden">
        <button
          @click="sidebarOpen = true"
          class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 transition-colors"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <div class="flex items-center gap-2">
          <div class="flex h-7 w-7 items-center justify-center rounded-md bg-gradient-to-br from-indigo-500 to-indigo-700 text-xs font-bold text-white">
            R
          </div>
          <span class="font-semibold text-slate-900">RoyaStars Admin</span>
        </div>
      </header>

      <!-- Page content -->
      <main class="flex-1 overflow-y-auto">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
          <RouterView />
        </div>
      </main>
    </div>
  </div>
</template>
