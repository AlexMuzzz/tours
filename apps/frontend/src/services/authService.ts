import { apiClient } from '@/services/apiClient';
import { canUseDOM, getLocalStorageItem, removeLocalStorageItem, setLocalStorageItem } from '@/utils/browser';
import type { LoginResponse, User } from '@/types/api';

const TOKEN_KEY = 'tour-catalog-admin-token';
const USER_KEY = 'tour-catalog-admin-user';

interface ResourceResponse<T> {
  data: T;
}

export const authService = {
  async login(email: string, password: string) {
    return apiClient.post<LoginResponse>('/admin/login', { email, password });
  },

  async logout(token?: string | null) {
    return apiClient.post<{ message: string }>('/admin/logout', undefined, token ?? this.getToken());
  },

  async me(token?: string | null) {
    const response = await apiClient.get<ResourceResponse<User>>('/admin/me', undefined, token ?? this.getToken());

    return response.data;
  },

  getToken() {
    return getLocalStorageItem(TOKEN_KEY);
  },

  setToken(token: string) {
    setLocalStorageItem(TOKEN_KEY, token);
  },

  clearToken() {
    removeLocalStorageItem(TOKEN_KEY);
  },

  getStoredUser(): User | null {
    const raw = getLocalStorageItem(USER_KEY);

    if (!raw) {
      return null;
    }

    try {
      return JSON.parse(raw) as User;
    } catch {
      return null;
    }
  },

  setStoredUser(user: User) {
    if (!canUseDOM) {
      return;
    }

    setLocalStorageItem(USER_KEY, JSON.stringify(user));
  },

  clearStoredUser() {
    removeLocalStorageItem(USER_KEY);
  },
};
