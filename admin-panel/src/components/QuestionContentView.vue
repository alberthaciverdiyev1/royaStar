<script setup lang="ts">
import type { ContentBlocks } from '../api/questions'

/**
 * Renders a question's content blocks properly:
 * - text  → paragraph
 * - image → <img>
 * - audio → <audio controls>
 *
 * `compact` mode limits text to a single line and shows images small, suitable
 * for table/list rows (e.g. the Questions table).
 */
defineProps<{
  blocks: ContentBlocks | null
  compact?: boolean
}>()

function hasContent(blocks: ContentBlocks | null | undefined): boolean {
  return !!blocks && Array.isArray(blocks) && blocks.length > 0
}
</script>

<template>
  <div v-if="hasContent(blocks)" :class="compact ? 'space-y-1' : 'space-y-2'">
    <template v-for="(block, i) in blocks" :key="i">
      <!-- Text -->
      <p
        v-if="block.type === 'text'"
        :class="compact ? 'truncate text-sm text-gray-900' : 'text-sm text-gray-900'"
      >{{ block.content }}</p>

      <!-- Image -->
      <img
        v-else-if="block.type === 'image' && block.content"
        :src="block.content"
        alt=""
        :class="compact
          ? 'inline-block h-8 w-8 rounded-lg border border-gray-200 object-contain align-middle'
          : 'max-h-40 w-full rounded-lg border border-gray-200 object-contain'"
      />

      <!-- Audio -->
      <audio
        v-else-if="block.type === 'audio' && block.content"
        :src="block.content"
        controls
        :class="compact ? 'h-8 w-40' : 'h-10 w-full'"
      />
    </template>
  </div>
  <span v-else class="text-sm text-gray-400">—</span>
</template>
