export const canUseDOM = typeof window !== 'undefined' && typeof document !== 'undefined';

export function getLocalStorageItem(key: string): string | null {
  if (!canUseDOM) {
    return null;
  }

  return window.localStorage.getItem(key);
}

export function setLocalStorageItem(key: string, value: string): void {
  if (!canUseDOM) {
    return;
  }

  window.localStorage.setItem(key, value);
}

export function removeLocalStorageItem(key: string): void {
  if (!canUseDOM) {
    return;
  }

  window.localStorage.removeItem(key);
}
