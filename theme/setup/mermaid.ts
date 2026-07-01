import type { MermaidOptions } from '@slidev/types'
import { defineMermaidSetup } from '@slidev/types'

export default defineMermaidSetup(() => {
  // eslint-disable-next-line prefer-const
  let injection_return: MermaidOptions = {
    theme: 'base',
    themeVariables: {
      // General theme variables - light background friendly
      noteBkgColor: "#FEF3C7",
      noteTextColor: "#92400E",
      noteBorderColor: "#F59E0B",

      // Sequence diagram variables - light theme
      actorBkg: "#FFFFFF",
      actorBorder: "#5a55a5",
      actorTextColor: "#0E131F",
      actorLineColor: "#6B7280",
      signalColor: "#374151",
      signalTextColor: "#374151",
      sequenceNumberColor: "#0E131F",
      labelBoxBorderColor: "#5EADF2",
      activationBkgColor: "#EEF2FF",
      activationBorderColor: "#5a55a5",

      // Line colors
      lineColor: "#6B7280",
    },
    themeCSS: `
      polygon.labelBox,
      polygon.labelBox + text {
        display: none;
      }
      .actor {
        stroke-width: 2px;
      }
      .messageText {
        font-size: 12px;
        font-weight: 500;
      }
    `
  }

  return injection_return
})