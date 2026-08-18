<script setup lang="ts">
import { computed } from 'vue'
import { handleBackground } from '../utils/layoutHelper'
const props = defineProps({
  background: {
    default: '',
  },
})
const style = computed(() => handleBackground(props.background, true))
</script>

<template>
  <div class="slidev-layout closing" :style="style">
    <div class="closing-inner">
      <slot />
    </div>
  </div>
</template>
