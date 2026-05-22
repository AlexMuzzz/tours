const FALLBACK_API_BASE_URL = 'http://localhost:8000/api';

function getApiBaseUrl() {
  return import.meta.env.VITE_API_BASE_URL ?? FALLBACK_API_BASE_URL;
}

function getBackendOrigin() {
  try {
    return new URL(getApiBaseUrl()).origin;
  } catch {
    if (typeof window !== 'undefined') {
      return window.location.origin;
    }

    return 'http://localhost:8000';
  }
}

export function resolveMediaUrl(value?: string | null): string {
  const trimmed = value?.trim() ?? '';

  if (trimmed === '') {
    return '';
  }

  try {
    return new URL(trimmed).toString();
  } catch {
    return new URL(trimmed, `${getBackendOrigin()}/`).toString();
  }
}
