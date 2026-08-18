<script setup lang="ts">
withDefaults(defineProps<{
  src: string
  label?: string
  eu?: boolean
  strong?: boolean
  size?: number
}>(), { eu: false, strong: false, size: 4 })
</script>

<template>
  <span class="ds-logo">
    <img :src="src" :alt="label || ''" :style="{ width: size + 'rem', height: size + 'rem' }" />
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
