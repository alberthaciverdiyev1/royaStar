<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { usersApi, type User } from '../../api/users'
import Pagination from '../../components/Pagination.vue'
import Toast from '../../components/Toast.vue'
import { showToast } from '../../stores/toast'
import type { PaginationMeta } from '../../api/types'

const users = ref<User[]>([])
const meta = ref<PaginationMeta | null>(null)
const loading = ref(true)
const page = ref(1)
const approving = ref<number | null>(null)

async function fetchPending() {
  loading.value = true
  try {
    const res = await usersApi.pending({ page: page.value, per_page: 20 })
    users.value = res.data.data
    meta.value = res.data.meta
  } catch {
    showToast({ type: 'error', text: 'Istifadəçilər yüklənərkən xəta baş verdi' })
  } finally {
    loading.value = false
  }
}

async function approveUser(userId: number) {
  approving.value = userId
  try {
    await usersApi.approve(userId)
    users.value = users.value.filter((u) => u.id !== userId)
    showToast({ type: 'success', text: 'İstifadəçi təsdiqləndi' })
  } catch {
    showToast({ type: 'error', text: 'Təsdiqləmə zamanı xəta baş verdi' })
  } finally {
    approving.value = null
  }
}

function formatDate(dateStr: string) {
  return new Date(dateStr).toLocaleDateString('az-AZ')
}

onMounted(fetchPending)
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Təsdiq gözləyən istifadəçilər</h1>
        <p class="mt-1 text-sm text-slate-500">Yeni qeydiyyatdan keçmiş və təsdiq gözləyən hesablar</p>
      </div>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-indigo-600 border-t-transparent" />
    </div>

    <div v-else-if="users.length === 0" class="rounded-2xl border border-slate-200 bg-white py-20 text-center">
      <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-green-50">
        <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-slate-900">Təsdiq gözləyən istifadəçi yoxdur</h3>
      <p class="mt-1 text-sm text-slate-500">Bütün istifadəçilər təsdiqlənib.</p>
    </div>

    <div v-else class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <table class="w-full">
        <thead>
          <tr class="border-b border-slate-100 bg-slate-50">
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Ad</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">E-poçt</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Telefon</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Tip</th>
            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Tarix</th>
            <th class="px-5 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Əməliyyat</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="user in users" :key="user.id" class="transition-colors hover:bg-slate-50">
            <td class="px-5 py-4 whitespace-nowrap">
              <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-600">
                  {{ (user.name?.charAt(0) || '?').toUpperCase() }}
                </div>
                <span class="font-medium text-slate-900">{{ user.name }} {{ user.surname || '' }}</span>
              </div>
            </td>
            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600">{{ user.email }}</td>
            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600">{{ user.phone }}</td>
            <td class="px-5 py-4 whitespace-nowrap">
              <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                {{ user.type }}
              </span>
            </td>
            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-500">{{ formatDate(user.created_at) }}</td>
            <td class="px-5 py-4 whitespace-nowrap text-right">
              <button
                @click="approveUser(user.id)"
                :disabled="approving === user.id"
                class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-3.5 py-2 text-xs font-bold text-white uppercase tracking-wider shadow-sm transition-all hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
              >
                <svg v-if="approving === user.id" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <svg v-else class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Təsdiq et
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="meta" class="mt-4">
      <Pagination :meta="meta" @page-change="(p: number) => { page = p; fetchPending() }" />
    </div>

    <Toast />
  </div>
</template>
