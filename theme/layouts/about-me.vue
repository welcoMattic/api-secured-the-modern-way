<!-- Based on https://github.com/estruyf/slidev-theme-the-unnamed/blob/main/layouts/about-me.vue -->
<script setup lang="ts">
import { computed } from 'vue'
import { handleBackground } from '@slidev/client/layoutHelper.ts'
import { resolveAssetUrl } from '../utils/layoutHelper'

const props = defineProps<{
  imageSrc?: string
  helloMsg?: string
  name?: string
  job?: string
  line1?: string
  line2?: string
  email?: string
  social1?: string
  social2?: string
  social3?: string
  position?: string
}>()

const RE_ROOT_SRC = /(\ssrc=")(\/(?!\/)[^"]*)"/g

// These props are raw HTML from the slide frontmatter and go straight to v-html, so
// no build step ever sees their `<img src="/foo.png">`. Rebase them by hand or they
// 404 once the deck is served under a base path (GitHub Pages: /<repo>/).
function rebaseHtml(html?: string) {
  return html?.replace(RE_ROOT_SRC, (_, attr, url) => `${attr}${resolveAssetUrl(url)}"`)
}

const style = computed(() => handleBackground(props.imageSrc, false))
const flexRow = computed(() => props.position === 'left' ? 'flex-row-reverse' : 'flex-row')
const textItems = computed(() => props.position === 'left' ? 'items-start' : 'items-end')
const textAlign = computed(() => props.position === 'left' ? 'text-left' : 'text-right')

const jobHtml = computed(() => rebaseHtml(props.job))
const line1Html = computed(() => rebaseHtml(props.line1))
const line2Html = computed(() => rebaseHtml(props.line2))
const social1Html = computed(() => rebaseHtml(props.social1))
const social2Html = computed(() => rebaseHtml(props.social2))
const social3Html = computed(() => rebaseHtml(props.social3))
</script>

<template>
  <div class="slidev-layout about-me p-0">
    <div class="flex h-full" :class="flexRow">
      <div class="w-1/2 h-full flex flex-col justify-end" :style="style">
      </div>
      <div class="w-1/2 flex flex-col justify-between px-8 py-16" :class="textItems">
        <h1 v-if="helloMsg" class="flex">{{ helloMsg }}</h1>

        <div class="flex flex-col flex flex-col justify-end pt-4 pb-16" :class="[textItems, textAlign]">
          <h2 class="font-extrabold">{{ name }}</h2>

          <div class="text-2xl space-y-2 mt-4">
            <p class="job" v-html="jobHtml"></p>
            <p class="line-1 text-xl" v-html="line1Html"></p>
            <p class="line-2 text-xl" v-html="line2Html"></p>
          </div>

          <div class="text-xl space-y-2 mt-4">
            <p class="social social-1" v-html="social1Html"></p>
            <p class="social social-2" v-html="social2Html"></p>
            <p class="social social-3" v-html="social3Html"></p>
          </div>
        </div>
      </div>
    </div>

    <slot />
  </div>
</template>
