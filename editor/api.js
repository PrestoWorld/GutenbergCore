/**
 * Data layer — talk to /api/admin/context/* (ContextApiController).
 * csrf token comes from <meta name="csrf-token">.
 */

const bootstrap = window.__SITE_EDITOR_STATE__ || {};

export const apiBase = bootstrap.apiBase || '/api/admin/context';

function csrf() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : bootstrap.nonce || '';
}

async function request(path, options = {}) {
  const res = await fetch(apiBase + path, {
    credentials: 'same-origin',
    headers: {
      'X-CSRF-Token': csrf(),
      ...(options.body ? { 'Content-Type': 'application/json' } : {}),
      ...(options.headers || {}),
    },
    ...options,
  });

  let data = null;
  const text = await res.text();
  try {
    data = text ? JSON.parse(text) : null;
  } catch {
    data = null;
  }

  if (!res.ok) {
    const message = data && data.error ? data.error : `HTTP ${res.status}`;
    throw new Error(message);
  }

  return data;
}

export function fetchLayout(contextType) {
  return request(`/layout/${encodeURIComponent(contextType)}`);
}

export function saveLayout(contextType, layout) {
  return request(`/layout/${encodeURIComponent(contextType)}`, {
    method: 'POST',
    body: JSON.stringify({ layout }),
  });
}

export function fetchBlocks() {
  return request('/blocks');
}

export function fetchTypes() {
  return request('/types');
}

export function renderPreview(contextType, data = {}) {
  return request(`/render/${encodeURIComponent(contextType)}`, {
    method: 'POST',
    body: JSON.stringify({ data }),
  });
}
