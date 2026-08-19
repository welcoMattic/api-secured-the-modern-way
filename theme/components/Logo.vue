<script setup lang="ts">
import { computed } from 'vue'
import { resolveAssetUrl } from '../utils/layoutHelper'

const props = withDefaults(defineProps<{
  src: string
  label?: string
  eu?: boolean
  strong?: boolean
  size?: number
}>(), { eu: false, strong: false, size: 4 })

// Vite rebases `<img src="/foo.png">` written literally in a template, but not a
// path that arrives through a prop. Without this, every logo 404s once the deck is
// served under a base path (GitHub Pages: /<repo>/).
const resolvedSrc = computed(() => resolveAssetUrl(props.src))
</script>

<template>
  <span class="ds-logo">
    <img :src="resolvedSrc" :alt="label || ''" :style="{ width: size + 'rem', height: size + 'rem' }" />
    <span class="ds-logo__label" :class="{ 'ds-logo__label--strong': strong }">
      <span v-html="label"></span><span v-if="eu"> 🇪🇺</span>
    </span>
  </span>
</template>

<style scoped>
.ds-logo { display: flex; flex-direction: column; align-items: center; }
.ds-logo img { object-fit: contain; }
.ds-logo__label { margin-top: 0.4rem; text-align: center; line-height: 1.2; font-size: 0.95rem; }
.ds-logo__label :deep(small) { font-size: 0.75rem; opacity: 0.7; }
.ds-logo__label--strong { font-weight: 700; }
</style>
