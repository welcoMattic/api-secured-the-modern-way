<script setup lang="ts">
withDefaults(defineProps<{ label?: string; europe?: boolean; cols?: number }>(), {
  europe: false,
  cols: 5,
})
</script>

<template>
  <div class="ds-sgroup" :class="{ 'ds-sgroup--eu': europe }">
    <div class="ds-sgroup__label"><span v-if="europe">🇪🇺 </span>{{ label }}</div>
    <div class="ds-sgroup__grid" :style="{ '--cols': cols }">
      <slot />
    </div>
  </div>
</template>

<style scoped>
.ds-sgroup {
  border-radius: var(--radius-lg);
  padding: 0.85rem 1.25rem 1rem;
  border: 1px solid transparent;
}
.ds-sgroup--eu {
  background: rgba(var(--a-1-rgb), 0.07);
  border-color: rgba(var(--a-1-rgb), 0.22);
}
.ds-sgroup__label {
  font-family: "Sora", sans-serif;
  font-weight: 700;
  font-size: 0.78rem;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--c-muted);
  margin-bottom: 0.7rem;
}
.ds-sgroup--eu .ds-sgroup__label { color: var(--a-1); }
.ds-sgroup__grid {
  display: grid;
  grid-template-columns: repeat(var(--cols, 5), minmax(0, 1fr));
  gap: 0.9rem 1rem;
  align-items: start;
}
</style>
