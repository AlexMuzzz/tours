import { createPinia, setActivePinia } from 'pinia';
import { flushPromises, mount } from '@vue/test-utils';
import { vi } from 'vitest';
import AdminLoginPage from '@/pages/admin/AdminLoginPage.vue';
import { baseGlobalStubs } from '@/test/stubs';
import { createUser } from '@/test/fixtures';

const { navigateMock, toastAddMock, authServiceMock } = vi.hoisted(() => ({
  navigateMock: vi.fn(),
  toastAddMock: vi.fn(),
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

vi.mock('vike/client/router', () => ({
  navigate: navigateMock,
}));

vi.mock('primevue/usetoast', () => ({
  useToast: () => ({
    add: toastAddMock,
  }),
}));

vi.mock('@/services/authService', () => ({
  authService: authServiceMock,
}));

describe('AdminLoginPage', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    navigateMock.mockReset();
    toastAddMock.mockReset();
    Object.values(authServiceMock).forEach((mock) => {
      if ('mockReset' in mock) {
        mock.mockReset();
      }
    });

    authServiceMock.getToken.mockReturnValue(null);
    authServiceMock.getStoredUser.mockReturnValue(null);
    authServiceMock.login.mockResolvedValue({
      token: 'test-token',
      user: createUser(),
    });
  });

  it('calls authService.login on form submit', async () => {
    const wrapper = mount(AdminLoginPage, {
      global: {
        plugins: [createPinia()],
        stubs: baseGlobalStubs,
      },
    });

    await flushPromises();
    await wrapper.find('form').trigger('submit.prevent');
    await flushPromises();

    expect(authServiceMock.login).toHaveBeenCalledWith('admin@example.com', 'password');
    expect(navigateMock).toHaveBeenCalledWith('/admin/tours');
  });
});
