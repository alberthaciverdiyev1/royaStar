<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { topicsApi, type Topic } from '../../api/topics'
import { lessonsApi, type Lesson, type Video } from '../../api/lessons'
import Table from '../../components/Table.vue'
import type { Column } from '../../components/Table.vue'
import Pagination from '../../components/Pagination.vue'
import SearchInput from '../../components/SearchInput.vue'
import Modal from '../../components/Modal.vue'
import ConfirmDialog from '../../components/ConfirmDialog.vue'
import Toast from '../../components/Toast.vue'
import { showToast } from '../../stores/toast'
import type { PaginationMeta } from '../../api/types'
import { h } from 'vue'

const topics = ref<Topic[]>([])
const selectedTopicId = ref<number | null>(null)
const lessons = ref<Lesson[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(true)
const search = ref('')
const page = ref(1)

// Modal state
const modalOpen = ref(false)
const editingLesson = ref<Lesson | null>(null)
const name = ref('')
const description = ref('')
const videos = ref<{ youtube_url: string; name: string }[]>([])
const saving = ref(false)
const formError = ref('')

// Delete state
const deleteTarget = ref<Lesson | null>(null)
const deleting = ref(false)

onMounted(async () => {
  try {
    const res = await topicsApi.list({ per_page: 100 })
    topics.value = res.data
    if (topics.value.length > 0) {
      selectedTopicId.value = topics.value[0].id
    }
  } catch {
    showToast({ type: 'error', text: 'Mövzular yüklənərkən xəta baş verdi' })
  }
})

async function fetchLessons() {
  if (!selectedTopicId.value) {
    lessons.value = []
    meta.value = null
    loading.value = false
    return
  }
  loading.value = true
  try {
    const res = await lessonsApi.list(selectedTopicId.value, {
      search: search.value || undefined,
      page: page.value,
      per_page: 15,
    })
    lessons.value = res.data
    meta.value = res.meta
  } catch {
    showToast({ type: 'error', text: 'Dərslər yüklənərkən xəta baş verdi' })
  } finally {
    loading.value = false
  }
}

watch(selectedTopicId, () => { page.value = 1 })
watch(search, () => { page.value = 1 })
watch([page, search, selectedTopicId], fetchLessons)

function openCreate() {
  editingLesson.value = null
  name.value = ''
  description.value = ''
  videos.value = []
  formError.value = ''
  modalOpen.value = true
}

function openEdit(lesson: Lesson) {
  editingLesson.value = lesson
  name.value = lesson.name
  description.value = lesson.description || ''
  videos.value = (lesson.videos || []).map((v) => ({
    youtube_url: v.youtube_url,
    name: v.name || '',
  }))
  formError.value = ''
  modalOpen.value = true
}

function addVideo() {
  videos.value.push({ youtube_url: '', name: '' })
}

function removeVideo(index: number) {
  videos.value.splice(index, 1)
}

async function handleSave() {
  if (!name.value.trim()) {
    formError.value = 'Ad tələb olunur'
    return
  }
  if (!selectedTopicId.value) return

  saving.value = true
  formError.value = ''
  try {
    const payload = {
      name: name.value,
      description: description.value || undefined,
      videos: videos.value.filter((v) => v.youtube_url.trim()),
    }

    if (editingLesson.value) {
      await lessonsApi.update(selectedTopicId.value, editingLesson.value.id, payload)
      showToast({ type: 'success', text: 'Dərs yeniləndi' })
    } else {
      await lessonsApi.create(selectedTopicId.value, payload)
      showToast({ type: 'success', text: 'Dərs yaradıldı' })
    }
    modalOpen.value = false
    fetchLessons()
  } catch (err: any) {
    const msg = err?.response?.data?.message || err?.response?.data?.errors?.[Object.keys(err?.response?.data?.errors || {})[0]]?.[0] || 'Xəta baş verdi'
    if (err?.response?.data?.errors) {
      formError.value = Object.values(err.response.data.errors).flat().join(', ')
    }
    showToast({ type: 'error', text: msg })
  } finally {
    saving.value = false
  }
}

async function handleDelete() {
  if (!deleteTarget.value || !selectedTopicId.value) return
  deleting.value = true
  try {
    await lessonsApi.delete(selectedTopicId.value, deleteTarget.value.id)
    showToast({ type: 'success', text: 'Dərs silindi' })
    deleteTarget.value = null
    fetchLessons()
  } catch {
    showToast({ type: 'error', text: 'Silinərkən xəta baş verdi' })
  } finally {
    deleting.value = false
  }
}

const columns: Column[] = [
  { key: 'id', label: 'ID', className: 'w-16' },
  { key: 'name', label: 'Ad' },
  {
    key: 'description',
    label: 'Açıqlama',
    render: (l: any) => {
      if (!l.description) return h('span', { class: 'text-xs text-slate-400' }, '—')
      return l.description.length > 60
        ? l.description.slice(0, 60) + '...'
        : l.description
    },
  },
  {
    key: 'videos',
    label: 'Videolar',
    className: 'w-24',
    render: (l: any) => {
      const count = l.videos?.length || 0
      return h('span', {
        class: 'inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600',
      }, `${count} video`)
    },
  },
  {
    key: 'view_count',
    label: 'Baxış',
    className: 'w-20',
  },
  {
    key: 'created_at',
    label: 'Yaradılma tarixi',
    render: (l: any) => new Date(l.created_at).toLocaleDateString('az-AZ'),
  },
]
</script>

<template>
  <Toast />

  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Dərslər</h1>
      <p class="mt-1 text-sm text-gray-500">Mövzular üzrə dərsləri idarə edin</p>
    </div>
    <button
      v-if="selectedTopicId"
      @click="openCreate"
      class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Yeni dərs
    </button>
  </div>

  <!-- Topic selector -->
  <div class="mb-5 max-w-xs">
    <label class="block text-sm font-medium text-gray-700 mb-1.5">Mövzu seçin</label>
    <select
      v-model="selectedTopicId"
      class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
    >
      <option :value="null">Mövzu seçin...</option>
      <option v-for="t in topics" :key="t.id" :value="t.id">{{ t.name }}</option>
    </select>
  </div>

  <template v-if="selectedTopicId">
    <div class="mb-5 max-w-xs">
      <SearchInput v-model="search" placeholder="Dərs axtar..." />
    </div>

    <Table
      :columns="columns"
      :data="lessons"
      :loading="loading"
      empty-message="Bu mövzuda heç bir dərs əlavə edilməyib"
      :on-edit="openEdit"
      :on-delete="(l: any) => deleteTarget = l"
    />

    <Pagination v-if="meta" :meta="meta" @page-change="(p: number) => page = p" />
  </template>

  <!-- Create/Edit Modal -->
  <Modal
    :open="modalOpen"
    :title="editingLesson ? 'Dərsi redaktə et' : 'Yeni dərs'"
    size="lg"
    @close="modalOpen = false"
  >
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Dərsin adı</label>
        <input
          v-model="name"
          type="text"
          placeholder="Dərs adını daxil edin"
          :class="[
            'w-full rounded-xl border bg-white py-2.5 px-3 text-sm text-gray-900 placeholder-gray-400 transition-colors focus:outline-none focus:ring-2',
            formError
              ? 'border-red-300 focus:border-red-400 focus:ring-red-100'
              : 'border-gray-200 focus:border-indigo-400 focus:ring-indigo-100'
          ]"
        />
        <p v-if="formError" class="mt-1.5 text-xs text-red-500">{{ formError }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Açıqlama</label>
        <textarea
          v-model="description"
          rows="3"
          placeholder="Dərs haqqında qısa məlumat"
          class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 placeholder-gray-400 transition-colors focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 resize-none"
        />
      </div>

      <!-- Videos section -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <label class="block text-sm font-medium text-gray-700">Videolar</label>
          <button
            type="button"
            @click="addVideo"
            class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Video əlavə et
          </button>
        </div>
        <div v-if="videos.length === 0" class="rounded-xl border border-dashed border-gray-200 bg-gray-50/50 p-4 text-center text-sm text-gray-400">
          Hələ video əlavə edilməyib
        </div>
        <div v-for="(video, i) in videos" :key="i" class="mb-2 flex items-start gap-2 rounded-xl border border-gray-100 bg-gray-50/30 p-3">
          <div class="flex-1 space-y-2">
            <input
              v-model="video.youtube_url"
              type="url"
              placeholder="YouTube URL (https://youtube.com/watch?v=...)"
              class="w-full rounded-lg border border-gray-200 bg-white py-2 px-3 text-sm text-gray-900 placeholder-gray-400 transition-colors focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100"
            />
            <input
              v-model="video.name"
              type="text"
              placeholder="Video adı (istəyə bağlı)"
              class="w-full rounded-lg border border-gray-200 bg-white py-2 px-3 text-sm text-gray-900 placeholder-gray-400 transition-colors focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100"
            />
          </div>
          <button
            type="button"
            @click="removeVideo(i)"
            class="shrink-0 rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors"
            title="Videonu sil"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <button
          @click="modalOpen = false"
          class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
        >
          İmtina
        </button>
        <button
          @click="handleSave"
          :disabled="saving"
          class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 transition-colors disabled:opacity-50"
        >
          {{ saving ? 'Saxlanılır...' : editingLesson ? 'Yadda saxla' : 'Yarat' }}
        </button>
      </div>
    </div>
  </Modal>

  <!-- Delete Confirmation -->
  <ConfirmDialog
    :open="!!deleteTarget"
    :title="'Dərsi sil'"
    :message='deleteTarget ? `"${deleteTarget.name}" dərsini silmək istədiyinizə əminsiniz?` : ""'
    :loading="deleting"
    @confirm="handleDelete"
    @cancel="deleteTarget = null"
  />
</template>
