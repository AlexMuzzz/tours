<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { navigate } from 'vike/client/router';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import { useToast } from 'primevue/usetoast';
import { useAuthStore } from '@/stores/auth';
import { getErrorMessage } from '@/utils/errors';

const authStore = useAuthStore();
const toast = useToast();

const form = reactive({
  email: 'admin@example.com',
  password: 'password',
});

const loading = ref(false);
const errorMessage = ref('');

async function handleLogin() {
  errorMessage.value = '';
  loading.value = true;

  try {
    await authStore.login(form.email, form.password);
    toast.add({
      severity: 'success',
      summary: 'Вход выполнен',
      detail: 'Bearer token сохранён в localStorage.',
      life: 2500,
    });
    await navigate('/admin/tours');
  } catch (error) {
    errorMessage.value = getErrorMessage(error, 'Не удалось войти в админку.');
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  await authStore.loadFromStorage();

  if (authStore.token) {
    await navigate('/admin/tours');
  }
});
</script>

<template>
  <div class="page-shell flex items-center justify-center bg-[linear-gradient(180deg,#fbf7ef_0%,#eef8f4_100%)] px-4 py-10">
    <div class="w-full max-w-5xl grid gap-8 lg:grid-cols-[0.95fr_1.05fr]">
      <div class="glass-panel rounded-[2.6rem] border-0 p-8 md:p-10">
        <div class="space-y-6">
          <div class="inline-flex items-center gap-3 rounded-full bg-white/75 px-4 py-2 text-xs uppercase tracking-[0.24em] text-[var(--travel-ocean)]">
            <span class="travel-dot" />
            Admin access
          </div>
          <div class="space-y-4">
            <h1 class="text-5xl font-semibold leading-tight text-[var(--travel-ink)]">
              Управляйте каталогом туров из одной admin-панели.
            </h1>
            <p class="text-base leading-8 text-[var(--travel-muted)]">
              Логин использует существующий Laravel Sanctum Bearer token flow. После входа можно
              редактировать туры, даты, изображения, route points и генерировать stub-описание.
            </p>
          </div>
          <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-[1.5rem] border border-white/70 bg-white/70 p-4 text-sm text-[var(--travel-muted)]">
              CRUD туров
            </div>
            <div class="rounded-[1.5rem] border border-white/70 bg-white/70 p-4 text-sm text-[var(--travel-muted)]">
              Даты и цены
            </div>
            <div class="rounded-[1.5rem] border border-white/70 bg-white/70 p-4 text-sm text-[var(--travel-muted)]">
              Route points и map-ready данные
            </div>
          </div>
        </div>
      </div>

      <Card class="glass-panel rounded-[2.6rem] border-0">
        <template #content>
          <form class="space-y-5 p-2" @submit.prevent="handleLogin">
            <div>
              <div class="text-xs uppercase tracking-[0.24em] text-[var(--travel-muted)]">Tour Catalog AI</div>
              <h2 class="mt-2 text-4xl font-semibold text-[var(--travel-ink)]">
                Вход в админку
              </h2>
              <p class="mt-3 text-sm leading-6 text-[var(--travel-muted)]">
                Для демо уже доступен администратор <strong>admin@example.com</strong> с паролем <strong>password</strong>.
              </p>
            </div>

            <label class="block space-y-2">
              <span class="text-sm font-medium text-[var(--travel-ink)]">Email</span>
              <InputText v-model="form.email" fluid autocomplete="username" />
            </label>

            <label class="block space-y-2">
              <span class="text-sm font-medium text-[var(--travel-ink)]">Password</span>
              <InputText v-model="form.password" type="password" fluid autocomplete="current-password" />
            </label>

            <p v-if="errorMessage" class="rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-600">
              {{ errorMessage }}
            </p>

            <Button
              type="submit"
              label="Войти"
              icon="pi pi-sign-in"
              class="w-full"
              :loading="loading"
            />

            <a href="/tours" class="travel-link travel-link-secondary w-full justify-center">
              Вернуться в каталог
            </a>
          </form>
        </template>
      </Card>
    </div>
  </div>
</template>
