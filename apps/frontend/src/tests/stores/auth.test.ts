import { createPinia, setActivePinia } from 'pinia';
import { vi } from 'vitest';
import { useAuthStore } from '@/stores/auth';
import { createUser } from '@/test/fixtures';

const { authServiceMock } = vi.hoisted(() => ({
  authServiceMock: {
    login: vi.fn(),
    logout: vi.fn(),
    me: vi.fn(),
    getToken: vi.fn(),
    setToken: vi.fn(),
    clearToken: vi.fn(),
    getStoredUser: vi.fn(),
    setStoredUser: vi.fn(),
    clearStoredUser: vi.fn(),
  },
}));

vi.mock('@/services/authService', () => ({
  authService: authServiceMock,
}));

describe('auth store', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    Object.values(authServiceMock).forEach((mock) => {
      if ('mockReset' in mock) {
        mock.mockReset();
      }
    });
  });

  it('stores token and user after login', async () => {
    const user = createUser({ email: 'editor@example.com' });

    authServiceMock.login.mockResolvedValue({
      token: 'token-123',
      user,
    });

    const store = useAuthStore();

    await store.login('editor@example.com', 'password');

    expect(store.token).toBe('token-123');
    expect(store.user).toEqual(user);
    expect(store.isAuthenticated).toBe(true);
    expect(authServiceMock.setToken).toHaveBeenCalledWith('token-123');
    expect(authServiceMock.setStoredUser).toHaveBeenCalledWith(user);
  });
});
