<script setup lang="ts">
import { computed } from 'vue'
import { resolveAssetUrl } from '../utils/layoutHelper'

const props = withDefaults(defineProps<{
  src: string
  label?: string
  eu?: boolean
  strong?: boolean
  size?: number
  badge?: string
  badgeAlt?: string
}>(), { eu: false, strong: false, size: 4 })

// Vite rebases `<img src="/foo.png">` written literally in a template, but not a
// path that arrives through a prop. Without this, every logo 404s once the deck is
// served under a base path (GitHub Pages: /<repo>/).
const resolvedSrc = computed(() => resolveAssetUrl(props.src))
// Même piège pour le badge, et il n'y a pas d'échappatoire par v-html : une balise
// img injectée dans le label ne serait pas rebasée non plus.
const resolvedBadge = computed(() => (props.badge ? resolveAssetUrl(props.badge) : null))
</script>

<template>
  <span class="ds-logo">
    <span class="ds-logo__mark" :style="{ width: size + 'rem', height: size + 'rem' }">
      <img :src="resolvedSrc" :alt="label || ''" :style="{ width: size + 'rem', height: size + 'rem' }" />
      <img
        v-if="resolvedBadge"
        class="ds-logo__badge"
        :src="resolvedBadge"
        :alt="badgeAlt || ''"
        :style="{ width: size * 0.42 + 'rem', height: size * 0.42 + 'rem' }"
      />
    </span>
    <span class="ds-logo__label" :class="{ 'ds-logo__label--strong': strong }">
      <span v-html="label"></span><span v-if="eu"> 🇪🇺</span>
    </span>
  </span>
</template>

<style scoped>
.ds-logo { display: flex; flex-direction: column; align-items: center; }
.ds-logo img { object-fit: contain; }
/* Le badge dit « et voilà qui s'en sert », donc il chevauche le logo principal au
   lieu de s'aligner à côté : deux logos côte à côte se liraient comme deux offres. */
.ds-logo__mark { position: relative; display: inline-flex; flex: none; }
.ds-logo__badge {
  position: absolute;
  right: -0.18rem;
  bottom: -0.18rem;
  border-radius: 50%;
  background: var(--c-bg, #fff);
  padding: 0.1rem;
  box-shadow: 0 0 0 1.5px var(--c-bg, #fff);
}
.ds-logo__label { margin-top: 0.4rem; text-align: center; line-height: 1.2; font-size: 0.95rem; }
.ds-logo__label :deep(small) { font-size: 0.75rem; opacity: 0.7; }
.ds-logo__label--strong { font-weight: 700; }
</style>
