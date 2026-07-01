<script setup>
  import ProgressBar from "./components/ProgressBar.vue";
</script>
<template>
  <footer
      class="absolute flex bottom-0 left-0 right-0 py-2 px-4 z-1 items-center"
      :class="($slidev.nav.currentPage !== 1 && $slidev.nav.currentLayout !== 'about-me') ? 'justify-between' : 'justify-end'"
  >
    <div
        class="font-size-4 font-semibold"
        v-if="$slidev.nav.currentPage !== 1 && $slidev.nav.currentLayout !== 'about-me'"
    >
      {{ $slidev.nav.currentPage }}
    </div>
    <div class="flex items-center">
      <img src="/apipcon.svg" alt="API Platform Con 2026" class="h-8 opacity-80" />
    </div>
  </footer>
  <ProgressBar
      level="0"
      position="bottom"
      height="1"
      thickness="6px"
      class="z-1"
      v-if="$slidev.nav.currentPage !== 1 && $slidev.nav.currentLayout !== 'about-me'"
  />
</template>

