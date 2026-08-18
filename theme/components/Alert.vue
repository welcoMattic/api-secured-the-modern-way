<script setup lang="ts">
const props = withDefaults(defineProps<{
  type?: 'info' | 'warning' | 'error',
  class?: string,
}>(), {
  type: 'info',
  class: 'alert',
});
</script>

<template>
  <div
      class="alert"
      :class="`${props.class} alert--${type}`"
  >
    <div class="alert__icon">
      <svg
          v-if="type === 'info'"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          width="24"
          height="24"
          fill="currentColor"
      >
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15h2v-6h-2v6zm1-8h2V7h-2v2z"/>
      </svg>

      <svg
          v-else-if="type === 'warning'"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          width="24"
          height="24"
          fill="currentColor"
      >
        <path d="M12 2L1 21h22L12 2zm0 3.5L19.5 19H4.5L12 5.5zM11 10v4h2v-4h-2zm0 6v2h2v-2h-2z"/>
      </svg>

      <svg
          v-else-if="type === 'error'"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          width="24"
          height="24"
          fill="currentColor"
      >
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
      </svg>
    </div>
    <div class="alert__content">
      <slot />
    </div>
  </div>
</template>

<style scoped>
.alert {
  position: relative;
  display: flex;
  align-items: flex-start;
  padding: 16px;
  border-radius: 8px;
  font-size: 0.9375rem;
  line-height: 1.4;
  margin: 1rem 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
}

.alert:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.alert__icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  margin-right: 12px;
  flex-shrink: 0;
}

.alert__content {
  flex: 1;
  min-width: 0;
}

.alert__content :deep(p) {
  margin: 0;
  padding: 0;
}

/* Info Alert */
.alert--info {
  background-color: #e8f2fc;
  border-left: 4px solid #2b8ad9;
  color: #0b3a63;
}

.alert--info .alert__icon {
  color: #2b8ad9;
}

/* Warning Alert */
.alert--warning {
  background-color: #fbf3da;
  border-left: 4px solid #c98a00;
  color: #684900;
}

.alert--warning .alert__icon {
  color: #c98a00;
}

/* Error Alert */
.alert--error {
  background-color: #fce9e8;
  border-left: 4px solid #d83533;
  color: #7a1e1c;
}

.alert--error .alert__icon {
  color: #d83533;
}
</style>
