<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const error = ref('')
const submitting = ref(false)

async function handleSubmit() {
  error.value = ''
  submitting.value = true
  try {
    await auth.login(email.value, password.value)
    router.push('/')
  } catch (err: any) {
    error.value =
      err.response?.data?.message ||
      err.response?.data?.errors?.email?.[0] ||
      'Giriş zamanı xəta baş verdi'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
    <div class="w-full max-w-md">
      <div class="rounded-xl bg-white p-8 shadow-lg">
        <div class="mb-8 text-center">
          <h1 class="text-2xl font-bold text-gray-900">RoyaStars</h1>
          <p class="mt-1 text-sm text-gray-500">Admin panelinə xoş gəlmisiniz</p>
        </div>

        <div
          v-if="error"
          class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-600"
        >
          {{ error }}
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">E-poçt</label>
            <input
              id="email"
              v-model="email"
              type="email"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
              placeholder="admin@royastar.com"
            />
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Şifrə</label>
            <input
              id="password"
              v-model="password"
              type="password"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
              placeholder="••••••••"
            />
          </div>

          <button
            type="submit"
            :disabled="submitting"
            class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
          >
            {{ submitting ? 'Giriş edilir...' : 'Daxil ol' }}
          </button>
        </form>

        <p class="mt-6 text-center text-xs text-gray-400">RoyaStars Admin Panel v1.0</p>
      </div>
    </div>
  </div>
</template>
