import type { ApiError } from '@/types/api';

export function getErrorMessage(error: unknown, fallback = 'Что-то пошло не так.'): string {
  if (typeof error === 'object' && error !== null && 'message' in error) {
    const message = (error as ApiError).message;

    if (typeof message === 'string' && message.trim() !== '') {
      return message;
    }
  }

  return fallback;
}

export function getFieldError(
  errors: Record<string, string[]> | undefined,
  field: string,
): string | null {
  const value = errors?.[field]?.[0];

  return value ?? null;
}
