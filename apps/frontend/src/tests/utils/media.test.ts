import { describe, expect, it, vi } from 'vitest';
import { resolveMediaUrl } from '@/utils/media';

describe('resolveMediaUrl', () => {
  it('builds an absolute URL for relative storage paths', () => {
    vi.stubEnv('VITE_API_BASE_URL', 'http://localhost:8000/api');

    expect(resolveMediaUrl('/storage/tours/17/main.jpg'))
      .toBe('http://localhost:8000/storage/tours/17/main.jpg');
    expect(resolveMediaUrl('storage/tours/17/main.jpg'))
      .toBe('http://localhost:8000/storage/tours/17/main.jpg');
  });

  it('keeps absolute media URLs unchanged', () => {
    expect(resolveMediaUrl('https://cdn.example.com/tours/17/main.jpg'))
      .toBe('https://cdn.example.com/tours/17/main.jpg');
  });

  it('returns an empty string for empty values', () => {
    expect(resolveMediaUrl('')).toBe('');
    expect(resolveMediaUrl(null)).toBe('');
    expect(resolveMediaUrl(undefined)).toBe('');
  });
});
