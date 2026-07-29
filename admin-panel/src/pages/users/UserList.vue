<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { usersApi, type User } from '../../api/users'
import Pagination from '../../components/Pagination.vue'
import Toast from '../../components/Toast.vue'
import { showToast } from '../../stores/toast'
import type { PaginationMeta } from '../../api/types'

const users = ref<User[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(true)
const page = ref(1)
const search = ref('')
const activeStatusTab = ref<string>('all') // 'all', 'pending', 'approved'
const approving = ref<number | null>(null)

// Selected user for User Details Modal
const selectedUser = ref<User | null>(null)
const loadingDetails = ref(false)
const showDetailsModal = ref(false)

// Change Password Modal state
const passwordUser = ref<User | null>(null)
const newPassword = ref('')
const showPasswordModal = ref(false)
const updatingPassword = ref(false)

async function fetchUsers() {
  loading.value = true
  try {
    const res = await usersApi.list({
      page: page.value,
      per_page: 20,
      search: search.value || undefined,
      status: activeStatusTab.value === 'all' ? undefined : activeStatusTab.value
    })
    users.value = res.data.data
    meta.value = res.data.meta
  } catch {
    showToast({ type: 'error', text: 'Şagirdlər yüklənərkən xəta baş verdi' })
  } finally {
    loading.value = false
  }
}

async function approveUser(userId: number) {
  approving.value = userId
  try {
    await usersApi.approve(userId)
    
    // Update status in list
    const userInList = users.value.find(u => u.id === userId)
    if (userInList) {
      userInList.is_approved = true
    }

    if (selectedUser.value && selectedUser.value.id === userId) {
      selectedUser.value.is_approved = true
    }

    showToast({ type: 'success', text: 'Şagird hesabınız təsdiqləndi' })
    fetchUsers()
  } catch {
    showToast({ type: 'error', text: 'Təsdiqləmə zamanı xəta baş verdi' })
  } finally {
    approving.value = null
  }
}

async function openUserDetails(userId: number) {
  loadingDetails.value = true
  showDetailsModal.value = true
  try {
    const res = await usersApi.getDetails(userId)
    selectedUser.value = res.data.data
  } catch {
    showToast({ type: 'error', text: 'Şagird detalları yüklənərkən xəta baş verdi' })
  } finally {
    loadingDetails.value = false
  }
}

function closeDetailsModal() {
  showDetailsModal.value = false
  selectedUser.value = null
}

function openPasswordModal(user: User) {
  passwordUser.value = user
  newPassword.value = ''
  showPasswordModal.value = true
}

function closePasswordModal() {
  showPasswordModal.value = false
  passwordUser.value = null
  newPassword.value = ''
}

async function submitChangePassword() {
  if (!passwordUser.value) return
  if (!newPassword.value || newPassword.value.length < 6) {
    showToast({ type: 'error', text: 'Şifrə ən azı 6 simvol olmalıdır' })
    return
  }

  updatingPassword.value = true
  try {
    await usersApi.changePassword(passwordUser.value.id, newPassword.value)
    showToast({ type: 'success', text: 'Şifrə uğurla yeniləndi' })
    closePasswordModal()
  } catch {
    showToast({ type: 'error', text: 'Şifrə yenilənərkən xəta baş verdi' })
  } finally {
    updatingPassword.value = false
  }
}

function formatDate(dateStr: string) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('az-AZ', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

let searchTimer: ReturnType<typeof setTimeout>
function onSearchInput() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    page.value = 1
    fetchUsers()
  }, 400)
}

function setStatusTab(tab: string) {
  activeStatusTab.value = tab
  page.value = 1
  fetchUsers()
}

onMounted(fetchUsers)
</script>

<template>
  <div>
    <!-- Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Şagirdlərin Siyahısı</h1>
        <p class="mt-1 text-sm text-slate-500">Bütün şagirdlər (Təsdiq gözləyənlər ən yuxarıda gösterilir)</p>
      </div>

      <!-- Search Input -->
      <div class="relative w-full sm:w-72">
        <input
          v-model="search"
          @input="onSearchInput"
          type="text"
          placeholder="Axtar (Ad, E-poçt, Telefon)..."
          class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm font-medium text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 shadow-sm"
        />
        <svg class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>

    <!-- Status Tabs -->
    <div class="mb-6 flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
      <button
        @click="setStatusTab('all')"
        :class="[
          'rounded-xl px-4 py-2 text-xs font-bold transition-all uppercase tracking-wider',
          activeStatusTab === 'all'
            ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200'
            : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'
        ]"
      >
        Bütün Şagirdlər
      </button>

      <button
        @click="setStatusTab('pending')"
        :class="[
          'rounded-xl px-4 py-2 text-xs font-bold transition-all uppercase tracking-wider flex items-center gap-1.5',
          activeStatusTab === 'pending'
            ? 'bg-amber-500 text-white shadow-md shadow-amber-200'
            : 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100'
        ]"
      >
        <span>Təsdiq Gözləyənlər</span>
        <span class="rounded-full bg-amber-600/30 px-2 py-0.5 text-[10px] font-extrabold text-amber-900">⏳</span>
      </button>

      <button
        @click="setStatusTab('approved')"
        :class="[
          'rounded-xl px-4 py-2 text-xs font-bold transition-all uppercase tracking-wider',
          activeStatusTab === 'approved'
            ? 'bg-green-600 text-white shadow-md shadow-green-200'
            : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'
        ]"
      >
        Təsdiqlənmişlər ✓
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-indigo-600 border-t-transparent" />
    </div>

    <!-- Empty State -->
    <div v-else-if="users.length === 0" class="rounded-2xl border border-slate-200 bg-white py-20 text-center shadow-sm">
      <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50">
        <svg class="h-8 w-8 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-slate-900">Şagird tapılmadı</h3>
      <p class="mt-1 text-sm text-slate-500">Seçilmiş meyarda şagird mövcud deyil.</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
      <table class="w-full min-w-[700px]">
        <thead>
          <tr class="border-b border-slate-100 bg-slate-50">
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Şagird</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">E-poçt</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Telefon</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Rol</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Qeydiyyat Tarixi</th>
            <th class="px-5 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Əməliyyat</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr 
            v-for="user in users" 
            :key="user.id" 
            :class="[
              'transition-colors hover:bg-slate-50',
              !user.is_approved ? 'bg-amber-50/40 font-medium' : ''
            ]"
          >
            <td class="px-5 py-4 whitespace-nowrap">
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700 shadow-xs border border-indigo-200">
                  {{ (user.name?.charAt(0) || '?').toUpperCase() }}
                </div>
                <div>
                  <span class="font-bold text-slate-900 block">{{ user.name }} {{ user.surname || '' }}</span>
                  <span v-if="user.student?.grade" class="text-xs text-indigo-600 font-medium">
                    Sinif {{ user.student.grade }}
                  </span>
                </div>
              </div>
            </td>
            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600 font-medium">{{ user.email }}</td>
            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600 font-medium">{{ user.phone }}</td>
            <td class="px-5 py-4 whitespace-nowrap">
              <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-bold text-indigo-700 uppercase tracking-wider">
                {{ user.type === 'admin' ? 'Admin' : 'Şagird' }}
              </span>
            </td>
            <td class="px-5 py-4 whitespace-nowrap">
              <span 
                v-if="user.is_approved" 
                class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-800"
              >
                Təsdiqlənib ✓
              </span>
              <span 
                v-else 
                class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800 animate-pulse"
              >
                Təsdiq Gözləyir ⏳
              </span>
            </td>
            <td class="px-5 py-4 whitespace-nowrap text-xs font-semibold text-slate-500">{{ formatDate(user.created_at) }}</td>
            <td class="px-5 py-4 whitespace-nowrap text-right space-x-2">
              <!-- Approve Button if unapproved -->
              <button
                v-if="!user.is_approved"
                @click="approveUser(user.id)"
                :disabled="approving === user.id"
                class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-bold text-white uppercase tracking-wider shadow-xs transition-all hover:bg-green-700 disabled:opacity-50"
              >
                <svg v-if="approving === user.id" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                Təsdiq et
              </button>

              <!-- Change Password Button -->
              <button
                @click="openPasswordModal(user)"
                class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1.5 text-xs font-bold text-amber-800 uppercase tracking-wider shadow-xs transition-all hover:bg-amber-100"
                title="Şifrəni dəyiş"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                Şifrə
              </button>

              <!-- View Details Button -->
              <button
                @click="openUserDetails(user.id)"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 uppercase tracking-wider shadow-xs transition-all hover:bg-slate-50 hover:border-slate-300"
              >
                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Ətraflı
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="meta" class="mt-4">
      <Pagination :meta="meta" @page-change="(p: number) => { page = p; fetchUsers() }" />
    </div>

    <!-- USER DETAILS MODAL -->
    <div 
      v-if="showDetailsModal" 
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs"
    >
      <div class="w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl transition-all">
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-6 py-4">
          <div class="flex items-center gap-2">
            <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <h3 class="text-base font-bold text-slate-900">Şagird Məlumatları</h3>
          </div>
          <button @click="closeDetailsModal" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Modal Body Loading -->
        <div v-if="loadingDetails" class="flex items-center justify-center py-16">
          <div class="h-8 w-8 animate-spin rounded-full border-4 border-indigo-600 border-t-transparent" />
        </div>

        <!-- Modal Content -->
        <div v-else-if="selectedUser" class="space-y-6 p-6">
          <!-- Profile Card -->
          <div class="flex items-center gap-4 rounded-xl bg-indigo-50/60 p-4 border border-indigo-100">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-xl font-black text-white shadow-md">
              {{ (selectedUser.name?.charAt(0) || '?').toUpperCase() }}
            </div>
            <div>
              <h4 class="text-lg font-bold text-slate-900">{{ selectedUser.name }} {{ selectedUser.surname || '' }}</h4>
              <p class="text-xs font-semibold text-slate-500">{{ selectedUser.email }}</p>
              <div class="mt-1 flex items-center gap-2">
                <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-[10px] font-bold text-indigo-700 uppercase tracking-wider">
                  {{ selectedUser.type }}
                </span>
                <span 
                  v-if="selectedUser.is_approved"
                  class="rounded-full bg-green-100 px-2.5 py-0.5 text-[10px] font-bold text-green-800"
                >
                  Təsdiqlənib ✓
                </span>
                <span 
                  v-else
                  class="rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-800"
                >
                  Təsdiq Gözləyir ⏳
                </span>
              </div>
            </div>
          </div>

          <!-- Info Grid -->
          <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 space-y-1">
              <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px]">Telefon</span>
              <p class="font-bold text-slate-800">{{ selectedUser.phone || 'Göstərilməyib' }}</p>
            </div>

            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 space-y-1">
              <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px]">Qeydiyyat Tarixi</span>
              <p class="font-bold text-slate-800">{{ formatDate(selectedUser.created_at) }}</p>
            </div>

            <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-3 space-y-1">
              <span class="font-bold uppercase tracking-wider text-amber-700 text-[10px]">Qazanılmış Ulduzlar</span>
              <p class="font-black text-amber-800 text-sm">🌟 {{ selectedUser.total_stars ?? 0 }} XP Ulduz</p>
            </div>

            <div v-if="selectedUser.student?.grade" class="rounded-xl border border-slate-100 bg-slate-50 p-3 space-y-1">
              <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px]">Sinif / Dərəcə</span>
              <p class="font-bold text-slate-800">Sinif {{ selectedUser.student.grade }}</p>
            </div>

            <div v-if="selectedUser.student?.school" class="col-span-2 rounded-xl border border-slate-100 bg-slate-50 p-3 space-y-1">
              <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px]">Məktəb / Təhsil Müəssisəsi</span>
              <p class="font-bold text-slate-800">{{ selectedUser.student.school }}</p>
            </div>

            <div v-if="selectedUser.student?.city" class="col-span-2 rounded-xl border border-slate-100 bg-slate-50 p-3 space-y-1">
              <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px]">Şəhər / Rayon</span>
              <p class="font-bold text-slate-800">{{ selectedUser.student.city }}</p>
            </div>
          </div>

          <!-- Modal Action Bar -->
          <div class="flex items-center justify-between pt-2">
            <button
              @click="openPasswordModal(selectedUser); closeDetailsModal();"
              class="inline-flex items-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2 text-xs font-bold text-amber-800 uppercase tracking-wider hover:bg-amber-100"
            >
              🔑 Şifrəni Dəyiş
            </button>
            <div class="flex items-center gap-2">
              <button
                v-if="!selectedUser.is_approved"
                @click="approveUser(selectedUser.id)"
                :disabled="approving === selectedUser.id"
                class="inline-flex items-center gap-1.5 rounded-xl bg-green-600 px-4 py-2 text-xs font-bold text-white uppercase tracking-wider shadow-md transition-all hover:bg-green-700 disabled:opacity-50"
              >
                Təsdiq Et ✓
              </button>
              <button
                @click="closeDetailsModal"
                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 uppercase tracking-wider hover:bg-slate-50"
              >
                Bağla
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CHANGE PASSWORD MODAL -->
    <div 
      v-if="showPasswordModal" 
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs"
    >
      <div class="w-full max-w-md max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl transition-all">
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-100 bg-amber-50/60 px-6 py-4">
          <div class="flex items-center gap-2">
            <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
            <h3 class="text-base font-bold text-slate-900">Şifrənin Dəyişdirilməsi</h3>
          </div>
          <button @click="closePasswordModal" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Modal Form Body -->
        <form @submit.prevent="submitChangePassword" class="space-y-4 p-6">
          <div v-if="passwordUser" class="rounded-xl bg-slate-50 p-3 border border-slate-100">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">İstifadəçi</span>
            <p class="text-sm font-bold text-slate-900">{{ passwordUser.name }} ({{ passwordUser.email }})</p>
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Yeni Şifrə</label>
            <input
              v-model="newPassword"
              type="password"
              placeholder="Minimum 6 simvol daxil edin"
              required
              minlength="6"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
            />
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <button
              type="button"
              @click="closePasswordModal"
              class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 uppercase tracking-wider hover:bg-slate-50"
            >
              Ləğv Et
            </button>
            <button
              type="submit"
              :disabled="updatingPassword"
              class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 px-5 py-2 text-xs font-bold text-white uppercase tracking-wider shadow-md transition-all hover:bg-amber-600 disabled:opacity-50"
            >
              <svg v-if="updatingPassword" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              Yenilə
            </button>
          </div>
        </form>
      </div>
    </div>

    <Toast />
  </div>
</template>
