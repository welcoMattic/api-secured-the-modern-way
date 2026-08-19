// Polices du deck, auto-hébergées : la démo doit rester identique hors ligne,
// le wifi d'une salle de conférence n'est pas une dépendance acceptable.
import '@fontsource-variable/inter'
import '@fontsource-variable/sora'
import './style.css'
import type { User } from 'oidc-client-ts'
import { userManager } from './oidc'
import { callApi, decodeJwt } from './api'

const hero = document.getElementById('hero') as HTMLElement
const workbench = document.getElementById('workbench') as HTMLElement
const userPill = document.getElementById('user-pill') as HTMLElement
const idClaims = document.getElementById('id-claims') as HTMLElement
const idJson = document.getElementById('id-json') as HTMLElement
const accessClaims = document.getElementById('access-claims') as HTMLElement
const accessJson = document.getElementById('access-json') as HTMLElement
const accessTtl = document.getElementById('access-ttl') as HTMLElement
const responseBox = document.getElementById('response') as HTMLElement

// Les écouteurs sont posés UNE fois, sur des nœuds qui vivent dans index.html.
// Les rattacher à chaque rendu déclencherait deux redirections par clic.
document.getElementById('login-btn')!.addEventListener('click', () => {
  void userManager.signinRedirect()
})
document.getElementById('logout-btn')!.addEventListener('click', () => {
  void userManager.signoutRedirect()
})

let ttlTimer: number | undefined

// Retour du Provider : le code d'autorisation est dans l'URL.
if (window.location.search.includes('code=')) {
  userManager
    .signinRedirectCallback()
    .then(() => {
      history.replaceState({}, '', '/')
      return userManager.getUser()
    })
    .then(render)
    .catch((error) => {
      console.error(error)
      render(null)
    })
} else {
  void userManager.getUser().then(render)
}

function render(user: User | null): void {
  // Des const locales, pas des propriétés : TypeScript garde le narrowing dans les callbacks.
  const idToken = user?.id_token
  const accessToken = user?.access_token

  if (!user || !user.profile || user.expired || !idToken || !accessToken) {
    hero.hidden = false
    workbench.hidden = true
    window.clearInterval(ttlTimer)
    return
  }

  hero.hidden = true
  workbench.hidden = false
  userPill.textContent = user.profile.preferred_username ?? user.profile.sub

  const idPayload = decodeJwt(idToken) as Record<string, unknown>
  const accessPayload = decodeJwt(accessToken) as Record<string, unknown>

  // Les deux cartes montrent le même claim en premier : `aud`. C'est là que se lit
  // la différence entre un token pour le client et un token pour l'API.
  renderClaims(idClaims, [
    ['aud', idPayload.aud, true],
    ['sub', idPayload.sub, false],
  ])
  renderClaims(accessClaims, [
    ['aud', accessPayload.aud, true],
    ['azp', accessPayload.azp, false],
    ['realm_access.roles', (accessPayload.realm_access as Record<string, unknown> | undefined)?.roles, false],
  ])

  idJson.textContent = JSON.stringify(idPayload, null, 2)
  accessJson.textContent = JSON.stringify(accessPayload, null, 2)

  startTtlCountdown(Number(accessPayload.exp))
  renderIdle()

  for (const button of document.querySelectorAll<HTMLButtonElement>('.api-btn')) {
    button.onclick = async () => {
      const method = button.dataset.method as 'GET' | 'POST'
      const token = button.dataset.token === 'id' ? idToken : accessToken
      renderPending()
      const { status, body, challenge } = await callApi(method, token)
      renderResponse(status, body, challenge, button.dataset.token === 'id')
    }
  }
}

type ClaimRow = [label: string, value: unknown, emphasised: boolean]

function renderClaims(target: HTMLElement, rows: ClaimRow[]): void {
  target.replaceChildren()
  for (const [label, value, emphasised] of rows) {
    if (value === undefined || value === null) {
      continue
    }
    const dt = document.createElement('dt')
    dt.textContent = label
    const dd = document.createElement('dd')
    dd.textContent = Array.isArray(value) ? value.join(', ') : String(value)
    if (emphasised) {
      dd.classList.add('is-emphasised')
    }
    target.append(dt, dd)
  }
}

/**
 * Un access token est court. Sur scène, afficher le temps restant sert deux fois :
 * ça illustre le propos, et ça prévient l'orateur avant que la démo ne réponde 401.
 */
function startTtlCountdown(exp: number): void {
  window.clearInterval(ttlTimer)
  if (!Number.isFinite(exp)) {
    accessTtl.textContent = ''
    return
  }

  const tick = () => {
    const left = Math.max(0, exp - Math.floor(Date.now() / 1000))
    const minutes = String(Math.floor(left / 60)).padStart(2, '0')
    const seconds = String(left % 60).padStart(2, '0')
    accessTtl.textContent = left > 0 ? `expire dans ${minutes}:${seconds}` : 'expiré'
    accessTtl.classList.toggle('is-warning', left > 0 && left < 120)
    accessTtl.classList.toggle('is-expired', left === 0)
  }

  tick()
  ttlTimer = window.setInterval(tick, 1000)
}

function renderIdle(): void {
  responseBox.replaceChildren(element('p', 'response-idle', "Aucun appel pour l'instant."))
}

function renderPending(): void {
  responseBox.replaceChildren(element('p', 'response-idle', 'Appel en cours...'))
}

function renderResponse(status: number, body: string, challenge: string | null, usedIdToken: boolean): void {
  const ok = status >= 200 && status < 300
  const injoignable = 0 === status

  const chip = element('span', 'status-chip', injoignable ? '!' : String(status))
  chip.classList.add(ok ? 'is-ok' : 'is-ko')

  const header = document.createElement('div')
  header.className = 'response-head'
  const label = injoignable ? 'API injoignable' : ok ? 'OK' : "refusé par l'API"
  header.append(chip, element('span', 'status-label', label))

  const nodes: HTMLElement[] = [header]
  // Un 401 n'a pas de corps : tout est dans l'en-tête WWW-Authenticate.
  if (challenge) {
    nodes.push(element('p', 'response-challenge', `WWW-Authenticate: ${challenge}`))
  }
  // Un 401 sur l'access token, alors qu'on se croit connecté : le token n'est plus
  // vérifiable. Cas classique en démo, quand Keycloak a redémarré et régénéré ses clés.
  // On ne dit rien pour le bouton ID token : là, le 401 est justement la démonstration.
  if (401 === status && !usedIdToken) {
    const hint = element('p', 'response-hint', "Ce token n'est plus accepté : le Provider a sans doute redémarré. ")
    const reset = document.createElement('button')
    reset.type = 'button'
    reset.className = 'link-btn'
    reset.textContent = 'Oublier la session'
    // Surtout pas signoutRedirect() ici : elle enverrait un id_token_hint que le
    // Provider redémarré ne sait plus vérifier, et répondrait 400.
    reset.addEventListener('click', () => {
      void userManager.removeUser().then(() => render(null))
    })
    hint.append(reset, document.createTextNode(', puis se reconnecter.'))
    nodes.push(hint)
  }
  nodes.push(element('pre', 'response-body', readable(body)))
  responseBox.replaceChildren(...nodes)
}

/**
 * En dev, l'API joint une trace PHP de plusieurs kilooctets à ses erreurs. Projetée,
 * elle noie la seule ligne qui compte ("Access Denied."). On la retire à l'affichage,
 * exactement comme le ferait un vrai client.
 */
function readable(body: string): string {
  if (!body) {
    return '(corps vide)'
  }
  try {
    const parsed = JSON.parse(body) as Record<string, unknown>
    delete parsed.trace
    return JSON.stringify(parsed, null, 2)
  } catch {
    return body
  }
}

function element(tag: string, className: string, text: string): HTMLElement {
  const node = document.createElement(tag)
  node.className = className
  node.textContent = text
  return node
}
