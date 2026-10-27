<script setup lang="ts">
import { ref, onMounted } from 'vue'
import Pagination from '../../components/Pagination.vue'
import Toast from '../../components/Toast.vue'
import { showToast } from '../../stores/toast'
import { popupsApi, type Popup, type PopupFormData } from '../../api/popups'
import type { PaginationMeta } from '../../api/types'

interface FormState {
  title: string
  content: string
  image: string | null
  is_active: boolean
}

const popups = ref<Popup[]>([])
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

// Delete
const deletingId = ref<number | null>(null)
const deletingName = ref('')
const showDeleteModal = ref(false)
const deleting = ref(false)

function emptyForm(): FormState {
  return {
    title: '',
    content: '',
    image: null,
    is_active: true,
  }
}

async function fetchPopups() {
  loading.value = true
  try {
    const res = await popupsApi.list({
      page: page.value,
      per_page: 20,
      search: search.value || undefined,
      is_active: statusFilter.value === '' ? undefined : Number(statusFilter.value),
    })
    popups.value = res.data
    meta.value = res.meta
  } catch {
    showToast({ type: 'error', text: 'Siyahı yüklənərkən xəta baş verdi' })
  } finally {
    loading.value = false
  }
}

function canPreview(src: string | null | undefined) {
  if (!src) return false
  return src.includes('/') || src.includes('http')
}

function popupLabel(p: Popup) {
  return p.title?.trim() || `Popup #${p.id}`
}

function popupType(p: Popup) {
  const hasImage = canPreview(p.image)
  const hasText = !!(p.title?.trim() || p.content?.trim())
  if (hasImage && hasText) return 'Şəkil + Mətn'
  if (hasImage) return 'Şəkil'
  return 'Mətn'
}

function openCreate() {
  editingId.value = null
  form.value = emptyForm()
  showModal.value = true
}

function openEdit(p: Popup) {
  editingId.value = p.id
  form.value = {
    title: p.title ?? '',
    content: p.content ?? '',
    image: p.image ?? null,
    is_active: p.is_active,
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
      // Popups are shown large, so allow more resolution than avatars.
      const MAX = 1400
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

function isFormValid(): boolean {
  return !!(form.value.title.trim() || form.value.content.trim() || form.value.image)
}

async function submitForm() {
  if (!isFormValid()) {
    showToast({ type: 'error', text: 'Ən azı başlıq, mətn və ya şəkil daxil edin' })
    return
  }
  saving.value = true
  const payload: PopupFormData = {
    title: form.value.title.trim() || null,
    content: form.value.content.trim() || null,
    image: form.value.image || null,
    is_active: form.value.is_active,
  }
  try {
    if (editingId.value !== null) {
      await popupsApi.update(editingId.value, payload)
      showToast({ type: 'success', text: 'Popup yeniləndi' })
    } else {
      await popupsApi.create(payload)
      showToast({ type: 'success', text: 'Yeni popup əlavə edildi' })
    }
    closeModal()
    fetchPopups()
  } catch {
    showToast({ type: 'error', text: 'Saxlama zamanı xəta baş verdi' })
  } finally {
    saving.value = false
  }
}

function confirmDelete(p: Popup) {
  deletingId.value = p.id
  deletingName.value = popupLabel(p)
  showDeleteModal.value = true
}

async function submitDelete() {
  if (deletingId.value === null || deleting.value) return
  deleting.value = true
  try {
    await popupsApi.remove(deletingId.value)
    showToast({ type: 'success', text: 'Popup silindi' })
    showDeleteModal.value = false
    fetchPopups()
  } catch {
    showToast({ type: 'error', text: 'Silinmə zamanı xəta baş verdi' })
  } finally {
    deleting.value = false
    deletingId.value = null
  }
}

let searchTimer: ReturnType<typeof setTimeout>
function onSearchInput() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    page.value = 1
    fetchPopups()
  }, 400)
}

function onStatusFilterChange() {
  page.value = 1
  fetchPopups()
}

onMounted(fetchPopups)
</script>

<template>
  <div>
    <!-- Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Popuplar</h1>
        <p class="mt-1 text-sm text-slate-500">
          Ana səhifə açıldıqda ziyarətçilərə göstərilən popuplar. Birdən çox aktiv popup varsa, hər açılışda biri təsadüfi seçilir.
        </p>
      </div>

      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative w-full sm:w-64">
          <input
            v-model="search"
            @input="onSearchInput"
            type="text"
            placeholder="Axtar (başlıq / mətn)..."
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
    <div v-else-if="popups.length === 0" class="rounded-2xl border border-slate-200 bg-white py-20 text-center shadow-sm">
      <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50">
        <svg class="h-8 w-8 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v9a2 2 0 01-2 2H8l-4 4V6z" />
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-slate-900">Heç bir popup tapılmadı</h3>
      <p class="mt-1 text-sm text-slate-500">Yeni popup yaratmaq üçün "Əlavə Et" düyməsinə klik edin.</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
      <table class="w-full min-w-[760px]">
        <thead>
          <tr class="border-b border-slate-100 bg-slate-50">
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Popup</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Növ</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
            <th class="px-5 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Əməliyyat</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="p in popups" :key="p.id" class="transition-colors hover:bg-slate-50">
            <td class="px-5 py-4">
              <div class="flex items-center gap-3">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-400">
                  <img v-if="canPreview(p.image)" :src="p.image!" class="h-full w-full object-cover" />
                  <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h10" />
                  </svg>
                </div>
                <div class="min-w-0">
                  <span class="block font-bold text-slate-900">{{ popupLabel(p) }}</span>
                  <span v-if="p.content" class="mt-0.5 block max-w-xs truncate text-xs text-slate-500">{{ p.content }}</span>
                  <span v-else class="text-xs text-slate-400">#{{ p.id }}</span>
                </div>
              </div>
            </td>
            <td class="px-5 py-4 whitespace-nowrap">
              <span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                {{ popupType(p) }}
              </span>
            </td>
            <td class="px-5 py-4 whitespace-nowrap">
              <span
                v-if="p.is_active"
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
                @click="openEdit(p)"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 uppercase tracking-wider shadow-xs transition-all hover:bg-slate-50"
              >
                ✏️ Redaktə
              </button>
              <button
                @click="confirmDelete(p)"
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
      <Pagination :meta="meta" @page-change="(p: number) => { page = p; fetchPopups() }" />
    </div>

    <!-- CREATE / EDIT MODAL -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs">
      <div class="w-full max-w-lg max-h-[92vh] overflow-y-auto rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 sticky top-0 bg-white/95 backdrop-blur">
          <h3 class="text-base font-bold text-slate-900">{{ editingId ? 'Popupu Redaktə Et' : 'Yeni Popup Əlavə Et' }}</h3>
          <button @click="closeModal" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-5 p-6">
          <p class="rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 text-xs font-medium text-indigo-700">
            Popup yalnız mətn, yalnız şəkil, ya da hər ikisini göstərə bilər. Ən azı birini doldurun.
          </p>

          <!-- Title -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Başlıq</label>
            <input
              v-model="form.title"
              type="text"
              placeholder="Məsələn: Yeni mövsüm endirimi"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
          </div>

          <!-- Content -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Mətn</label>
            <textarea
              v-model="form.content"
              rows="4"
              placeholder="Popup-da göstəriləcək mətn..."
              class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
          </div>

          <!-- Image upload -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Şəkil</label>

            <div v-if="canPreview(form.image)" class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
              <img :src="form.image!" class="max-h-56 w-full object-contain" />
            </div>

            <div class="flex items-center gap-4">
              <div v-if="!canPreview(form.image)" class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-indigo-200 bg-indigo-50 text-2xs font-bold text-indigo-400">
                Şəkil yoxdur
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
        <h3 class="text-lg font-bold text-slate-900">Bu popupu silmək istədiyinizə əminsiniz?</h3>
        <p class="mt-1 text-sm text-slate-500"><strong>{{ deletingName }}</strong> silinəcək. Bu əməliyyat geri qaytarıla bilməz.</p>
        <div class="mt-6 flex items-center justify-end gap-3">
          <button
            @click="showDeleteModal = false"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 uppercase tracking-wider hover:bg-slate-50"
          >
            Ləğv Et
          </button>
          <button
            @click="submitDelete"
            :disabled="deleting"
            class="rounded-xl bg-red-600 px-4 py-2 text-xs font-bold text-white uppercase tracking-wider shadow-md transition-all hover:bg-red-700 disabled:opacity-50"
          >
            Sil
          </button>
        </div>
      </div>
    </div>

    <Toast />
  </div>
</template>
