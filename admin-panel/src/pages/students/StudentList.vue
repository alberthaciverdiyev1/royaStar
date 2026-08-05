<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { studentsApi, type Student, type StudentFormData } from '../../api/students'
import { gradesApi, type Grade } from '../../api/grades'
import { citiesApi, type City } from '../../api/cities'
import Pagination from '../../components/Pagination.vue'
import Toast from '../../components/Toast.vue'
import { showToast } from '../../stores/toast'
import type { PaginationMeta } from '../../api/types'

const students = ref<Student[]>([])
const grades = ref<Grade[]>([])
const cities = ref<City[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(true)
const page = ref(1)
const search = ref('')
const gradeFilter = ref<number | ''>('')

// Details modal
const showDetailsModal = ref(false)
const selectedStudent = ref<Student | null>(null)

// Edit modal
const showEditModal = ref(false)
const editingStudent = ref<Student | null>(null)
const editForm = ref<StudentFormData>({})
const saving = ref(false)

// Delete
const deletingId = ref<number | null>(null)
const deletingName = ref('')
const showDeleteModal = ref(false)

async function fetchStudents() {
  loading.value = true
  try {
    const res = await studentsApi.list({
      page: page.value,
      per_page: 20,
      search: search.value || undefined,
      grade_id: gradeFilter.value === '' ? undefined : Number(gradeFilter.value),
    })
    students.value = res.data
    meta.value = res.meta
  } catch {
    showToast({ type: 'error', text: 'Şagirdlər yüklənərkən xəta baş verdi' })
  } finally {
    loading.value = false
  }
}

async function fetchOptions() {
  try {
    grades.value = (await gradesApi.all()).data
    cities.value = (await citiesApi.all()).data
  } catch {
    showToast({ type: 'error', text: 'Məlumatlar yüklənərkən xəta baş verdi' })
  }
}

function openDetails(student: Student) {
  selectedStudent.value = student
  showDetailsModal.value = true
}

function closeDetails() {
  showDetailsModal.value = false
  selectedStudent.value = null
}

function openEdit(student: Student) {
  editingStudent.value = student
  editForm.value = {
    grade_id: student.grade_id ?? null,
    city_id: student.city_id ?? null,
    school_name: student.school_name ?? '',
    birth_date: student.birth_date ?? '',
  }
  showEditModal.value = true
}

function closeEdit() {
  showEditModal.value = false
  editingStudent.value = null
}

async function submitEdit() {
  if (!editingStudent.value) return
  saving.value = true
  try {
    const res = await studentsApi.update(editingStudent.value.id, {
      grade_id: editForm.value.grade_id || null,
      city_id: editForm.value.city_id || null,
      school_name: editForm.value.school_name || null,
      birth_date: editForm.value.birth_date || null,
    })
    const updated = res.data
    const idx = students.value.findIndex(s => s.id === updated.id)
    if (idx !== -1) students.value[idx] = updated
    if (selectedStudent.value?.id === updated.id) selectedStudent.value = updated
    showToast({ type: 'success', text: 'Şagird profili yeniləndi' })
    closeEdit()
    fetchStudents()
  } catch {
    showToast({ type: 'error', text: 'Yeniləmə zamanı xəta baş verdi' })
  } finally {
    saving.value = false
  }
}

function confirmDelete(student: Student) {
  deletingId.value = student.id
  deletingName.value = student.user?.name || `#${student.id}`
  showDeleteModal.value = true
}

async function submitDelete() {
  if (deletingId.value === null) return
  try {
    await studentsApi.remove(deletingId.value)
    showToast({ type: 'success', text: 'Şagird silindi' })
    showDeleteModal.value = false
    if (selectedStudent.value?.id === deletingId.value) closeDetails()
    fetchStudents()
  } catch {
    showToast({ type: 'error', text: 'Silinmə zamanı xəta baş verdi' })
  } finally {
    deletingId.value = null
  }
}

function studentName(s: Student) {
  return [s.user?.name, s.user?.surname].filter(Boolean).join(' ') || `Şagird #${s.id}`
}

function formatDate(dateStr: string | null) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('az-AZ', { year: 'numeric', month: 'long', day: 'numeric' })
}

let searchTimer: ReturnType<typeof setTimeout>
function onSearchInput() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    page.value = 1
    fetchStudents()
  }, 400)
}

function onGradeFilterChange() {
  page.value = 1
  fetchStudents()
}

onMounted(() => {
  fetchStudents()
  fetchOptions()
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Şagirdlər</h1>
        <p class="mt-1 text-sm text-slate-500">Bütün şagird profilləri, sinif və şəhər məlumatları</p>
      </div>

      <!-- Search -->
      <div class="relative w-full sm:w-72">
        <input
          v-model="search"
          @input="onSearchInput"
          type="text"
          placeholder="Axtar (Ad)..."
          class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm font-medium text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 shadow-sm"
        />
        <svg class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>

    <!-- Grade filter -->
    <div class="mb-6 flex flex-wrap items-center gap-2">
      <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Sinif:</label>
      <select
        v-model="gradeFilter"
        @change="onGradeFilterChange"
        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
      >
        <option value="">Bütün siniflər</option>
        <option v-for="g in grades" :key="g.id" :value="g.id">{{ g.name }}</option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-indigo-600 border-t-transparent" />
    </div>

    <!-- Empty State -->
    <div v-else-if="students.length === 0" class="rounded-2xl border border-slate-200 bg-white py-20 text-center shadow-sm">
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
      <table class="w-full min-w-[720px]">
        <thead>
          <tr class="border-b border-slate-100 bg-slate-50">
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Şagird</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Sinif</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Şəhər</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Məktəb</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Doğum Tarixi</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
            <th class="px-5 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Əməliyyat</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="s in students" :key="s.id" class="transition-colors hover:bg-slate-50">
            <td class="px-5 py-4 whitespace-nowrap">
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700 border border-indigo-200 overflow-hidden">
                  <img v-if="s.user?.avatar && (s.user.avatar.includes('/') || s.user.avatar.includes('http'))" :src="s.user.avatar" class="w-full h-full object-cover" />
                  <span v-else>{{ (s.user?.name?.charAt(0) || '?').toUpperCase() }}</span>
                </div>
                <div>
                  <span class="font-bold text-slate-900 block">{{ studentName(s) }}</span>
                  <span class="text-xs text-slate-500">{{ s.user?.email || 'E-poçt yoxdur' }}</span>
                </div>
              </div>
            </td>
            <td class="px-5 py-4 whitespace-nowrap">
              <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-bold text-indigo-700">
                {{ s.grade?.name || '—' }}
              </span>
            </td>
            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600 font-medium">{{ s.city?.name || '—' }}</td>
            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600 font-medium max-w-[180px] truncate">{{ s.school_name || '—' }}</td>
            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600 font-medium">{{ formatDate(s.birth_date) }}</td>
            <td class="px-5 py-4 whitespace-nowrap">
              <span
                v-if="s.user?.is_approved"
                class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-800"
              >
                Təsdiqlənib ✓
              </span>
              <span
                v-else
                class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800 animate-pulse"
              >
                Gözləyir ⏳
              </span>
            </td>
            <td class="px-5 py-4 whitespace-nowrap text-right space-x-2">
              <button
                @click="openEdit(s)"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 uppercase tracking-wider shadow-xs transition-all hover:bg-slate-50"
              >
                ✏️ Redaktə
              </button>
              <button
                @click="openDetails(s)"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 uppercase tracking-wider shadow-xs transition-all hover:bg-slate-50"
              >
                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Ətraflı
              </button>
              <button
                @click="confirmDelete(s)"
                class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-bold text-red-700 uppercase tracking-wider shadow-xs transition-all hover:bg-red-100"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Sil
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="meta" class="mt-4">
      <Pagination :meta="meta" @page-change="(p: number) => { page = p; fetchStudents() }" />
    </div>

    <!-- DETAILS MODAL -->
    <div v-if="showDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs">
      <div class="w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-6 py-4">
          <div class="flex items-center gap-2">
            <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <h3 class="text-base font-bold text-slate-900">Şagird Məlumatları</h3>
          </div>
          <button @click="closeDetails" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div v-if="selectedStudent" class="space-y-6 p-6">
          <div class="flex items-center gap-4 rounded-xl bg-indigo-50/60 p-4 border border-indigo-100">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xl font-black text-white shadow-md overflow-hidden border-2 border-indigo-200">
              <img v-if="selectedStudent.user?.avatar && (selectedStudent.user.avatar.includes('/') || selectedStudent.user.avatar.includes('http'))" :src="selectedStudent.user.avatar" class="w-full h-full object-cover" />
              <span v-else>{{ (selectedStudent.user?.name?.charAt(0) || '?').toUpperCase() }}</span>
            </div>
            <div>
              <h4 class="text-lg font-bold text-slate-900">{{ studentName(selectedStudent) }}</h4>
              <p class="text-xs font-semibold text-slate-500">{{ selectedStudent.user?.email }}</p>
              <p class="text-xs text-slate-400">{{ selectedStudent.user?.phone }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 space-y-1">
              <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px]">Sinif</span>
              <p class="font-bold text-slate-800">{{ selectedStudent.grade?.name || '—' }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 space-y-1">
              <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px]">Şəhər</span>
              <p class="font-bold text-slate-800">{{ selectedStudent.city?.name || '—' }}</p>
            </div>
            <div class="col-span-2 rounded-xl border border-slate-100 bg-slate-50 p-3 space-y-1">
              <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px]">Məktəb</span>
              <p class="font-bold text-slate-800">{{ selectedStudent.school_name || '—' }}</p>
            </div>
            <div class="col-span-2 rounded-xl border border-slate-100 bg-slate-50 p-3 space-y-1">
              <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px]">Doğum Tarixi</span>
              <p class="font-bold text-slate-800">{{ formatDate(selectedStudent.birth_date) }}</p>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-2">
            <button
              @click="openEdit(selectedStudent); closeDetails();"
              class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white uppercase tracking-wider shadow-md transition-all hover:bg-indigo-700"
            >
              ✏️ Redaktə Et
            </button>
            <button
              @click="closeDetails"
              class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 uppercase tracking-wider hover:bg-slate-50"
            >
              Bağla
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- EDIT MODAL -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs">
      <div class="w-full max-w-md max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 bg-indigo-50/60 px-6 py-4">
          <h3 class="text-base font-bold text-slate-900">Şagirdi Redaktə Et</h3>
          <button @click="closeEdit" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitEdit" class="space-y-4 p-6">
          <div v-if="editingStudent" class="rounded-xl bg-slate-50 p-3 border border-slate-100">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Şagird</span>
            <p class="text-sm font-bold text-slate-900">{{ studentName(editingStudent) }}</p>
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Sinif</label>
            <select
              v-model="editForm.grade_id"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            >
              <option :value="null">Sinif seçilməyib</option>
              <option v-for="g in grades" :key="g.id" :value="g.id">{{ g.name }}</option>
            </select>
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Şəhər</label>
            <select
              v-model="editForm.city_id"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            >
              <option :value="null">Şəhər seçilməyib</option>
              <option v-for="c in cities" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Məktəb</label>
            <input
              v-model="editForm.school_name"
              type="text"
              placeholder="Məktəbin adı"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Doğum Tarixi</label>
            <input
              v-model="editForm.birth_date"
              type="date"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <button
              type="button"
              @click="closeEdit"
              class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 uppercase tracking-wider hover:bg-slate-50"
            >
              Ləğv Et
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-5 py-2 text-xs font-bold text-white uppercase tracking-wider shadow-md transition-all hover:bg-indigo-700 disabled:opacity-50"
            >
              <svg v-if="saving" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              Yenilə
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- DELETE CONFIRM MODAL -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs">
      <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-red-50">
          <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <h3 class="text-lg font-bold text-slate-900">Şagirdi silmək istədiyinizə əminsiniz?</h3>
        <p class="mt-1 text-sm text-slate-500">
          <strong>{{ deletingName }}</strong> üçün profil qeydi silinəcək. Bu əməliyyat geri qaytarıla bilməz.
        </p>
        <div class="mt-6 flex items-center justify-end gap-3">
          <button
            @click="showDeleteModal = false"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 uppercase tracking-wider hover:bg-slate-50"
          >
            Ləğv Et
          </button>
          <button
            @click="submitDelete"
            class="rounded-xl bg-red-600 px-4 py-2 text-xs font-bold text-white uppercase tracking-wider shadow-md transition-all hover:bg-red-700"
          >
            Sil
          </button>
        </div>
      </div>
    </div>

    <Toast />
  </div>
</template>
