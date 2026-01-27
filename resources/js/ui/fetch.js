function getCsrfToken() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

export async function fetchJson(url, options = {}) {
  const headers = {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
    ...options.headers,
  };

  const method = (options.method || 'GET').toUpperCase();
  if (!['GET', 'HEAD'].includes(method)) {
    headers['X-CSRF-TOKEN'] = getCsrfToken();
    if (!headers['Content-Type'] && !(options.body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
    }
  }

  const res = await fetch(url, { ...options, headers });

  // 204
  if (res.status === 204) return { ok: true, data: null };

  let payload = null;
  const text = await res.text();
  try {
    payload = text ? JSON.parse(text) : null;
  } catch (_) {
    payload = { ok: false, message: text || 'Non-JSON response' };
  }

  if (res.ok) return payload ?? { ok: true };

  // Normalize common errors
  if (res.status === 422) {
    return payload ?? { ok: false, message: 'Validation failed', errors: {} };
  }
  if (res.status === 419) {
    return { ok: false, message: 'Session expired. Refresh and try again.' };
  }
  return payload ?? { ok: false, message: 'Request failed' };
}