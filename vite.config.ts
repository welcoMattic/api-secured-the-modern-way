import { defineConfig } from 'vite'

export default defineConfig({
  server: {
    fs: {
      // TODO: remove once https://github.com/slidevjs/slidev/issues/2616 is fixed.
      // Slidev's `slidev:slide-import-guard` (Vite 8) wrongly rejects public/ assets
      // referenced with absolute paths (e.g. <img src="/apisix.svg">) in slides.
      // Setting strict to false disables that guard for the local dev server only;
      // `slidev build` / `slidev export` are unaffected.
      strict: false,
    },
  },
})
