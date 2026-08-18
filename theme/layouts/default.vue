<script setup lang="ts">
import { computed } from "vue";
import { handleBackground } from "../utils/layoutHelper";
const props = defineProps({
  background: {
    default: "",
  },
});

const style = computed(() => handleBackground(props.background, true));
</script>

<template>
  <div class="slidev-layout default" :style="style">
    <div class="h-full flex flex-col justify-center">
      <slot />
    </div>
  </div>
</template>
