<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { websiteTextsApi, type WebsiteTextGroup, type WebsiteTextItem } from '../../api/website-texts'
import { showToast } from '../../stores/toast'
import Toast from '../../components/Toast.vue'

const groups = ref<WebsiteTextGroup[]>([])
const loading = ref(true)
const saving = ref(false)
const search = ref('')

const drafts = ref<Record<string, string>>({})
/** keys whose override is currently "custom" (non-empty) — used for reset badges */
const customKeys = ref<Set<string>>(new Set())

onMounted(() => {
  websiteTextsApi
    .list()
    .then((res) => {
      groups.value = res.data || []
      const d: Record<string, string> = {}
      const custom = new Set<string>()
      for (const g of groups.value) {
        for (const it of g.items) {
          // Draft = stored override only. Empty input means "use fallback"
          // (fallback is shown as placeholder + caption below the field).
          d[it.key] = it.value ?? ''
          if (it.value && String(it.value).trim() !== '') custom.add(it.key)
        }
      }
      drafts.value = d
      customKeys.value = custom
    })
    .catch(() => showToast({ type: 'error', text: 'Mətnlər yüklənərkən xəta baş verdi' }))
    .finally(() => { loading.value = false })
})

// ── Filtering ──
const q = computed(() => search.value.trim().toLowerCase())

const filteredGroups = computed(() => {
  if (!q.value) return groups.value
  return groups.value
    .map((g) => ({
      ...g,
      items: g.items.filter(
        (it) =>
          it.key.toLowerCase().includes(q.value) ||
          it.fallback.toLowerCase().includes(q.value) ||
          (drafts.value[it.key] || '').toLowerCase().includes(q.value),
      ),
    }))
    .filter((g) => g.items.length > 0)
})

const visibleCount = computed(() => filteredGroups.value.reduce((s, g) => s + g.items.length, 0))

// ── Draft helpers ──
function isCustom(key: string): boolean {
  const v = drafts.value[key]
  return typeof v === 'string' && v.trim() !== ''
}

function resetKey(key: string) {
  drafts.value[key] = ''
  customKeys.value.delete(key)
}

// ── Save ──
async function saveAll() {
  saving.value = true
  // Only send non-empty overrides (empty string = use fallback).
  const payload: Record<string, string> = {}
  for (const [key, val] of Object.entries(drafts.value)) {
    if (typeof val === 'string' && val.trim() !== '') {
      payload[key] = val.trim()
    }
  }
  try {
    const res = await websiteTextsApi.update(payload)
    const saved = res.data || {}
    // Mark all non-empty values as custom after save
    const custom = new Set(Object.keys(saved).filter((k) => String(saved[k]).trim() !== ''))
    customKeys.value = custom
    showToast({ type: 'success', text: 'Mətnlər uğurla yeniləndi' })
  } catch {
    showToast({ type: 'error', text: 'Mətnlər yadda saxlanarkən xəta baş verdi' })
  } finally {
    saving.value = false
  }
}

// ── Collapsible groups ──
const openGroups = ref<Record<string, boolean>>({})
function isOpen(key: string): boolean {
  if (openGroups.value[key] !== undefined) return openGroups.value[key]
  // First group open by default
  const idx = groups.value.findIndex((g) => g.key === key)
  return idx === 0
}
function toggleGroup(key: string) {
  openGroups.value[key] = !isOpen(key)
}

function itemLabel(it: WebsiteTextItem): string {
  // Human-readable label from the dotted key, e.g. "topics.hero.title" -> "hero.title"
  const dot = it.key.indexOf('.')
  return dot >= 0 ? it.key.slice(dot + 1) : it.key
}
</script>

<template>
  <div class="space-y-6">
    <Toast />

    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-xl font-bold text-slate-900">Sayt Mətnləri</h1>
        <p class="mt-1 text-sm text-slate-500">
          Bütün ictimai sayt yazılarını buradan dəyişin. Boş qoyulan sahələr standart mətni göstərir.
        </p>
      </div>
      <button
        :disabled="saving"
        @click="saveAll"
        class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
      >
        <svg v-if="saving" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ saving ? 'Yadda saxlanılır...' : 'Bütün Dəyişiklikləri Yadda Saxla' }}
      </button>
    </div>

    <!-- Search -->
    <div class="relative max-w-md">
      <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <input
        v-model="search"
        type="text"
        placeholder="Açar söz ilə axtar... (məs: hero, nav, topic)"
        class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-900 shadow-xs placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
      />
    </div>

    <!-- Summary -->
    <p class="text-xs font-medium text-slate-500">
      {{ visibleCount }} sahə göstərilir · {{ customKeys.size }} sahə fərdiləşdirilib
    </p>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-24 text-sm font-medium text-slate-400">
      Yüklənir...
    </div>

    <!-- Groups -->
    <div v-else-if="filteredGroups.length" class="space-y-4">
      <div
        v-for="group in filteredGroups"
        :key="group.key"
        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs"
      >
        <!-- Group header -->
        <button
          @click="toggleGroup(group.key)"
          class="flex w-full items-center justify-between gap-3 px-4 py-3.5 text-left transition-colors hover:bg-slate-50"
        >
          <div class="flex items-center gap-3">
            <span class="text-lg">{{ group.icon }}</span>
            <span class="text-sm font-bold text-slate-900">{{ group.label }}</span>
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500">
              {{ group.items.length }}
            </span>
            <span v-if="group.items.some((it) => isCustom(it.key))" class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-600">
              {{ group.items.filter((it) => isCustom(it.key)).length }} dəyişib
            </span>
          </div>
          <svg
            class="h-4 w-4 shrink-0 text-slate-400 transition-transform"
            :class="isOpen(group.key) ? 'rotate-180' : ''"
            fill="none" viewBox="0 0 24 24" stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- Group body -->
        <div v-if="isOpen(group.key)" class="border-t border-slate-100 divide-y divide-slate-100">
          <div v-for="it in group.items" :key="it.key" class="px-4 py-3.5">
            <div class="mb-1.5 flex items-center justify-between gap-3">
              <label class="text-xs font-bold text-slate-700">{{ itemLabel(it) }}</label>
              <span class="font-mono text-[10px] text-slate-400">{{ it.key }}</span>
            </div>
            <div class="flex items-start gap-2">
              <input
                v-model="drafts[it.key]"
                type="text"
                class="w-full rounded-lg border bg-white px-3 py-2 text-sm text-slate-900 shadow-xs placeholder:text-slate-300 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                :class="isCustom(it.key) ? 'border-amber-300' : 'border-slate-200'"
                :placeholder="it.fallback"
              />
              <button
                v-if="isCustom(it.key)"
                @click="resetKey(it.key)"
                class="shrink-0 rounded-lg border border-slate-200 px-2.5 py-2 text-[11px] font-semibold text-slate-500 transition-colors hover:bg-slate-50"
                title="Standarta qaytar"
              >
                Sıfırla
              </button>
            </div>
            <p class="mt-1 text-[11px] text-slate-400">
              Standart: <span class="font-medium text-slate-500">{{ it.fallback }}</span>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="rounded-2xl border-2 border-dashed border-slate-200 py-24 text-center">
      <p class="text-sm font-semibold text-slate-400">Heç bir sahə tapılmadı</p>
      <p class="mt-1 text-xs text-slate-400">Axtarış sözünü dəyişib yenidən cəhd edin.</p>
    </div>
  </div>
</template>
