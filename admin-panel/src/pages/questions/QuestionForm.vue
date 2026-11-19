<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import QuestionFormContent from '../../components/QuestionFormContent.vue'
import Toast from '../../components/Toast.vue'

const route = useRoute()
const router = useRouter()

const editingId = computed(() => route.params.id ? Number(route.params.id) : null)

function goBack() {
  router.push('/questions')
}

// Yeni sual rejiminde kayıttan sonra form açık kalır (art arda ekleme için);
// düzenleme modunda ise kayıttan sonra listeye dönülür.
function onSaved() {
  if (editingId.value) goBack()
}
</script>

<template>
  <Toast />

  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center gap-3">
      <button
        @click="goBack"
        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 transition-colors"
        title="Geri"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ editingId ? 'Sualı redaktə et' : 'Yeni sual' }}</h1>
        <p class="mt-1 text-sm text-gray-500">
          {{ editingId ? 'Mövcud sualın məlumatlarını dəyişin' : 'Dərs üçün yeni test sualı yaradın' }}
        </p>
      </div>
    </div>
  </div>

  <div class="w-full max-w-7xl">
    <QuestionFormContent
      :question-id="editingId"
      reset-after-save
      @saved="onSaved"
      @cancel="goBack"
    />
  </div>
</template>
