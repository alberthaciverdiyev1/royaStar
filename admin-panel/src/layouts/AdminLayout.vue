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
  { to: '/topics', label: 'Mövzular', icon: 'topic' },
  { to: '/lessons', label: 'Dərslər', icon: 'lesson' },
  { to: '/lesson-reviews', label: 'Rəylər', icon: 'review' },
  { to: '/questions', label: 'Suallar', icon: 'question' },
  { to: '/quizzes', label: 'Quizlər', icon: 'quiz' },
  { to: '/exams', label: 'İmtahanlar', icon: 'exam' },
  { to: '/stars', label: 'Ulduzlar', icon: 'star' },
  { to: '/students', label: 'Şagirdlər', icon: 'student' },
  { to: '/users', label: 'İstifadəçilər', icon: 'users' },
  { to: '/website-texts', label: 'Sayt Mətnləri', icon: 'text' },
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
  <div class="flex h-screen bg-slate-50 overflow-hidden">
    <!-- Mobile Backdrop Overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs lg:hidden transition-opacity"
      @click="sidebarOpen = false"
    />

    <!-- Sidebar Drawer -->
    <aside
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
      class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white shadow-2xl transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 lg:shadow-md"
    >
      <!-- Logo -->
      <div class="flex h-16 items-center justify-between border-b border-slate-100 px-6">
        <div class="flex items-center gap-2.5">
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

        <!-- Mobile close button -->
        <button
          @click="sidebarOpen = false"
          class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 lg:hidden"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
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
              ? 'bg-indigo-50 text-indigo-700 font-bold shadow-xs'
              : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
          ]"
        >
          <NavIcon :icon="item.icon" :active="isActive(item)" />
          {{ item.label }}
        </router-link>
      </nav>

      <!-- User info Footer -->
      <div class="border-t border-slate-100 px-4 py-4">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 text-sm font-semibold text-white shadow-sm overflow-hidden border border-indigo-200">
            <img v-if="auth.user?.avatar && (auth.user.avatar.includes('/') || auth.user.avatar.includes('http'))" :src="auth.user.avatar" class="w-full h-full object-cover" />
            <span v-else-if="auth.user?.avatar" class="text-base select-none">{{ auth.user.avatar }}</span>
            <span v-else>{{ (auth.user?.name?.charAt(0) || 'A').toUpperCase() }}</span>
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

    <!-- Main Content Area -->
    <div class="flex flex-1 flex-col overflow-hidden min-w-0">
      <!-- Mobile Top Navbar Header -->
      <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 lg:hidden shrink-0 shadow-xs">
        <button
          @click="sidebarOpen = true"
          class="rounded-xl border border-slate-200 bg-slate-50 p-2 text-slate-700 hover:bg-slate-100 transition-colors"
          aria-label="Toggle Navigation"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <div class="flex items-center gap-2">
          <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-700 text-xs font-bold text-white shadow-xs">
            R
          </div>
          <span class="font-bold text-slate-900 text-sm">RoyaStars Admin</span>
        </div>

        <button
          @click="handleLogout"
          class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors"
          title="Çıxış"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
        </button>
      </header>

      <!-- Page view container with smooth scrolling & responsive padding -->
      <main class="flex-1 overflow-y-auto">
        <div class="mx-auto max-w-7xl px-3 py-4 sm:px-6 sm:py-6 lg:px-8">
          <RouterView />
        </div>
      </main>
    </div>
  </div>
</template>
