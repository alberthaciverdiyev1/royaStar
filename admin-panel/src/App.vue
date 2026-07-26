<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from './stores/auth'
import { RouterView } from 'vue-router'

const auth = useAuthStore()
const initialized = ref(false)

onMounted(async () => {
  await auth.initialize()
  initialized.value = true
})
</script>

<template>
  <div v-if="!initialized" class="flex h-screen items-center justify-center bg-slate-50">
    <div class="h-8 w-8 animate-spin rounded-full border-4 border-indigo-600 border-t-transparent" />
  </div>
  <RouterView v-else />
</template>
