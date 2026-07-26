<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { citiesApi, type City } from '../../api/cities'
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

const cities = ref<City[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(true)
const search = ref('')
const page = ref(1)

// Modal state
const modalOpen = ref(false)
const editingCity = ref<City | null>(null)
const name = ref('')
const saving = ref(false)
const formError = ref('')

// Delete state
const deleteTarget = ref<City | null>(null)
const deleting = ref(false)

const columns: Column[] = [
  { key: 'id', label: 'ID', className: 'w-16' },
  { key: 'name', label: 'Ad' },
  {
    key: 'created_at',
    label: 'Yaradılma tarixi',
    render: (city: any) => new Date(city.created_at).toLocaleDateString('az-AZ'),
  },
]

async function fetchCities() {
  loading.value = true
  try {
    const res = await citiesApi.list({ search: search.value || undefined, page: page.value, per_page: 15 })
    cities.value = res.data
    meta.value = res.meta
  } catch {
    showToast({ type: 'error', text: 'Şəhərlər yüklənərkən xəta baş verdi' })
  } finally {
    loading.value = false
  }
}

watch(search, () => { page.value = 1 })
watch([page, search], fetchCities)

onMounted(fetchCities)

function openCreate() {
  editingCity.value = null
  name.value = ''
  formError.value = ''
  modalOpen.value = true
}

function openEdit(city: City) {
  editingCity.value = city
  name.value = city.name
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
    if (editingCity.value) {
      await citiesApi.update(editingCity.value.id, { name: name.value })
      showToast({ type: 'success', text: 'Şəhər yeniləndi' })
    } else {
      await citiesApi.create({ name: name.value })
      showToast({ type: 'success', text: 'Şəhər yaradıldı' })
    }
    modalOpen.value = false
    fetchCities()
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
    await citiesApi.delete(deleteTarget.value.id)
    showToast({ type: 'success', text: 'Şəhər silindi' })
    deleteTarget.value = null
    fetchCities()
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
      <h1 class="text-2xl font-bold text-gray-900">Şəhərlər</h1>
      <p class="mt-1 text-sm text-gray-500">Bütün şəhərləri idarə edin</p>
    </div>
    <button
      @click="openCreate"
      class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Yeni şəhər
    </button>
  </div>

  <div class="mb-5 max-w-xs">
    <SearchInput v-model="search" placeholder="Şəhər axtar..." />
  </div>

  <Table
    :columns="columns"
    :data="cities"
    :loading="loading"
    empty-message="Hələ heç bir şəhər əlavə edilməyib"
    :on-edit="openEdit"
    :on-delete="(city: any) => deleteTarget = city"
  />

  <Pagination v-if="meta" :meta="meta" @page-change="(p: number) => page = p" />

  <!-- Create/Edit Modal -->
  <Modal
    :open="modalOpen"
    :title="editingCity ? 'Şəhəri redaktə et' : 'Yeni şəhər'"
    @close="modalOpen = false"
  >
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Şəhər adı</label>
        <input
          v-model="name"
          type="text"
          placeholder="Şəhər adını daxil edin"
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
          {{ saving ? 'Saxlanılır...' : editingCity ? 'Yadda saxla' : 'Yarat' }}
        </button>
      </div>
    </div>
  </Modal>

  <!-- Delete Confirmation -->
  <ConfirmDialog
    :open="!!deleteTarget"
    :title="'Şəhəri sil'"
    :message='deleteTarget ? `"${deleteTarget.name}" şəhərini silmək istədiyinizə əminsiniz?` : ""'
    :loading="deleting"
    @confirm="handleDelete"
    @cancel="deleteTarget = null"
  />
</template>
