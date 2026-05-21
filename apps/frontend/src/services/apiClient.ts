import { buildQueryString } from '@/utils/query';
import type { ApiError } from '@/types/api';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api';

type HttpMethod = 'GET' | 'POST' | 'PUT' | 'DELETE';

interface RequestOptions {
  method?: HttpMethod;
  query?: Record<string, unknown>;
  body?: unknown;
  token?: string | null;
}

export class ApiClientError extends Error implements ApiError {
  status: number;
  errors?: Record<string, string[]>;

  constructor(payload: ApiError) {
    super(payload.message);
    this.name = 'ApiClientError';
    this.status = payload.status;
    this.errors = payload.errors;
  }
}

async function request<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const url = new URL(path.startsWith('/') ? path.slice(1) : path, `${API_BASE_URL}/`);
  const query = options.query ? buildQueryString(options.query) : '';

  if (query) {
    url.search = query;
  }

  const headers = new Headers({
    Accept: 'application/json',
  });

  if (options.body !== undefined) {
    headers.set('Content-Type', 'application/json');
  }

  if (options.token) {
    headers.set('Authorization', `Bearer ${options.token}`);
  }

  const response = await fetch(url.toString(), {
    method: options.method ?? 'GET',
    headers,
    body: options.body !== undefined ? JSON.stringify(options.body) : undefined,
  });

  const contentType = response.headers.get('content-type') ?? '';
  const payload = contentType.includes('application/json') ? await response.json() : null;

  if (!response.ok) {
    throw new ApiClientError({
      status: response.status,
      message:
        payload?.message ??
        `Запрос завершился с ошибкой ${response.status}.`,
      errors: payload?.errors,
    });
  }

  if (response.status === 204) {
    return undefined as T;
  }

  return payload as T;
}

export const apiClient = {
  get<T>(path: string, query?: Record<string, unknown>, token?: string | null) {
    return request<T>(path, { method: 'GET', query, token });
  },
  post<T>(path: string, body?: unknown, token?: string | null) {
    return request<T>(path, { method: 'POST', body, token });
  },
  put<T>(path: string, body?: unknown, token?: string | null) {
    return request<T>(path, { method: 'PUT', body, token });
  },
  delete<T>(path: string, token?: string | null) {
    return request<T>(path, { method: 'DELETE', token });
  },
};
