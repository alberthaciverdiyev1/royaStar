<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
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

const grades = ref<Grade[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(true)
const search = ref('')
const page = ref(1)

// Modal state
const modalOpen = ref(false)
const editingGrade = ref<Grade | null>(null)
const name = ref('')
const saving = ref(false)
const formError = ref('')

// Delete state
const deleteTarget = ref<Grade | null>(null)
const deleting = ref(false)

const columns: Column[] = [
  { key: 'id', label: 'ID', className: 'w-16' },
  { key: 'name', label: 'Ad' },
  {
    key: 'created_at',
    label: 'Yaradılma tarixi',
    render: (g: any) => new Date(g.created_at).toLocaleDateString('az-AZ'),
  },
]

async function fetchGrades() {
  loading.value = true
  try {
    const res = await gradesApi.list({ search: search.value || undefined, page: page.value, per_page: 15 })
    grades.value = res.data
    meta.value = res.meta
  } catch {
    showToast({ type: 'error', text: 'Siniflər yüklənərkən xəta baş verdi' })
  } finally {
    loading.value = false
  }
}

watch(search, () => { page.value = 1 })
watch([page, search], fetchGrades)

onMounted(fetchGrades)

function openCreate() {
  editingGrade.value = null
  name.value = ''
  formError.value = ''
  modalOpen.value = true
}

function openEdit(grade: Grade) {
  editingGrade.value = grade
  name.value = grade.name
  formError.value = ''
  modalOpen.value = true
}

async function handleSave() {
  if (!name.value.trim()) {
    formError.value = 'Ad tələb olunur'
    return
  }
  saving.value = true
  formError.value = ''
  try {
    if (editingGrade.value) {
      await gradesApi.update(editingGrade.value.id, { name: name.value })
      showToast({ type: 'success', text: 'Sinif yeniləndi' })
    } else {
      await gradesApi.create({ name: name.value })
      showToast({ type: 'success', text: 'Sinif yaradıldı' })
    }
    modalOpen.value = false
    fetchGrades()
  } catch {
    showToast({ type: 'error', text: 'Xəta baş verdi' })
  } finally {
    saving.value = false
  }
}

async function handleDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await gradesApi.delete(deleteTarget.value.id)
    showToast({ type: 'success', text: 'Sinif silindi' })
    deleteTarget.value = null
    fetchGrades()
  } catch {
    showToast({ type: 'error', text: 'Silinərkən xəta baş verdi' })
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <Toast />

  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Siniflər</h1>
      <p class="mt-1 text-sm text-gray-500">Bütün sinifləri idarə edin</p>
    </div>
    <button
      @click="openCreate"
      class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Yeni sinif
    </button>
  </div>

  <div class="mb-5 max-w-xs">
    <SearchInput v-model="search" placeholder="Sinif axtar..." />
  </div>

  <Table
    :columns="columns"
    :data="grades"
    :loading="loading"
    empty-message="Hələ heç bir sinif əlavə edilməyib"
    :on-edit="openEdit"
    :on-delete="(g: any) => deleteTarget = g"
  />

  <Pagination v-if="meta" :meta="meta" @page-change="(p: number) => page = p" />

  <!-- Create/Edit Modal -->
  <Modal
    :open="modalOpen"
    :title="editingGrade ? 'Sinifi redaktə et' : 'Yeni sinif'"
    @close="modalOpen = false"
  >
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Sinif adı</label>
        <input
          v-model="name"
          type="text"
          placeholder="Sinif adını daxil edin"
          :class="[
            'w-full rounded-xl border bg-white py-2.5 px-3 text-sm text-gray-900 placeholder-gray-400 transition-colors focus:outline-none focus:ring-2',
            formError
              ? 'border-red-300 focus:border-red-400 focus:ring-red-100'
              : 'border-gray-200 focus:border-indigo-400 focus:ring-indigo-100'
          ]"
        />
        <p v-if="formError" class="mt-1.5 text-xs text-red-500">{{ formError }}</p>
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
          {{ saving ? 'Saxlanılır...' : editingGrade ? 'Yadda saxla' : 'Yarat' }}
        </button>
      </div>
    </div>
  </Modal>

  <!-- Delete Confirmation -->
  <ConfirmDialog
    :open="!!deleteTarget"
    :title="'Sinifi sil'"
    :message='deleteTarget ? `"${deleteTarget.name}" sinfini silmək istədiyinizə əminsiniz?` : ""'
    :loading="deleting"
    @confirm="handleDelete"
    @cancel="deleteTarget = null"
  />
</template>
