<script setup lang="ts">
withDefaults(defineProps<{ cols?: number }>(), { cols: 3 })
</script>

<template>
  <div class="ds-cardgrid" :style="{ '--cols': cols }">
    <slot />
  </div>
</template>

<style scoped>
.ds-cardgrid {
  display: grid;
  grid-template-columns: repeat(var(--cols), minmax(0, 1fr));
  gap: var(--sp-6);
  width: 100%;
}
</style>
