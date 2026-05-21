import { vi } from 'vitest';

const { getTokenMock, redirectMock } = vi.hoisted(() => ({
  getTokenMock: vi.fn(),
  redirectMock: vi.fn((url: string) => ({ redirectTo: url })),
}));

vi.mock('@/services/authService', () => ({
  authService: {
    getToken: getTokenMock,
  },
}));

vi.mock('vike/abort', () => ({
  redirect: redirectMock,
}));

describe('admin guard', () => {
  beforeEach(() => {
    getTokenMock.mockReset();
    redirectMock.mockClear();
  });

  it('redirects unauthenticated users to admin login', async () => {
    getTokenMock.mockReturnValue(null);

    const { guard } = await import('../../../pages/admin/+guard.client');

    await expect(guard({ urlPathname: '/admin/tours' })).rejects.toEqual({
      redirectTo: '/admin/login',
    });
  });

  it('redirects authenticated users away from login page', async () => {
    getTokenMock.mockReturnValue('token-123');

    const { guard } = await import('../../../pages/admin/+guard.client');

    await expect(guard({ urlPathname: '/admin/login' })).rejects.toEqual({
      redirectTo: '/admin/tours',
    });
  });
});
