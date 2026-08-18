<script setup lang="ts">
withDefaults(defineProps<{ gapX?: number; gapY?: number }>(), { gapX: 3, gapY: 1.75 })
</script>

<template>
  <div class="ds-logogrid" :style="{ columnGap: gapX + 'rem', rowGap: gapY + 'rem' }">
    <slot />
  </div>
</template>

<style scoped>
.ds-logogrid {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: flex-start;
  padding-top: 1.5rem;
}
</style>
