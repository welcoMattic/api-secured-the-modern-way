<script setup lang="ts">
import { computed } from 'vue'
const props = withDefaults(defineProps<{
  accent?: number | string
  icon?: string
  title?: string
  tag?: string
  question?: string
  lead?: string
}>(), { accent: 1 })

const vars = computed(() => ({
  '--rgb': `var(--a-${props.accent}-rgb)`,
  '--solid': `var(--a-${props.accent})`,
}))
</script>

<template>
  <div class="ds-pillar" :style="vars">
    <div class="ds-pillar__icon">{{ icon }}</div>

    <div class="ds-pillar__head">
      <span class="ds-pillar__name">{{ title }}</span>
      <span v-if="tag" class="ds-pillar__tag">{{ tag }}</span>
    </div>

    <div class="ds-pillar__rule"></div>

    <div class="ds-pillar__question">
      <span><b>{{ question }}</b> {{ lead }}</span>
    </div>

    <div class="ds-pillar__body"><slot /></div>
  </div>
</template>

<style scoped>
.ds-pillar {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 1.5rem 1.25rem 1.6rem;
  border-radius: var(--radius-lg);
  background: rgba(var(--rgb), .06);
  border: 1px solid rgba(var(--rgb), .30);
}

.ds-pillar__icon {
  font-size: 2.9rem;
  line-height: 1;
  margin-bottom: .75rem;
}

/* Fixed-height head keeps the three cards aligned even when a name wraps. */
.ds-pillar__head {
  height: 3.4rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: .3rem;
}

.ds-pillar__name {
  font-family: "Sora", sans-serif;
  font-weight: 800;
  font-size: 1.35rem;
  line-height: 1.1;
  color: var(--solid);
}

.ds-pillar__tag {
  font-size: .72rem;
  font-weight: 700;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: var(--solid);
  background: rgba(var(--rgb), .14);
  border-radius: var(--radius-pill);
  padding: .18rem .6rem;
}

.ds-pillar__rule {
  width: 2.25rem;
  height: 4px;
  border-radius: 2px;
  background: var(--solid);
  margin: .9rem 0 .85rem;
}

/* The Que / Qui / Combien spine: the pedagogical anchor of the whole talk.
   Two-line box so a wrapping question never shifts the cards apart. */
.ds-pillar__question {
  min-height: 3.9rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  line-height: 1.25;
  color: var(--c-fg);
  text-wrap: balance;
}
.ds-pillar__question b {
  font-weight: 800;
  color: var(--solid);
}

.ds-pillar__body {
  margin-top: auto;
  padding-top: .9rem;
  font-size: .92rem;
  line-height: 1.45;
  color: var(--c-muted);
  text-wrap: balance;
}
</style>
