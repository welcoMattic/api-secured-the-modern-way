<script setup lang="ts">
import { computed } from 'vue'
const props = withDefaults(defineProps<{
  accent?: number | string
  icon?: string
  badge?: string
  title?: string
}>(), { accent: 1 })

const vars = computed(() => ({
  '--rgb': `var(--a-${props.accent}-rgb)`,
  '--solid': `var(--a-${props.accent})`,
}))
</script>

<template>
  <div class="ds-card" :style="vars">
    <div v-if="icon || badge" class="ds-card__top">
      <span v-if="badge" class="ds-card__badge">{{ badge }}</span>
      <span v-if="icon" class="ds-card__icon">{{ icon }}</span>
    </div>
    <div v-if="title" class="ds-card__title" v-html="title"></div>
    <div class="ds-card__body"><slot /></div>
  </div>
</template>

<style scoped>
.ds-card {
  height: 100%;
  padding: 1.35rem 1.5rem;
  border-radius: var(--radius-lg);
  background: rgba(var(--rgb), .06);
  border: 1px solid rgba(var(--rgb), .30);
}
.ds-card__top {
  display: flex;
  align-items: center;
  gap: .6rem;
  margin-bottom: .5rem;
}
.ds-card__icon { font-size: 2.2rem; line-height: 1; }
.ds-card__badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  flex: none;
  border-radius: var(--radius-pill);
  background: rgba(var(--rgb), .16);
  color: var(--solid);
  font-weight: 800;
}
.ds-card__title {
  color: var(--solid);
  font-weight: 800;
  font-size: 1.3rem;
  line-height: 1.2;
  margin-bottom: .35rem;
}
.ds-card__title :deep(small) { font-weight: 700; opacity: .7; font-size: .8em; }
.ds-card__body {
  color: var(--c-muted);
  font-size: .95rem;
  line-height: 1.5;
}
.ds-card__body :deep(strong),
.ds-card__body :deep(b) { color: var(--c-fg); }
</style>
