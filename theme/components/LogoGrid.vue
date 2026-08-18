<script setup lang="ts">
withDefaults(defineProps<{ gapX?: number; gapY?: number; cols?: number }>(), { gapX: 3, gapY: 1.75, cols: 0 })
</script>

<template>
  <div
    class="ds-logogrid"
    :class="{ 'ds-logogrid--cols': cols > 0 }"
    :style="{ columnGap: gapX + 'rem', rowGap: gapY + 'rem', '--cols': cols }"
  >
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

/* Fixed-column variant: keeps rows even when labels have uneven widths,
   which flex-wrap cannot guarantee. */
.ds-logogrid--cols {
  display: grid;
  grid-template-columns: repeat(var(--cols), minmax(0, 1fr));
  justify-items: center;
}
</style>
