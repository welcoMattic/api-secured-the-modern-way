const apiBaseUrl = import.meta.env.VITE_API_BASE_URL

export interface ApiResult {
  status: number
  body: string
  /** Sur un 401, l'API n'a pas de corps : elle explique le refus dans cet en-tête. */
  challenge: string | null
}

export async function callApi(method: 'GET' | 'POST', token: string): Promise<ApiResult> {
  const url = `${apiBaseUrl}/api/photos`
  const headers: Record<string, string> = { Authorization: `Bearer ${token}` }

  if (method === 'POST') {
    headers['Content-Type'] = 'application/ld+json'
  }

  let response: Response
  try {
    response = await fetch(url, {
      method,
      headers,
      body: method === 'POST' ? JSON.stringify({ title: 'Photo déposée par PhotoPrint', url: 'https://cloudpics.example/photo.jpg' }) : undefined,
    })
  } catch {
    // API injoignable : fetch rejette, il n'y a pas de statut HTTP. Sans ce filet,
    // l'interface resterait figée sur "Appel en cours..." devant la salle.
    return { status: 0, body: `Aucune réponse de ${apiBaseUrl}. L'API est-elle démarrée ?`, challenge: null }
  }

  return {
    status: response.status,
    body: await response.text(),
    challenge: response.headers.get('www-authenticate'),
  }
}

// Décode le payload d'un JWT pour l'afficher. Aucune vérification ici : c'est l'API qui vérifie.
export function decodeJwt(token: string): unknown {
  const [, payload] = token.split('.')
  const base64 = payload.replace(/-/g, '+').replace(/_/g, '/').padEnd(payload.length + ((4 - (payload.length % 4)) % 4), '=')
  const bytes = Uint8Array.from(atob(base64), (c) => c.charCodeAt(0))
  return JSON.parse(new TextDecoder().decode(bytes))
}
