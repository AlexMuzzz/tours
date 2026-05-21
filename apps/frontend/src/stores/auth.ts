import { defineStore } from 'pinia';
import { authService } from '@/services/authService';
import { getErrorMessage } from '@/utils/errors';
import type { User } from '@/types/api';

interface AuthState {
  token: string | null;
  user: User | null;
  initialized: boolean;
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    token: null,
    user: null,
    initialized: false,
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.token),
  },
  actions: {
    async login(email: string, password: string) {
      const response = await authService.login(email, password);

      this.token = response.token;
      this.user = response.user;
      this.initialized = true;

      authService.setToken(response.token);
      authService.setStoredUser(response.user);

      return response;
    },

    async logout() {
      try {
        if (this.token) {
          await authService.logout(this.token);
        }
      } finally {
        this.clearSession();
      }
    },

    async loadFromStorage() {
      if (this.initialized) {
        return;
      }

      this.token = authService.getToken();
      this.user = authService.getStoredUser();
      this.initialized = true;

      if (this.token && !this.user) {
        await this.refreshCurrentUser();
      }
    },

    async refreshCurrentUser() {
      if (!this.token) {
        this.clearSession();
        return null;
      }

      try {
        const user = await authService.me(this.token);
        this.user = user;
        authService.setStoredUser(user);

        return user;
      } catch (error) {
        this.clearSession();
        throw new Error(getErrorMessage(error, 'Сессия администратора истекла.'));
      }
    },

    clearSession() {
      this.token = null;
      this.user = null;
      this.initialized = true;
      authService.clearToken();
      authService.clearStoredUser();
    },
  },
});
