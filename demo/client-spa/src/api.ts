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

export function decodeJwt(token: string): unknown {
  const [, payload] = token.split('.')
  const base64Url = payload.replace(/-/g, '+').replace(/_/g, '/')
  return JSON.parse(atob(base64Url))
}
