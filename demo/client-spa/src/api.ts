const apiBaseUrl = import.meta.env.VITE_API_BASE_URL

export async function callApi(method: 'GET' | 'POST', token: string): Promise<{ status: number; body: string }> {
  const url = `${apiBaseUrl}/api/photos`
  const headers: Record<string, string> = { Authorization: `Bearer ${token}` }

  if (method === 'POST') {
    headers['Content-Type'] = 'application/ld+json'
  }

  const response = await fetch(url, {
    method,
    headers,
    body: method === 'POST' ? JSON.stringify({ title: 'Nouvelle photo', url: 'https://example.com/photo.jpg' }) : undefined,
  })

  return { status: response.status, body: await response.text() }
}

// Decode le payload d'un JWT pour l'afficher. Aucune verification : c'est l'API qui verifie.
export function decodeJwt(token: string): unknown {
  const [, payload] = token.split('.')
  const base64 = payload.replace(/-/g, '+').replace(/_/g, '/').padEnd(payload.length + ((4 - (payload.length % 4)) % 4), '=')
  const bytes = Uint8Array.from(atob(base64), (c) => c.charCodeAt(0))
  return JSON.parse(new TextDecoder().decode(bytes))
}
