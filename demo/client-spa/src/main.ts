import './style.css'
import type { User } from 'oidc-client-ts'
import { userManager } from './oidc'
import { callApi, decodeJwt } from './api'

// Conteneurs du DOM
const authContainer = document.getElementById('auth')!
const userContainer = document.getElementById('user')!
const actionsContainer = document.getElementById('actions')!
const idTokenContainer = document.getElementById('id-token')!
const accessTokenContainer = document.getElementById('access-token')!
const responseContainer = document.getElementById('response')!

// Gestion du flow de redirection OIDC
if (window.location.search.includes('code=')) {
  userManager.signinRedirectCallback().then(() => {
    history.replaceState({}, '', '/')
    return userManager.getUser()
  }).then(user => renderApp(user)).catch(console.error)
} else {
  userManager.getUser().then(user => renderApp(user))
}

function renderApp(user: User | null): void {
  // Des const locales, pas des proprietes : TypeScript garde le narrowing dans les callbacks
  const idToken = user?.id_token
  const accessToken = user?.access_token

  if (!user || !user.profile || user.expired || !idToken || !accessToken) {
    // État déconnecté
    authContainer.innerHTML = '<button id="login-btn">Se connecter avec Keycloak</button>'
    userContainer.innerHTML = ''
    actionsContainer.innerHTML = ''
    idTokenContainer.innerHTML = ''
    accessTokenContainer.innerHTML = ''
    responseContainer.innerHTML = ''

    document.getElementById('login-btn')!.addEventListener('click', () => userManager.signinRedirect())
    return
  }

  // État connecté
  authContainer.innerHTML = '<button id="logout-btn">Se deconnecter</button>'
  userContainer.innerHTML = `<p>Connecté en tant que <strong>${user.profile.preferred_username}</strong></p>`

  document.getElementById('logout-btn')!.addEventListener('click', () => userManager.signoutRedirect())

  // Tokens décodés : deux destinataires, deux contenus
  const idClaims = decodeJwt(idToken)
  const accessClaims = decodeJwt(accessToken)

  idTokenContainer.innerHTML = `<h3>ID token -&gt; l&apos;application cliente</h3><pre>${JSON.stringify(idClaims, null, 2)}</pre>`
  accessTokenContainer.innerHTML = `<h3>Access token -&gt; l&apos;API</h3><p><small>Note : l&apos;<code>aud</code> doit contenir <code>api-photos</code> et <code>realm_access.roles</code> porte les rôles.</small></p><pre>${JSON.stringify(accessClaims, null, 2)}</pre>`

  // Boutons d'action
  actionsContainer.innerHTML = `
    <div class="action-buttons">
      <button class="api-btn" data-method="GET" data-token="access">GET /api/photos</button>
      <button class="api-btn" data-method="POST" data-token="access">POST /api/photos</button>
      <button class="api-btn" data-method="GET" data-token="id">GET /api/photos avec l&apos;ID token (doit échouer)</button>
      <button id="clear-btn">Effacer la réponse</button>
    </div>
  `

  document.querySelectorAll('.api-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const method = (btn as HTMLElement).dataset.method as 'GET' | 'POST'
      const tokenType = (btn as HTMLElement).dataset.token!
      const token = tokenType === 'id' ? idToken : accessToken

      const result = await callApi(method, token)
      showResponse(result.status, result.body)
    })
  })

  document.getElementById('clear-btn')!.addEventListener('click', () => {
    responseContainer.innerHTML = ''
  })
}

function showResponse(status: number, body: string): void {
  const ok = status >= 200 && status < 300
  responseContainer.innerHTML = `
    <div class="response-header" style="color: ${ok ? 'var(--success)' : 'var(--error)'}">HTTP ${status}</div>
    <pre class="response-body"></pre>
  `
  // textContent et pas innerHTML : le corps vient de l'API, on l'affiche tel quel
  responseContainer.querySelector('.response-body')!.textContent = body || '(vide)'
}
