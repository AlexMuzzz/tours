import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { adminTourService } from '@/services/adminTourService';
import { createTour as createTourFixture } from '@/test/fixtures';

const { fetchMock, authServiceMock } = vi.hoisted(() => ({
  fetchMock: vi.fn(),
  authServiceMock: {
    getToken: vi.fn(),
  },
}));

vi.mock('@/services/authService', () => ({
  authService: authServiceMock,
}));

describe('adminTourService', () => {
  beforeEach(() => {
    fetchMock.mockReset();
    authServiceMock.getToken.mockReset();
    authServiceMock.getToken.mockReturnValue('test-token');
    vi.stubGlobal('fetch', fetchMock);
  });

  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('sends multipart form data when creating a tour with an uploaded cover', async () => {
    const file = new File(['cover'], 'cover.png', { type: 'image/png' });

    fetchMock.mockResolvedValue(new Response(JSON.stringify({
      data: createTourFixture({ id: 42, title: 'Upload Cover Tour' }),
    }), {
      status: 201,
      headers: {
        'Content-Type': 'application/json',
      },
    }));

    await adminTourService.createTour({
      title: 'Upload Cover Tour',
      short_description: 'Короткое описание',
      description: 'Полное описание',
      duration_days: 5,
      category: 'nature',
      is_active: true,
      main_image: '',
      main_image_file: file,
    });

    const [, options] = fetchMock.mock.calls[0] as [string, RequestInit];
    const headers = options.headers as Headers;
    const body = options.body as FormData;

    expect(options.method).toBe('POST');
    expect(headers.get('Authorization')).toBe('Bearer test-token');
    expect(headers.get('Content-Type')).toBeNull();
    expect(body).toBeInstanceOf(FormData);
    expect(body.get('title')).toBe('Upload Cover Tour');
    expect(body.get('main_image_file')).toBe(file);
  });

  it('uses method spoofing when updating a tour with an uploaded cover', async () => {
    const file = new File(['cover'], 'new-cover.png', { type: 'image/png' });

    fetchMock.mockResolvedValue(new Response(JSON.stringify({
      data: createTourFixture({ id: 17, title: 'Updated Tour' }),
    }), {
      status: 200,
      headers: {
        'Content-Type': 'application/json',
      },
    }));

    await adminTourService.updateTour(17, {
      title: 'Updated Tour',
      main_image_file: file,
      remove_main_image: false,
    });

    const [url, options] = fetchMock.mock.calls[0] as [string, RequestInit];
    const body = options.body as FormData;

    expect(url).toContain('/admin/tours/17');
    expect(options.method).toBe('POST');
    expect(body.get('_method')).toBe('PUT');
    expect(body.get('title')).toBe('Updated Tour');
    expect(body.get('main_image_file')).toBe(file);
  });

  it('sends multipart form data when adding a gallery image', async () => {
    const file = new File(['gallery'], 'gallery.png', { type: 'image/png' });

    fetchMock.mockResolvedValue(new Response(JSON.stringify({
      data: {
        id: 1,
        image_url: 'http://localhost:8000/storage/tours/17/gallery/gallery.png',
        alt_text: 'Gallery image',
        sort_order: 3,
        created_at: null,
        updated_at: null,
      },
    }), {
      status: 201,
      headers: {
        'Content-Type': 'application/json',
      },
    }));

    await adminTourService.addImage(17, {
      image_file: file,
      alt_text: 'Gallery image',
      sort_order: 3,
    });

    const [, options] = fetchMock.mock.calls[0] as [string, RequestInit];
    const body = options.body as FormData;

    expect(options.method).toBe('POST');
    expect(body.get('image_file')).toBe(file);
    expect(body.get('alt_text')).toBe('Gallery image');
    expect(body.get('sort_order')).toBe('3');
  });
});
