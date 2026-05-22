<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import Button from 'primevue/button';
import ProgressSpinner from 'primevue/progressspinner';
import { navigate } from 'vike/client/router';
import { useToast } from 'primevue/usetoast';
import { useAuthStore } from '@/stores/auth';
import { getErrorMessage } from '@/utils/errors';

const props = defineProps<{
  title: string;
  subtitle?: string;
}>();

const authStore = useAuthStore();
const toast = useToast();
const isBooting = ref(true);
const currentPath = computed(() => (typeof window === 'undefined' ? '' : window.location.pathname));

const navItems = [
  { label: 'Туры', href: '/admin/tours' },
  { label: 'Создать тур', href: '/admin/tours/create' },
];

function isActive(href: string) {
  if (href === '/admin/tours/create') {
    return currentPath.value === href;
  }

  if (href === '/admin/tours') {
    return currentPath.value === href || /^\/admin\/tours\/\d+\/edit$/.test(currentPath.value);
  }

  return currentPath.value === href;
}

async function bootSession() {
  try {
    await authStore.loadFromStorage();

    if (authStore.token && !authStore.user) {
      await authStore.refreshCurrentUser();
    }
  } catch (error) {
    toast.add({
      severity: 'warn',
      summary: 'Сессия сброшена',
      detail: getErrorMessage(error, 'Нужно заново войти в админку.'),
      life: 3500,
    });
    await navigate('/admin/login');
  } finally {
    isBooting.value = false;
  }
}

async function handleLogout() {
  await authStore.logout();
  toast.add({
    severity: 'success',
    summary: 'Выход выполнен',
    detail: 'Текущая админская сессия завершена.',
    life: 2500,
  });
  await navigate('/admin/login');
}

onMounted(() => {
  void bootSession();
});
</script>

<template>
  <div class="page-shell bg-[linear-gradient(180deg,#f9f5eb_0%,#eef8f4_100%)]">
    <div class="content-wrapper py-6">
      <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
        <aside class="glass-panel rounded-[2rem] border-0 p-5">
          <div class="mb-8 flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,var(--travel-ocean),var(--travel-forest))] text-lg font-semibold text-white">
              TA
            </div>
            <div>
              <div class="text-xs uppercase tracking-[0.24em] text-[var(--travel-muted)]">
                Admin Console
              </div>
              <div class="text-lg font-semibold text-[var(--travel-ink)]">
                Tour Catalog AI
              </div>
            </div>
          </div>

          <div class="space-y-2">
            <a
              v-for="item in navItems"
              :key="item.href"
              :href="item.href"
              class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium transition"
              :class="
                isActive(item.href)
                  ? 'bg-[rgba(42,123,116,0.12)] text-[var(--travel-ocean)]'
                  : 'text-[var(--travel-muted)] hover:bg-white/70 hover:text-[var(--travel-ink)]'
              "
            >
              <span>{{ item.label }}</span>
              <i class="pi pi-angle-right text-xs" />
            </a>
          </div>

          <div class="mt-8 rounded-[1.5rem] border border-[var(--travel-line)] bg-white/65 p-4 text-sm">
            <div class="font-semibold text-[var(--travel-ink)]">
              {{ authStore.user?.name || 'Администратор' }}
            </div>
            <div class="mt-1 text-[var(--travel-muted)]">
              {{ authStore.user?.email || 'admin@example.com' }}
            </div>
          </div>

          <Button
            label="Выйти"
            icon="pi pi-sign-out"
            severity="secondary"
            class="mt-5 w-full"
            @click="handleLogout"
          />
        </aside>

        <section class="space-y-5">
          <div class="glass-panel rounded-[2rem] border-0 p-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
              <div>
                <div class="text-xs uppercase tracking-[0.24em] text-[var(--travel-muted)]">
                  Admin workspace
                </div>
                <h1 class="mt-2 text-4xl font-semibold text-[var(--travel-ink)]">
                  {{ props.title }}
                </h1>
                <p v-if="props.subtitle" class="mt-2 max-w-3xl text-sm leading-6 text-[var(--travel-muted)]">
                  {{ props.subtitle }}
                </p>
              </div>

              <div v-if="isBooting" class="flex items-center gap-3 text-sm text-[var(--travel-muted)]">
                <ProgressSpinner style="width: 1.75rem; height: 1.75rem" strokeWidth="5" />
                Восстанавливаем сессию...
              </div>
            </div>
          </div>

          <div v-if="isBooting" class="glass-panel rounded-[2rem] border-0 px-6 py-14 text-center">
            <ProgressSpinner style="width: 2.5rem; height: 2.5rem" strokeWidth="4" />
            <div class="mt-4 text-sm text-[var(--travel-muted)]">
              Проверяем admin session и готовим данные.
            </div>
          </div>

          <slot v-else />
        </section>
      </div>
    </div>
  </div>
</template>
