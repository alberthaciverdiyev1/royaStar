<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import Pagination from '../../components/Pagination.vue'
import Toast from '../../components/Toast.vue'
import { showToast } from '../../stores/toast'
import {
  musicApi,
  type MusicPageOption,
  type MusicSettings,
  type MusicTrack,
  type MusicTrackFormData,
} from '../../api/music'
import type { PaginationMeta } from '../../api/types'

interface FormState {
  title: string
  artist: string
  url: string
  cover: string | null
  is_active: boolean
  sort_order: number
}

const tracks = ref<MusicTrack[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(true)
const page = ref(1)
const search = ref('')
const statusFilter = ref<number | ''>('')

// General settings (shared by every track)
const settings = ref<MusicSettings | null>(null)
const settingsForm = ref<{ play_on_all: boolean; pages: string[] }>({ play_on_all: true, pages: [] })
const settingsLoading = ref(true)
const settingsSaving = ref(false)

const pageOptions = computed<MusicPageOption[]>(() => settings.value?.page_options ?? [])

// Create / edit modal
const showModal = ref(false)
const editingId = ref<number | null>(null)
const form = ref<FormState>(emptyForm())
const saving = ref(false)
const coverInput = ref<HTMLInputElement | null>(null)
const titleEdited = ref(false)
const lastAutoTitle = ref('')
const lastAutoArtist = ref('')
const metadataLoading = ref(false)

// Delete
const deletingId = ref<number | null>(null)
const deletingName = ref('')
const showDeleteModal = ref(false)
const deleting = ref(false)

const youtubeIdPattern = /(?:youtube\.com\/(?:watch\?(?:[^#]*&)?v=|embed\/|shorts\/|live\/|v\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/i

const detectedYoutubeId = computed(() => {
  const match = form.value.url.match(youtubeIdPattern)
  return match ? match[1] : null
})

const autoThumbnail = computed(() =>
  detectedYoutubeId.value
    ? `https://i.ytimg.com/vi/${detectedYoutubeId.value}/hqdefault.jpg`
    : null,
)

const previewThumbnail = computed(() =>
  canPreview(form.value.cover) ? form.value.cover : autoThumbnail.value,
)

function emptyForm(): FormState {
  return {
    title: '',
    artist: '',
    url: '',
    cover: null,
    is_active: true,
    sort_order: 0,
  }
}

function canPreview(src: string | null | undefined): boolean {
  if (!src) return false
  return src.includes('/') || src.includes('http')
}

function isValidUrl(value: string): boolean {
  try {
    const parsed = new URL(value)
    return parsed.protocol === 'http:' || parsed.protocol === 'https:'
  } catch {
    return false
  }
}

function hostOf(url: string): string {
  try {
    return new URL(url).hostname.replace(/^www\./, '')
  } catch {
    return url
  }
}

async function fetchTracks() {
  loading.value = true
  try {
    const res = await musicApi.list({
      page: page.value,
      per_page: 20,
      search: search.value || undefined,
      is_active: statusFilter.value === '' ? undefined : Number(statusFilter.value),
    })
    tracks.value = res.data
    meta.value = res.meta
  } catch {
    showToast({ type: 'error', text: 'Siyahı yüklənərkən xəta baş verdi' })
  } finally {
    loading.value = false
  }
}

async function fetchSettings() {
  settingsLoading.value = true
  try {
    const res = await musicApi.getSettings()
    settings.value = res.data
    settingsForm.value = {
      play_on_all: res.data.play_on_all,
      pages: [...res.data.pages],
    }
  } catch {
    showToast({ type: 'error', text: 'Ümumi ayarlar yüklənərkən xəta baş verdi' })
  } finally {
    settingsLoading.value = false
  }
}

async function saveSettings() {
  if (!settingsForm.value.play_on_all && settingsForm.value.pages.length === 0) {
    showToast({ type: 'error', text: 'Ən azı bir səhifə seçin və ya bütün səhifələr variantını aktiv edin' })
    return
  }

  settingsSaving.value = true
  try {
    const res = await musicApi.updateSettings({
      play_on_all: settingsForm.value.play_on_all,
      pages: settingsForm.value.pages,
    })
    settings.value = res.data
    settingsForm.value = {
      play_on_all: res.data.play_on_all,
      pages: [...res.data.pages],
    }
    showToast({ type: 'success', text: 'Musiqi ayarları yadda saxlanıldı' })
  } catch {
    showToast({ type: 'error', text: 'Ayarlar saxlanılarkən xəta baş verdi' })
  } finally {
    settingsSaving.value = false
  }
}

function openCreate() {
  editingId.value = null
  form.value = emptyForm()
  titleEdited.value = false
  lastAutoTitle.value = ''
  lastAutoArtist.value = ''
  showModal.value = true
}

function openEdit(track: MusicTrack) {
  editingId.value = track.id
  form.value = {
    title: track.title ?? '',
    artist: track.artist ?? '',
    url: track.url,
    cover: track.cover ?? null,
    is_active: track.is_active,
    sort_order: track.sort_order,
  }
  // Existing title is authoritative; only refresh it on an explicit request.
  titleEdited.value = true
  lastAutoTitle.value = track.title ?? ''
  lastAutoArtist.value = track.artist ?? ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingId.value = null
}

let urlTimer: ReturnType<typeof setTimeout> | undefined

function shouldAutoFillTitle(): boolean {
  return !titleEdited.value || form.value.title.trim() === lastAutoTitle.value
}

function onUrlInput() {
  clearTimeout(urlTimer)
  urlTimer = setTimeout(() => {
    const url = form.value.url.trim()
    if (isValidUrl(url) && shouldAutoFillTitle()) {
      fetchMetadata(false)
    }
  }, 700)
}

function onUrlBlur() {
  clearTimeout(urlTimer)
  const url = form.value.url.trim()
  if (isValidUrl(url) && shouldAutoFillTitle()) {
    fetchMetadata(false)
  }
}

async function fetchMetadata(force: boolean) {
  const url = form.value.url.trim()
  if (!isValidUrl(url)) {
    if (force) showToast({ type: 'error', text: 'Düzgün URL daxil edin' })
    return
  }

  metadataLoading.value = true
  try {
    const res = await musicApi.metadata(url)
    const data = res.data

    if ((force || !titleEdited.value || form.value.title.trim() === lastAutoTitle.value) && data.title) {
      form.value.title = data.title
      lastAutoTitle.value = data.title
      titleEdited.value = false
    }

    if ((force || !form.value.artist.trim() || form.value.artist.trim() === lastAutoArtist.value) && data.artist) {
      form.value.artist = data.artist
      lastAutoArtist.value = data.artist
    }

    if (force) {
      showToast({ type: 'success', text: 'Məlumat URL-dən götürüldü' })
    }
  } catch {
    if (force) {
      showToast({ type: 'error', text: 'URL-dən məlumat götürmək mümkün olmadı' })
    }
  } finally {
    metadataLoading.value = false
  }
}

function onTitleInput() {
  titleEdited.value = true
}

function onPickCover(event: Event) {
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
      // Cover is shown small in the player, 600px is more than enough.
      const MAX = 600
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
      form.value.cover = canvas.toDataURL(mime, 0.85)
    }
    img.onerror = () => showToast({ type: 'error', text: 'Şəkil oxunarkən xəta baş verdi' })
    img.src = src
  }
  reader.readAsDataURL(file)
}

function clearCover() {
  form.value.cover = null
  if (coverInput.value) coverInput.value.value = ''
}

function isFormValid(): boolean {
  return !!form.value.title.trim() && isValidUrl(form.value.url.trim())
}

async function submitForm() {
  if (!form.value.title.trim()) {
    showToast({ type: 'error', text: 'Mahnı adını daxil edin' })
    return
  }
  if (!isValidUrl(form.value.url.trim())) {
    showToast({ type: 'error', text: 'Düzgün musiqi linki daxil edin' })
    return
  }

  saving.value = true
  const payload: MusicTrackFormData = {
    title: form.value.title.trim(),
    artist: form.value.artist.trim() || null,
    url: form.value.url.trim(),
    cover: form.value.cover || null,
    is_active: form.value.is_active,
    sort_order: Number(form.value.sort_order) || 0,
  }

  try {
    if (editingId.value !== null) {
      await musicApi.update(editingId.value, payload)
      showToast({ type: 'success', text: 'Musiqi yeniləndi' })
    } else {
      await musicApi.create(payload)
      showToast({ type: 'success', text: 'Yeni musiqi əlavə edildi' })
    }
    closeModal()
    fetchTracks()
  } catch {
    showToast({ type: 'error', text: 'Saxlama zamanı xəta baş verdi' })
  } finally {
    saving.value = false
  }
}

function confirmDelete(track: MusicTrack) {
  deletingId.value = track.id
  deletingName.value = track.title
  showDeleteModal.value = true
}

async function submitDelete() {
  if (deletingId.value === null || deleting.value) return

  deleting.value = true
  try {
    await musicApi.remove(deletingId.value)
    showToast({ type: 'success', text: 'Musiqi silindi' })
    showDeleteModal.value = false
    fetchTracks()
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
    fetchTracks()
  }, 400)
}

function onStatusFilterChange() {
  page.value = 1
  fetchTracks()
}

onMounted(() => {
  fetchTracks()
  fetchSettings()
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-900">Musiqi</h1>
      <p class="mt-1 text-sm text-slate-500">
        Saytın sol aşağı küncündə görünən musiqi pleyeri. YouTube linkləri və birbaşa audio (mp3) linkləri dəstəklənir.
      </p>
    </div>

    <!-- General settings -->
    <section class="mb-8 overflow-hidden rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50/80 via-white to-white shadow-sm">
      <div class="flex items-center gap-3 border-b border-indigo-100/70 px-5 py-4 sm:px-6">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-600/20">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19V6l12-3v13M9 19a3 3 0 11-6 0 3 3 0 016 0zm12-3a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <div>
          <h2 class="text-base font-bold text-slate-900">Ümumi ayarlar</h2>
          <p class="text-xs text-slate-500">Musiqinin saytın hansı səhifələrində çalınacağını buradan idarə edin.</p>
        </div>
      </div>

      <div v-if="settingsLoading" class="flex items-center justify-center py-10">
        <div class="h-6 w-6 animate-spin rounded-full border-4 border-indigo-600 border-t-transparent" />
      </div>

      <div v-else class="space-y-5 px-5 py-5 sm:px-6">
        <label class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white px-4 py-3.5 shadow-xs">
          <span class="flex items-center gap-3">
            <span class="material-symbols-outlined text-indigo-600">public</span>
            <span>
              <span class="block text-sm font-bold text-slate-800">Bütün səhifələrdə çal</span>
              <span class="block text-xs text-slate-500">Aktiv olduqda aşağıdakı səhifə seçimi nəzərə alınmır.</span>
            </span>
          </span>
          <input
            v-model="settingsForm.play_on_all"
            type="checkbox"
            class="h-5 w-5 shrink-0 accent-indigo-600"
          />
        </label>

        <div :class="['transition-opacity', settingsForm.play_on_all ? 'pointer-events-none opacity-40' : 'opacity-100']">
          <div class="mb-2 flex items-center justify-between">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-600">Səhifələr</label>
            <span class="text-2xs font-semibold text-slate-400">{{ settingsForm.pages.length }} seçilib</span>
          </div>

          <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
            <label
              v-for="option in pageOptions"
              :key="option.key"
              class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:border-indigo-200 hover:bg-indigo-50/50"
            >
              <input
                v-model="settingsForm.pages"
                type="checkbox"
                :value="option.key"
                :disabled="settingsForm.play_on_all"
                class="h-4 w-4 accent-indigo-600"
              />
              <span class="material-symbols-outlined text-[18px] text-indigo-500">web</span>
              {{ option.label }}
            </label>
          </div>
        </div>

        <div class="flex justify-end">
          <button
            type="button"
            :disabled="settingsSaving"
            @click="saveSettings"
            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-md transition-all hover:bg-indigo-700 disabled:opacity-50"
          >
            <svg v-if="settingsSaving" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            Ayarları saxla
          </button>
        </div>
      </div>
    </section>

    <!-- Tracks header -->
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-bold text-slate-900">Mahnılar</h2>
        <p class="text-xs text-slate-500">Pleylist sırası ilə göstərilir. İstifadəçi pleyerdən mahnını dəyişə bilər.</p>
      </div>

      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative w-full sm:w-60">
          <input
            v-model="search"
            @input="onSearchInput"
            type="text"
            placeholder="Axtar (ad / ifaçı)..."
            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm font-medium text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
          />
          <svg class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <button
          @click="openCreate"
          class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold uppercase tracking-wider text-white shadow-md transition-all hover:bg-indigo-700 active:scale-95"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
          </svg>
          Əlavə Et
        </button>
      </div>
    </div>

    <!-- Status filter -->
    <div class="mb-5 flex flex-wrap items-center gap-2">
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
    <div v-else-if="tracks.length === 0" class="rounded-2xl border border-slate-200 bg-white py-20 text-center shadow-sm">
      <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50">
        <span class="material-symbols-outlined text-3xl text-indigo-500">music_note</span>
      </div>
      <h3 class="text-lg font-semibold text-slate-900">Heç bir musiqi tapılmadı</h3>
      <p class="mt-1 text-sm text-slate-500">Yeni mahnı əlavə etmək üçün "Əlavə Et" düyməsinə klik edin.</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
      <table class="w-full min-w-[760px]">
        <thead>
          <tr class="border-b border-slate-100 bg-slate-50">
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Mahnı</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Mənbə</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Sıra</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
            <th class="px-5 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Əməliyyat</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="track in tracks" :key="track.id" class="transition-colors hover:bg-slate-50">
            <td class="px-5 py-4">
              <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-indigo-200 bg-indigo-50">
                  <img v-if="canPreview(track.thumbnail)" :src="track.thumbnail!" alt="" class="h-full w-full object-cover" />
                  <span v-else class="material-symbols-outlined text-indigo-400">music_note</span>
                </div>
                <div class="min-w-0">
                  <span class="block font-bold text-slate-900">{{ track.title }}</span>
                  <span v-if="track.artist" class="mt-0.5 block max-w-xs truncate text-xs text-slate-500">{{ track.artist }}</span>
                  <span v-else class="text-xs text-slate-400">#{{ track.id }}</span>
                </div>
              </div>
            </td>
            <td class="px-5 py-4 whitespace-nowrap">
              <span
                :class="[
                  'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold',
                  track.source_type === 'youtube'
                    ? 'border border-red-200 bg-red-50 text-red-700'
                    : 'border border-sky-200 bg-sky-50 text-sky-700',
                ]"
              >
                {{ track.source_type === 'youtube' ? 'YouTube' : 'Audio' }}
              </span>
              <span class="mt-1 block max-w-[200px] truncate text-2xs text-slate-400">{{ hostOf(track.url) }}</span>
            </td>
            <td class="px-5 py-4 whitespace-nowrap">
              <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-lg bg-slate-100 px-2 text-xs font-bold text-slate-600">
                {{ track.sort_order }}
              </span>
            </td>
            <td class="px-5 py-4 whitespace-nowrap">
              <span
                v-if="track.is_active"
                class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-800"
              >
                Aktiv ✓
              </span>
              <span v-else class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                Gizlidir
              </span>
            </td>
            <td class="px-5 py-4 whitespace-nowrap text-right space-x-2">
              <a
                :href="track.url"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-slate-700 shadow-xs transition-all hover:bg-slate-50"
              >
                Aç
              </a>
              <button
                @click="openEdit(track)"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-slate-700 shadow-xs transition-all hover:bg-slate-50"
              >
                ✏️ Redaktə
              </button>
              <button
                @click="confirmDelete(track)"
                class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-bold uppercase tracking-wider text-red-700 shadow-xs transition-all hover:bg-red-100"
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
      <Pagination :meta="meta" @page-change="(p: number) => { page = p; fetchTracks() }" />
    </div>

    <!-- CREATE / EDIT MODAL -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs">
      <div class="max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl">
        <div class="sticky top-0 flex items-center justify-between border-b border-slate-100 bg-white/95 px-6 py-4 backdrop-blur">
          <h3 class="text-base font-bold text-slate-900">{{ editingId ? 'Musiqini Redaktə Et' : 'Yeni Musiqi Əlavə Et' }}</h3>
          <button @click="closeModal" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-5 p-6">
          <!-- URL + auto title -->
          <div class="space-y-1.5">
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Musiqi linki</label>
              <span v-if="metadataLoading" class="inline-flex items-center gap-1 text-2xs font-semibold text-indigo-600">
                <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                Məlumat götürülür...
              </span>
            </div>

            <div class="flex gap-2">
              <input
                v-model="form.url"
                @input="onUrlInput"
                @blur="onUrlBlur"
                type="url"
                placeholder="https://www.youtube.com/watch?v=... və ya https://site.com/song.mp3"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
              />
              <button
                type="button"
                :disabled="metadataLoading || !form.url.trim()"
                @click="fetchMetadata(true)"
                title="Başlığı və ifaçını URL-dən yenilə"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700 transition-colors hover:bg-indigo-100 disabled:opacity-40"
              >
                <span class="material-symbols-outlined text-[17px]">autorenew</span>
                Yenilə
              </button>
            </div>

            <p class="text-2xs text-slate-500">
              YouTube video/shorts linki və ya birbaşa audio (mp3, m4a, ogg) linki.
              Başlıq və ifaçı linkdən avtomatik götürülür — istənilən vaxt dəyişə bilərsiniz.
            </p>
          </div>

          <!-- Preview -->
          <div v-if="previewThumbnail" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
            <div class="h-16 w-24 shrink-0 overflow-hidden rounded-lg bg-slate-200">
              <img :src="previewThumbnail" alt="" class="h-full w-full object-cover" />
            </div>
            <div class="min-w-0 text-xs">
              <span
                :class="[
                  'inline-flex rounded-full px-2 py-0.5 text-2xs font-bold',
                  detectedYoutubeId ? 'bg-red-100 text-red-700' : 'bg-sky-100 text-sky-700',
                ]"
              >
                {{ detectedYoutubeId ? 'YouTube' : 'Audio' }}
              </span>
              <p class="mt-1 truncate font-semibold text-slate-700">{{ detectedYoutubeId ? 'YouTube video' : hostOf(form.url) }}</p>
            </div>
          </div>

          <!-- Title -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Mahnı adı</label>
            <input
              v-model="form.title"
              @input="onTitleInput"
              type="text"
              placeholder="URL daxil edin — avtomatik dolacaq"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
          </div>

          <!-- Artist -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">İfaçı</label>
            <input
              v-model="form.artist"
              type="text"
              placeholder="Məsələn: Coldplay"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
          </div>

          <!-- Cover -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Örtük (istəyə bağlı)</label>
            <p class="text-2xs text-slate-500">Boş buraxılsa, YouTube video şəkli avtomatik istifadə olunur.</p>

            <div class="flex items-center gap-4">
              <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-indigo-200 bg-indigo-50">
                <img v-if="canPreview(form.cover)" :src="form.cover!" alt="" class="h-full w-full object-cover" />
                <span v-else class="material-symbols-outlined text-2xl text-indigo-400">image</span>
              </div>
              <div class="flex flex-col gap-2">
                <label class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-slate-50">
                  {{ form.cover ? 'Şəkli dəyiş' : 'Şəkil seç' }}
                  <input ref="coverInput" type="file" accept="image/*" class="hidden" @change="onPickCover" />
                </label>
                <button
                  v-if="form.cover"
                  type="button"
                  @click="clearCover"
                  class="text-xs font-bold uppercase tracking-wider text-red-600 hover:text-red-800"
                >
                  Şəkli sil
                </button>
              </div>
            </div>
          </div>

          <!-- Order + active -->
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Sıra</label>
              <input
                v-model.number="form.sort_order"
                type="number"
                min="0"
                max="9999"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
              />
            </div>

            <label class="flex cursor-pointer items-center justify-between gap-3 self-end rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5">
              <span class="text-sm font-bold text-slate-800">Saytda aktiv</span>
              <input v-model="form.is_active" type="checkbox" class="h-5 w-5 accent-indigo-600" />
            </label>
          </div>

          <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-slate-50"
            >
              Ləğv Et
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-5 py-2 text-xs font-bold uppercase tracking-wider text-white shadow-md transition-all hover:bg-indigo-700 disabled:opacity-50"
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
        <h3 class="text-lg font-bold text-slate-900">Bu musiqini silmək istədiyinizə əminsiniz?</h3>
        <p class="mt-1 text-sm text-slate-500"><strong>{{ deletingName }}</strong> silinəcək. Bu əməliyyat geri qaytarıla bilməz.</p>
        <div class="mt-6 flex items-center justify-end gap-3">
          <button
            @click="showDeleteModal = false"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-slate-50"
          >
            Ləğv Et
          </button>
          <button
            @click="submitDelete"
            :disabled="deleting"
            class="rounded-xl bg-red-600 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white shadow-md transition-all hover:bg-red-700 disabled:opacity-50"
          >
            Sil
          </button>
        </div>
      </div>
    </div>

    <Toast />
  </div>
</template>
