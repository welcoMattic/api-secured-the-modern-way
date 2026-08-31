import { UserManager, WebStorageStateStore } from 'oidc-client-ts'

export const userManager = new UserManager({
  authority: import.meta.env.VITE_OIDC_AUTHORITY,
  client_id: import.meta.env.VITE_OIDC_CLIENT_ID,
  redirect_uri: window.location.origin + '/',
  post_logout_redirect_uri: window.location.origin + '/',
  response_type: 'code',
  // photos:read et photos:write sont ce que PhotoPrint demande à Alice de lui
  // déléguer. CloudPics ID n'accorde chaque scope que si Alice détient le rôle
  // correspondant, et n'inscrit dans le token que ce qu'il a accordé.
  scope: 'openid profile email photos:read photos:write',
  userStore: new WebStorageStateStore({ store: window.localStorage }),
  automaticSilentRenew: false,
})
// PKCE avec S256 est activé par défaut par la bibliothèque
