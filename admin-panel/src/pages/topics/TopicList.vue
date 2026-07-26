<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { subjectsApi, type Subject } from '../../api/subjects'
import { topicsApi, type Topic } from '../../api/topics'
import { gradesApi, type Grade } from '../../api/grades'
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

const subjects = ref<Subject[]>([])
const selectedSubjectId = ref<number | null>(null)
const topics = ref<Topic[]>([])
const grades = ref<Grade[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(true)
const search = ref('')
const page = ref(1)

// Modal state
const modalOpen = ref(false)
const editingTopic = ref<Topic | null>(null)
const name = ref('')
const difficultyLevel = ref(3)
const selectedGradeIds = ref<number[]>([])
const saving = ref(false)
const formError = ref('')

// Delete state
const deleteTarget = ref<Topic | null>(null)
const deleting = ref(false)

const DIFFICULTY_OPTIONS = [
  { value: 1, label: 'Başlanğıc' },
  { value: 2, label: 'Elementar' },
  { value: 3, label: 'Orta' },
  { value: 4, label: 'Qabaqcıl' },
  { value: 5, label: 'Ekspert' },
]

// Load subjects and grades on mount
onMounted(async () => {
  try {
    const [subRes, gradeRes] = await Promise.all([
      subjectsApi.list({ per_page: 100 }),
      gradesApi.all(),
    ])
    subjects.value = subRes.data
    grades.value = gradeRes.data
    if (subRes.data.length > 0) {
      selectedSubjectId.value = subRes.data[0].id
    }
  } catch {
    showToast({ type: 'error', text: 'Məlumatlar yüklənərkən xəta baş verdi' })
  }
})

async function fetchTopics() {
  if (!selectedSubjectId.value) return
  loading.value = true
  try {
    const res = await topicsApi.list(selectedSubjectId.value, {
      search: search.value || undefined,
      page: page.value,
      per_page: 15,
    })
    topics.value = res.data
    meta.value = res.meta
  } catch {
    showToast({ type: 'error', text: 'Mövzular yüklənərkən xəta baş verdi' })
  } finally {
    loading.value = false
  }
}

watch(selectedSubjectId, () => { page.value = 1 })
watch(search, () => { page.value = 1 })
watch([page, search, selectedSubjectId], fetchTopics)

function openCreate() {
  editingTopic.value = null
  name.value = ''
  difficultyLevel.value = 3
  selectedGradeIds.value = []
  formError.value = ''
  modalOpen.value = true
}

function openEdit(topic: Topic) {
  editingTopic.value = topic
  name.value = topic.name
  difficultyLevel.value = topic.difficulty_level
  selectedGradeIds.value = topic.grades?.map((g) => g.id) || []
  formError.value = ''
  modalOpen.value = true
}

function toggleGrade(gradeId: number) {
  const idx = selectedGradeIds.value.indexOf(gradeId)
  if (idx === -1) {
    selectedGradeIds.value.push(gradeId)
  } else {
    selectedGradeIds.value.splice(idx, 1)
  }
}

async function handleSave() {
  if (!name.value.trim()) {
    formError.value = 'Ad tələb olunur'
    return
  }
  if (!selectedSubjectId.value) return

  saving.value = true
  formError.value = ''
  try {
    const payload = {
      name: name.value,
      difficulty_level: difficultyLevel.value,
      grade_ids: selectedGradeIds.value.length > 0 ? selectedGradeIds.value : undefined,
    }

    if (editingTopic.value) {
      await topicsApi.update(selectedSubjectId.value, editingTopic.value.id, payload)
      showToast({ type: 'success', text: 'Mövzu yeniləndi' })
    } else {
      await topicsApi.create(selectedSubjectId.value, payload)
      showToast({ type: 'success', text: 'Mövzu yaradıldı' })
    }
    modalOpen.value = false
    fetchTopics()
  } catch {
    showToast({ type: 'error', text: 'Xəta baş verdi' })
  } finally {
    saving.value = false
  }
}

async function handleDelete() {
  if (!deleteTarget.value || !selectedSubjectId.value) return
  deleting.value = true
  try {
    await topicsApi.delete(selectedSubjectId.value, deleteTarget.value.id)
    showToast({ type: 'success', text: 'Mövzu silindi' })
    deleteTarget.value = null
    fetchTopics()
  } catch {
    showToast({ type: 'error', text: 'Silinərkən xəta baş verdi' })
  } finally {
    deleting.value = false
  }
}

const columns: Column[] = [
  { key: 'id', label: 'ID', className: 'w-16' },
  {
    key: 'name',
    label: 'Ad',
    render: (t: any) =>
      h('div', { class: 'flex items-center gap-2' }, [
        h('span', t.name),
        h('span', {
          class: 'inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-600',
        }, t.difficulty_label),
      ]),
  },
  {
    key: 'grades',
    label: 'Siniflər',
    render: (t: any) => {
      if (!t.grades?.length) return h('span', { class: 'text-xs text-slate-400' }, '—')
      return h('div', { class: 'flex flex-wrap gap-1' }, t.grades.map((g: any) =>
        h('span', {
          key: g.id,
          class: 'inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600',
        }, g.name)
      ))
    },
  },
  {
    key: 'created_at',
    label: 'Yaradılma tarixi',
    render: (t: any) => new Date(t.created_at).toLocaleDateString('az-AZ'),
  },
]
</script>

<template>
  <Toast />

  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Mövzular</h1>
      <p class="mt-1 text-sm text-gray-500">Fənlər üzrə mövzuları idarə edin</p>
    </div>
    <button
      v-if="selectedSubjectId"
      @click="openCreate"
      class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Yeni mövzu
    </button>
  </div>

  <!-- Subject selector -->
  <div class="mb-5">
    <label class="block text-sm font-medium text-gray-700 mb-1.5">Fən seçin</label>
    <select
      v-model="selectedSubjectId"
      class="w-full max-w-xs rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
    >
      <option :value="null">Fən seçin...</option>
      <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
    </select>
  </div>

  <template v-if="selectedSubjectId">
    <div class="mb-5 max-w-xs">
      <SearchInput v-model="search" placeholder="Mövzu axtar..." />
    </div>

    <Table
      :columns="columns"
      :data="topics"
      :loading="loading"
      empty-message="Bu fəndə heç bir mövzu əlavə edilməyib"
      :on-edit="openEdit"
      :on-delete="(t: any) => deleteTarget = t"
    />

    <Pagination v-if="meta" :meta="meta" @page-change="(p: number) => page = p" />
  </template>

  <!-- Create/Edit Modal -->
  <Modal
    :open="modalOpen"
    :title="editingTopic ? 'Mövzunu redaktə et' : 'Yeni mövzu'"
    size="lg"
    @close="modalOpen = false"
  >
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Mövzu adı</label>
        <input
          v-model="name"
          type="text"
          placeholder="Mövzu adını daxil edin"
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
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Çətinlik səviyyəsi</label>
        <select
          v-model="difficultyLevel"
          class="w-full max-w-xs rounded-xl border border-gray-200 bg-white py-2.5 px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-indigo-400 focus:ring-indigo-100 transition-colors"
        >
          <option v-for="opt in DIFFICULTY_OPTIONS" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Siniflər</label>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="grade in grades"
            :key="grade.id"
            type="button"
            @click="toggleGrade(grade.id)"
            :class="[
              'inline-flex items-center rounded-xl border px-3 py-1.5 text-sm font-medium transition-colors',
              selectedGradeIds.includes(grade.id)
                ? 'border-indigo-300 bg-indigo-50 text-indigo-700'
                : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'
            ]"
          >
            <svg
              v-if="selectedGradeIds.includes(grade.id)"
              class="mr-1 h-3.5 w-3.5"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                clip-rule="evenodd"
              />
            </svg>
            {{ grade.name }}
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
          {{ saving ? 'Saxlanılır...' : editingTopic ? 'Yadda saxla' : 'Yarat' }}
        </button>
      </div>
    </div>
  </Modal>

  <!-- Delete Confirmation -->
  <ConfirmDialog
    :open="!!deleteTarget"
    :title="'Mövzunu sil'"
    :message='deleteTarget ? `"${deleteTarget.name}" mövzusunu silmək istədiyinizə əminsiniz?` : ""'
    :loading="deleting"
    @confirm="handleDelete"
    @cancel="deleteTarget = null"
  />
</template>
