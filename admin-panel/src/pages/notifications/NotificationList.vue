<script setup lang="ts">
import { ref, onMounted } from 'vue'
import Pagination from '../../components/Pagination.vue'
import ConfirmDialog from '../../components/ConfirmDialog.vue'
import Toast from '../../components/Toast.vue'
import { showToast } from '../../stores/toast'
import {
  notificationsApi,
  type AdminNotification,
  type NotificationSettings,
  type NotificationType,
} from '../../api/notifications'
import type { PaginationMeta } from '../../api/types'

type SettingKey = keyof NotificationSettings

interface ActionRow {
  key: SettingKey
  label: string
  description: string
  icon: 'video' | 'quiz' | 'exam'
  tone: string
}

const actionRows: ActionRow[] = [
  {
    key: 'notify_lesson_video',
    label: 'Dərs videoları',
    description: 'Dərsə yeni video əlavə edildikdə şagirdlərə bildiriş göndərilsin.',
    icon: 'video',
    tone: 'bg-indigo-50 text-indigo-600',
  },
  {
    key: 'notify_quiz',
    label: 'Quizlər',
    description: 'Yeni quiz yaradıldıqda şagirdlərə bildiriş göndərilsin.',
    icon: 'quiz',
    tone: 'bg-amber-50 text-amber-600',
  },
  {
    key: 'notify_exam',
    label: 'İmtahanlar',
    description: 'Yeni imtahan yaradıldıqda şagirdlərə bildiriş göndərilsin.',
    icon: 'exam',
    tone: 'bg-rose-50 text-rose-600',
  },
]

const notifications = ref<AdminNotification[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(true)
const page = ref(1)
const typeFilter = ref<NotificationType | ''>('')

const settings = ref<NotificationSettings | null>(null)
const loadingSettings = ref(true)
const savingSettings = ref(false)

const deletingId = ref<number | null>(null)
const deletingTitle = ref('')
const showDeleteModal = ref(false)
const deleting = ref(false)

const typeLabels: Record<string, string> = {
  lesson_video: 'Dərs videosu',
  quiz: 'Quiz',
  exam: 'İmtahan',
}

const typeBadges: Record<string, string> = {
  lesson_video: 'border-indigo-200 bg-indigo-50 text-indigo-700',
  quiz: 'border-amber-200 bg-amber-50 text-amber-700',
  exam: 'border-rose-200 bg-rose-50 text-rose-700',
}

const filters: { value: NotificationType | ''; label: string }[] = [
  { value: '', label: 'Hamısı' },
  { value: 'lesson_video', label: 'Dərs videoları' },
  { value: 'quiz', label: 'Quizlər' },
  { value: 'exam', label: 'İmtahanlar' },
]

async function fetchNotifications() {
  loading.value = true
  try {
    const res = await notificationsApi.list({
      page: page.value,
      per_page: 20,
      type: typeFilter.value || undefined,
    })
    notifications.value = res.data
    meta.value = res.meta
  } catch {
    showToast({ type: 'error', text: 'Bildirişlər yüklənərkən xəta baş verdi' })
  } finally {
    loading.value = false
  }
}

async function fetchSettings() {
  loadingSettings.value = true
  try {
    const res = await notificationsApi.getSettings()
    settings.value = res.data
  } catch {
    showToast({ type: 'error', text: 'Tənzimləmələr yüklənərkən xəta baş verdi' })
  } finally {
    loadingSettings.value = false
  }
}

async function toggleSetting(key: SettingKey) {
  if (!settings.value || savingSettings.value) return

  const previous = { ...settings.value }
  settings.value[key] = !settings.value[key]
  savingSettings.value = true

  try {
    const res = await notificationsApi.updateSettings({ ...settings.value })
    settings.value = res.data
    showToast({ type: 'success', text: 'Bildiriş tənzimləmələri yeniləndi' })
  } catch {
    settings.value = previous
    showToast({ type: 'error', text: 'Tənzimləmə saxlanılmadı' })
  } finally {
    savingSettings.value = false
  }
}

function onFilterChange(type: NotificationType | '') {
  typeFilter.value = type
  page.value = 1
  fetchNotifications()
}

function onPageChange(next: number) {
  page.value = next
  fetchNotifications()
}

function confirmDelete(item: AdminNotification) {
  deletingId.value = item.id
  deletingTitle.value = item.title
  showDeleteModal.value = true
}

function closeDeleteModal() {
  if (deleting.value) return
  showDeleteModal.value = false
  deletingId.value = null
  deletingTitle.value = ''
}

async function submitDelete() {
  if (deletingId.value === null || deleting.value) return
  deleting.value = true
  try {
    await notificationsApi.remove(deletingId.value)
    showToast({ type: 'success', text: 'Bildiriş silindi' })
    showDeleteModal.value = false
    deletingId.value = null
    deletingTitle.value = ''
    fetchNotifications()
  } catch {
    showToast({ type: 'error', text: 'Silinmə zamanı xəta baş verdi' })
  } finally {
    deleting.value = false
  }
}

function readPercent(item: AdminNotification): number {
  if (!item.recipients_count) return 0
  return Math.round((item.read_count / item.recipients_count) * 100)
}

function formatDate(value: string | null): string {
  if (!value) return '—'
  return new Date(value).toLocaleString('az-AZ', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  fetchNotifications()
  fetchSettings()
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-900">Bildirişlər</h1>
      <p class="mt-1 text-sm text-slate-500">
        Şagirdlərə göndərilən bildirişlər və hansı əməliyyatların bildiriş göndərəcəyi.
      </p>
    </div>

    <!-- Action settings -->
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
        <div>
          <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Bildiriş Qaydaları</h2>
          <p class="mt-1 text-xs text-slate-500">
            Söndürülmüş əməliyyat üçün yeni bildiriş yaradılmır. Bu, həm saytdaki bildirişlərə, həm də push bildirişlərinə aiddir.
          </p>
        </div>
        <span
          v-if="savingSettings"
          class="inline-flex shrink-0 items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700"
        >
          <span class="h-3 w-3 animate-spin rounded-full border-2 border-indigo-600 border-t-transparent" />
          Saxlanılır...
        </span>
      </div>

      <div v-if="loadingSettings" class="flex items-center justify-center py-10">
        <div class="h-7 w-7 animate-spin rounded-full border-4 border-indigo-600 border-t-transparent" />
      </div>

      <div v-else class="grid gap-3 p-4 sm:grid-cols-3 sm:p-5">
        <div
          v-for="row in actionRows"
          :key="row.key"
          class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4"
        >
          <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-xl', row.tone]">
            <svg v-if="row.icon === 'video'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <svg v-else-if="row.icon === 'quiz'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>

          <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-2">
              <span class="text-sm font-bold text-slate-900">{{ row.label }}</span>
              <button
                type="button"
                role="switch"
                :aria-checked="settings?.[row.key] ?? false"
                :disabled="!settings || savingSettings"
                @click="toggleSetting(row.key)"
                :class="[
                  'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors disabled:opacity-50',
                  settings?.[row.key] ? 'bg-indigo-600' : 'bg-slate-300',
                ]"
              >
                <span
                  :class="[
                    'inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform',
                    settings?.[row.key] ? 'translate-x-6' : 'translate-x-1',
                  ]"
                />
              </button>
            </div>
            <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ row.description }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-4 flex flex-wrap items-center gap-2">
      <button
        v-for="f in filters"
        :key="f.value || 'all'"
        @click="onFilterChange(f.value)"
        :class="[
          'rounded-full border px-4 py-1.5 text-xs font-bold uppercase tracking-wider transition-colors',
          typeFilter === f.value
            ? 'border-indigo-600 bg-indigo-600 text-white shadow-sm'
            : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700',
        ]"
      >
        {{ f.label }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-indigo-600 border-t-transparent" />
    </div>

    <!-- Empty -->
    <div v-else-if="notifications.length === 0" class="rounded-2xl border border-slate-200 bg-white py-20 text-center shadow-sm">
      <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50">
        <svg class="h-8 w-8 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-9.33-5M6 9a6 6 0 00-.67 2.7v2.458c0 .538-.214 1.055-.595 1.436L3 17h9m3 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-slate-900">Hələ bildiriş göndərilməyib</h3>
      <p class="mt-1 text-sm text-slate-500">Yeni dərs videosu, quiz və ya imtahan yaradıldıqda bildirişlər burada görünəcək.</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
      <table class="w-full min-w-[820px]">
        <thead>
          <tr class="border-b border-slate-100 bg-slate-50">
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Bildiriş</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Növ</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Alıcı</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Oxunub</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Tarix</th>
            <th class="px-5 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Əməliyyat</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="item in notifications" :key="item.id" class="transition-colors hover:bg-slate-50">
            <td class="px-5 py-4">
              <div class="max-w-sm">
                <span class="block font-bold text-slate-900">{{ item.title }}</span>
                <span v-if="item.body" class="mt-0.5 block truncate text-xs text-slate-500">{{ item.body }}</span>
                <a
                  v-if="item.url"
                  :href="item.url"
                  target="_blank"
                  rel="noopener"
                  class="mt-1 inline-block text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:underline"
                >
                  {{ item.url }}
                </a>
              </div>
            </td>
            <td class="px-5 py-4 whitespace-nowrap">
              <span :class="['inline-flex rounded-full border px-2.5 py-0.5 text-xs font-bold', typeBadges[item.type] || 'border-slate-200 bg-slate-100 text-slate-600']">
                {{ typeLabels[item.type] || item.type }}
              </span>
            </td>
            <td class="px-5 py-4 whitespace-nowrap">
              <span class="text-sm font-bold text-slate-900">{{ item.recipients_count }}</span>
              <span class="ml-1 text-xs text-slate-500">şagird</span>
            </td>
            <td class="px-5 py-4 whitespace-nowrap">
              <div class="flex items-center gap-2">
                <div class="h-1.5 w-20 overflow-hidden rounded-full bg-slate-100">
                  <div class="h-full rounded-full bg-emerald-500" :style="{ width: `${readPercent(item)}%` }" />
                </div>
                <span class="text-xs font-semibold text-slate-600">
                  {{ item.read_count }}/{{ item.recipients_count }}
                </span>
              </div>
            </td>
            <td class="px-5 py-4 whitespace-nowrap text-xs font-medium text-slate-500">
              {{ formatDate(item.created_at) }}
            </td>
            <td class="px-5 py-4 whitespace-nowrap text-right">
              <button
                @click="confirmDelete(item)"
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
      <Pagination :meta="meta" @page-change="onPageChange" />
    </div>

    <ConfirmDialog
      :open="showDeleteModal"
      title="Bildirişi sil"
      :message="`“${deletingTitle}” bildirişi bütün şagirdlərin bildiriş qutusundan silinəcək. Davam edilsin?`"
      confirm-text="Sil"
      :loading="deleting"
      @confirm="submitDelete"
      @cancel="closeDeleteModal"
    />

    <Toast />
  </div>
</template>
