<script setup lang="ts">
import { ref, onMounted } from 'vue'
import Pagination from '../../components/Pagination.vue'
import Toast from '../../components/Toast.vue'
import { showToast } from '../../stores/toast'
import {
  acceptedStudentsApi,
  type AcceptedStudent,
  type AcceptedStudentFormData,
  type PickableStudent,
} from '../../api/accepted-students'
import { studentsApi } from '../../api/students'
import type { PaginationMeta } from '../../api/types'

interface FormState {
  user_id: number | null
  name: string
  surname: string
  image: string | null
  exam_points: number | null
  is_active: boolean
}

const students = ref<AcceptedStudent[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(true)
const page = ref(1)
const search = ref('')
const statusFilter = ref<number | ''>('')

// Create / edit modal
const showModal = ref(false)
const editingId = ref<number | null>(null)
const form = ref<FormState>(emptyForm())
const saving = ref(false)
const imageInput = ref<HTMLInputElement | null>(null)

// Existing-student picker
const pickerOpen = ref(false)
const pickerSearch = ref('')
const pickerLoading = ref(false)
const pickerResults = ref<PickableStudent[]>([])
let pickerTimer: ReturnType<typeof setTimeout>

// Delete
const deletingId = ref<number | null>(null)
const deletingName = ref('')
const showDeleteModal = ref(false)

function emptyForm(): FormState {
  return {
    user_id: null,
    name: '',
    surname: '',
    image: null,
    exam_points: null,
    is_active: true,
  }
}

async function fetchStudents() {
  loading.value = true
  try {
    const res = await acceptedStudentsApi.list({
      page: page.value,
      per_page: 20,
      search: search.value || undefined,
      is_active: statusFilter.value === '' ? undefined : Number(statusFilter.value),
    })
    students.value = res.data
    meta.value = res.meta
  } catch {
    showToast({ type: 'error', text: 'Siyahı yüklənərkən xəta baş verdi' })
  } finally {
    loading.value = false
  }
}

function fullName(s: { name: string; surname: string | null }) {
  return [s.name, s.surname].filter(Boolean).join(' ') || '#'
}

function initials(s: { name: string }) {
  return (s.name?.charAt(0) || '?').toUpperCase()
}

function canPreview(src: string | null | undefined) {
  if (!src) return false
  return src.includes('/') || src.includes('http')
}

function openCreate() {
  editingId.value = null
  form.value = emptyForm()
  showModal.value = true
}

function openEdit(s: AcceptedStudent) {
  editingId.value = s.id
  form.value = {
    user_id: s.user_id ?? null,
    name: s.name,
    surname: s.surname ?? '',
    image: s.image ?? null,
    exam_points: s.exam_points,
    is_active: s.is_active,
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingId.value = null
}

function onPickImage(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return
  input.value = ''
  if (!file.type.startsWith('image/')) {
    showToast({ type: 'error', text: 'Zəhmət olmasa şəkil seçin' })
    return
  }
  const reader = new FileReader()
  reader.onload = (e) => {
    const src = e.target?.result as string
    const img = new Image()
    img.onload = () => {
      // Downscale huge photos so the upload stays small and consistent.
      const MAX = 900
      let { width, height } = img
      if (width > height && width > MAX) {
        height = Math.round((height * MAX) / width)
        width = MAX
      } else if (height > MAX) {
        width = Math.round((width * MAX) / height)
        height = MAX
      }
      const canvas = document.createElement('canvas')
      canvas.width = width
      canvas.height = height
      canvas.getContext('2d')?.drawImage(img, 0, 0, width, height)
      const mime = file.type === 'image/png' ? 'image/png' : 'image/jpeg'
      form.value.image = canvas.toDataURL(mime, 0.85)
    }
    img.onerror = () => showToast({ type: 'error', text: 'Şəkil oxunarkən xəta baş verdi' })
    img.src = src
  }
  reader.readAsDataURL(file)
}

function clearImage() {
  form.value.image = null
  if (imageInput.value) imageInput.value.value = ''
}

async function runExistingSearch() {
  const q = pickerSearch.value.trim()
  pickerLoading.value = true
  try {
    const res = await studentsApi.list({ search: q || undefined, per_page: 12 })
    pickerResults.value = res.data
      .filter((s) => s.user)
      .map((s) => ({
        id: s.id,
        user_id: s.user_id,
        name: s.user?.name ?? '',
        surname: s.user?.surname ?? null,
        email: s.user?.email ?? '',
        avatar: s.user?.avatar ?? null,
        grade: s.grade ?? null,
      }))
  } catch {
    showToast({ type: 'error', text: 'Şagirdlər axtarılarkən xəta baş verdi' })
  } finally {
    pickerLoading.value = false
  }
}

function onPickerInput() {
  clearTimeout(pickerTimer)
  pickerTimer = setTimeout(() => runExistingSearch(), 350)
}

function togglePicker() {
  pickerOpen.value = !pickerOpen.value
  if (pickerOpen.value) runExistingSearch()
}

function pickStudent(s: PickableStudent) {
  form.value.user_id = s.user_id
  form.value.name = s.name
  form.value.surname = s.surname ?? ''
  form.value.image = s.avatar ?? null
  pickerOpen.value = false
}

function isFormValid(): boolean {
  return form.value.name.trim().length > 0
}

async function submitForm() {
  if (!isFormValid()) {
    showToast({ type: 'error', text: 'Ad daxil edilməlidir' })
    return
  }
  saving.value = true
  const payload: AcceptedStudentFormData = {
    user_id: form.value.user_id,
    name: form.value.name.trim(),
    surname: form.value.surname.trim() || null,
    image: form.value.image || null,
    exam_points: form.value.exam_points ?? 0,
    is_active: form.value.is_active,
  }
  try {
    if (editingId.value !== null) {
      await acceptedStudentsApi.update(editingId.value, payload)
      showToast({ type: 'success', text: 'Qeyd yeniləndi' })
    } else {
      await acceptedStudentsApi.create(payload)
      showToast({ type: 'success', text: 'Yeni məzun əlavə edildi' })
    }
    closeModal()
    fetchStudents()
  } catch {
    showToast({ type: 'error', text: 'Saxlama zamanı xəta baş verdi' })
  } finally {
    saving.value = false
  }
}

function confirmDelete(s: AcceptedStudent) {
  deletingId.value = s.id
  deletingName.value = fullName(s)
  showDeleteModal.value = true
}

async function submitDelete() {
  if (deletingId.value === null) return
  try {
    await acceptedStudentsApi.remove(deletingId.value)
    showToast({ type: 'success', text: 'Qeyd silindi' })
    showDeleteModal.value = false
    fetchStudents()
  } catch {
    showToast({ type: 'error', text: 'Silinmə zamanı xəta baş verdi' })
  } finally {
    deletingId.value = null
  }
}

let searchTimer: ReturnType<typeof setTimeout>
function onSearchInput() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    page.value = 1
    fetchStudents()
  }, 400)
}

function onStatusFilterChange() {
  page.value = 1
  fetchStudents()
}

onMounted(fetchStudents)
</script>

<template>
  <div>
    <!-- Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Universitetə qəbul olanlar</h1>
        <p class="mt-1 text-sm text-slate-500">
          Tanıtım üçün qəbul olan şagirdlər. Bu qeydlər yalnız göstərişdir — giriş hüququ vermir.
        </p>
      </div>

      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative w-full sm:w-64">
          <input
            v-model="search"
            @input="onSearchInput"
            type="text"
            placeholder="Axtar (Ad / Soyad)..."
            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm font-medium text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 shadow-sm"
          />
          <svg class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <button
          @click="openCreate"
          class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white uppercase tracking-wider shadow-md transition-all hover:bg-indigo-700 active:scale-95"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
          </svg>
          Əlavə Et
        </button>
      </div>
    </div>

    <!-- Status filter -->
    <div class="mb-6 flex flex-wrap items-center gap-2">
      <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Status:</label>
      <select
        v-model="statusFilter"
        @change="onStatusFilterChange"
        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
      >
        <option value="">Hamısı</option>
        <option :value="1">Aktiv</option>
        <option :value="0">Gizlədilib</option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-indigo-600 border-t-transparent" />
    </div>

    <!-- Empty -->
    <div v-else-if="students.length === 0" class="rounded-2xl border border-slate-200 bg-white py-20 text-center shadow-sm">
      <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50">
        <svg class="h-8 w-8 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 10v6M2 10l10-5 10 5-10 5v6m0-11L2 10" />
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-slate-900">Heç bir qeyd tapılmadı</h3>
      <p class="mt-1 text-sm text-slate-500">Yeni məzun əlavə etmək üçün "Əlavə Et" düyməsinə klik edin.</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
      <table class="w-full min-w-[700px]">
        <thead>
          <tr class="border-b border-slate-100 bg-slate-50">
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Məzun</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Mənbə</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Bal</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
            <th class="px-5 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Əməliyyat</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="s in students" :key="s.id" class="transition-colors hover:bg-slate-50">
            <td class="px-5 py-4 whitespace-nowrap">
              <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700 border border-indigo-200 overflow-hidden">
                  <img v-if="canPreview(s.image)" :src="s.image" class="w-full h-full object-cover" />
                  <span v-else>{{ initials(s) }}</span>
                </div>
                <div>
                  <span class="font-bold text-slate-900 block">{{ fullName(s) }}</span>
                  <span class="text-xs text-slate-500">#{{ s.id }}</span>
                </div>
              </div>
            </td>
            <td class="px-5 py-4 whitespace-nowrap">
              <span
                v-if="s.user_id"
                class="inline-flex rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-bold text-green-700 border border-green-100"
              >
                Mövcud şagird
              </span>
              <span
                v-else
                class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600 border border-slate-200"
              >
                Yeni qeyd
              </span>
            </td>
            <td class="px-5 py-4 whitespace-nowrap">
              <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-sm font-black text-amber-700 border border-amber-200">
                {{ s.exam_points }}
              </span>
            </td>
            <td class="px-5 py-4 whitespace-nowrap">
              <span
                v-if="s.is_active"
                class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-800"
              >
                Aktiv ✓
              </span>
              <span v-else class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                Gizlidir
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
                @click="confirmDelete(s)"
                class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-bold text-red-700 uppercase tracking-wider shadow-xs transition-all hover:bg-red-100"
              >
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

    <!-- CREATE / EDIT MODAL -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs">
      <div class="w-full max-w-lg max-h-[92vh] overflow-y-auto rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 bg-indigo-50/60 px-6 py-4 sticky top-0 bg-white/95 backdrop-blur">
          <h3 class="text-base font-bold text-slate-900">{{ editingId ? 'Məzunu Redaktə Et' : 'Yeni Məzun Əlavə Et' }}</h3>
          <button @click="closeModal" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-5 p-6">
          <!-- Existing student picker -->
          <div class="rounded-xl border border-indigo-200 bg-indigo-50/50 p-4">
            <div class="flex items-center justify-between gap-2">
              <span class="text-xs font-bold uppercase tracking-wider text-indigo-700">Mövcud şagirddən seç (istəyə bağlı)</span>
              <button type="button" @click="togglePicker" class="text-xs font-bold text-indigo-600 underline hover:text-indigo-800">
                {{ pickerOpen ? 'Bağla' : 'Seç' }}
              </button>
            </div>

            <div v-if="form.user_id" class="mt-3 flex items-center gap-2 rounded-lg bg-white border border-indigo-100 px-3 py-2">
              <span class="flex-1 text-sm font-semibold text-slate-800 truncate">{{ fullName(form) }}</span>
              <button type="button" @click="form.user_id = null; pickerOpen = false" class="text-xs font-bold text-red-600 hover:text-red-800">
                Ləğv et
              </button>
            </div>

            <div v-else-if="pickerOpen" class="mt-3 space-y-2">
              <input
                v-model="pickerSearch"
                @input="onPickerInput"
                type="text"
                placeholder="Şagird axtar..."
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
              />
              <div class="max-h-44 overflow-y-auto rounded-xl border border-slate-100 bg-white divide-y divide-slate-50">
                <div v-if="pickerLoading" class="px-3 py-3 text-sm text-slate-400">Yüklənir...</div>
                <button
                  v-for="r in pickerResults"
                  :key="r.id"
                  type="button"
                  @click="pickStudent(r)"
                  class="flex w-full items-center gap-2 px-3 py-2 text-left hover:bg-indigo-50 transition-colors"
                >
                  <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-[10px] font-bold text-indigo-700 overflow-hidden">
                    <img v-if="canPreview(r.avatar)" :src="r.avatar" class="w-full h-full object-cover" />
                    <span v-else>{{ (r.name?.charAt(0) || '?').toUpperCase() }}</span>
                  </span>
                  <span class="flex-1 min-w-0">
                    <span class="block truncate text-sm font-semibold text-slate-800">{{ [r.name, r.surname].filter(Boolean).join(' ') }}</span>
                    <span class="block truncate text-xs text-slate-400">{{ r.email }}</span>
                  </span>
                  <span v-if="r.grade" class="text-xs font-bold text-slate-400">{{ r.grade.name }}</span>
                </button>
                <div v-if="!pickerLoading && pickerResults.length === 0" class="px-3 py-3 text-sm text-slate-400">Nəticə tapılmadı.</div>
              </div>
            </div>
          </div>

          <!-- Name / surname -->
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-1.5">
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Ad *</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Şagirdin adı"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
              />
            </div>
            <div class="space-y-1.5">
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Soyad</label>
              <input
                v-model="form.surname"
                type="text"
                placeholder="Şagirdin soyadı"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
              />
            </div>
          </div>

          <!-- Points -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Bal (Qəbul imtahanı) *</label>
            <input
              v-model.number="form.exam_points"
              type="number"
              min="0"
              max="1000"
              placeholder="0"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
            <p class="text-xs text-slate-400">Sahədən qazanılan ulduzlarla əlaqəsi yoxdur — əl ilə daxil edilir.</p>
          </div>

          <!-- Image upload -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Şəkil</label>
            <div class="flex items-center gap-4">
              <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 border border-dashed border-indigo-200 text-sm font-bold text-indigo-400 overflow-hidden">
                <img v-if="canPreview(form.image)" :src="form.image" class="w-full h-full object-cover" />
                <span v-else class="text-2xs">Şəkil yoxdur</span>
              </div>
              <div class="flex flex-col gap-2">
                <label class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 uppercase tracking-wider hover:bg-slate-50">
                  {{ form.image ? 'Şəkli dəyiş' : 'Şəkil seç' }}
                  <input ref="imageInput" type="file" accept="image/*" class="hidden" @change="onPickImage" />
                </label>
                <button
                  v-if="form.image"
                  type="button"
                  @click="clearImage"
                  class="text-xs font-bold text-red-600 uppercase tracking-wider hover:text-red-800"
                >
                  Şəkli sil
                </button>
              </div>
            </div>
          </div>

          <!-- Active toggle -->
          <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 cursor-pointer">
            <span class="text-sm font-bold text-slate-800">Saytda göstər</span>
            <input type="checkbox" v-model="form.is_active" class="h-5 w-5 accent-indigo-600" />
          </label>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <button
              type="button"
              @click="closeModal"
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
              {{ editingId ? 'Yenilə' : 'Əlavə Et' }}
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
        <h3 class="text-lg font-bold text-slate-900">Bu qeydi silmək istədiyinizə əminsiniz?</h3>
        <p class="mt-1 text-sm text-slate-500"><strong>{{ deletingName }}</strong> siyahıdan silinəcək. Bu əməliyyat geri qaytarıla bilməz.</p>
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
