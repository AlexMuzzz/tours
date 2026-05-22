<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { navigate } from 'vike/client/router';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Skeleton from 'primevue/skeleton';
import type { CatalogFilters, PaginatedResponse, Tour, TourCategory } from '@/types/api';
import PublicLayout from '@/layouts/PublicLayout.vue';
import TourCard from '@/components/TourCard.vue';
import StateBlock from '@/components/StateBlock.vue';
import { buildQueryString } from '@/utils/query';
import { categoryOptions, sortOptions } from '@/utils/tourMeta';

const props = defineProps<{
  initialTours: PaginatedResponse<Tour> | null;
  initialError: string | null;
  initialFilters: Required<
    Pick<CatalogFilters, 'sort' | 'page' | 'per_page'>
  > &
    Omit<CatalogFilters, 'sort' | 'page' | 'per_page'>;
}>();

const filters = reactive({
  category: props.initialFilters.category ?? '',
  duration_min: props.initialFilters.duration_min ? String(props.initialFilters.duration_min) : '',
  duration_max: props.initialFilters.duration_max ? String(props.initialFilters.duration_max) : '',
  price_min: props.initialFilters.price_min ? String(props.initialFilters.price_min) : '',
  price_max: props.initialFilters.price_max ? String(props.initialFilters.price_max) : '',
  search: props.initialFilters.search ?? '',
  sort: props.initialFilters.sort ?? 'newest',
  per_page: String(props.initialFilters.per_page ?? 12),
});

const isNavigating = ref(false);

const pageNumbers = computed(() => {
  const current = props.initialTours?.meta.current_page ?? 1;
  const last = props.initialTours?.meta.last_page ?? 1;
  const pages = new Set<number>([1, last, current - 1, current, current + 1]);

  return [...pages].filter((page) => page >= 1 && page <= last).sort((left, right) => left - right);
});

function buildCatalogQuery(page = 1) {
  return buildQueryString({
    category: filters.category || undefined,
    duration_min: filters.duration_min || undefined,
    duration_max: filters.duration_max || undefined,
    price_min: filters.price_min || undefined,
    price_max: filters.price_max || undefined,
    search: filters.search.trim() || undefined,
    sort: filters.sort,
    page,
    per_page: filters.per_page || 12,
  });
}

function getCatalogPath() {
  if (typeof window === 'undefined') {
    return '/tours';
  }

  const pathname = window.location.pathname;

  return pathname === '/' || pathname === '/tours' ? pathname : '/tours';
}

async function applyFilters(page = 1) {
  isNavigating.value = true;

  try {
    const query = buildCatalogQuery(page);
    const pathname = getCatalogPath();

    await navigate(query ? `${pathname}?${query}` : pathname);
  } finally {
    isNavigating.value = false;
  }
}

function resetFilters() {
  filters.category = '';
  filters.duration_min = '';
  filters.duration_max = '';
  filters.price_min = '';
  filters.price_max = '';
  filters.search = '';
  filters.sort = 'newest';
  filters.per_page = '12';
  void applyFilters(1);
}

function hasPage(page: number) {
  return pageNumbers.value.includes(page);
}

function categoryValue(value: string | TourCategory) {
  return value as TourCategory | '';
}
</script>

<template>
  <PublicLayout>
    <section class="content-wrapper pb-12 pt-8">
      <div class="glass-panel relative overflow-hidden rounded-[2.8rem] border-0 p-5 md:p-8 xl:p-10">
        <div class="pointer-events-none absolute inset-x-10 top-0 h-32 rounded-full bg-[radial-gradient(circle,rgba(24,122,140,0.12),transparent_70%)] blur-3xl" />
        <div class="pointer-events-none absolute -right-18 top-24 h-56 w-56 rounded-full bg-[radial-gradient(circle,rgba(240,170,102,0.18),transparent_68%)] blur-3xl" />

        <div class="relative grid gap-8 xl:grid-cols-[310px_minmax(0,1fr)] 2xl:grid-cols-[330px_minmax(0,1fr)]">
          <Card class="border-0 bg-[linear-gradient(180deg,rgba(255,255,255,0.94),rgba(241,249,247,0.86))] shadow-none xl:sticky xl:top-24">
            <template #content>
              <div class="space-y-5">
                <div>
                  <div class="text-xs uppercase tracking-[0.24em] text-[var(--travel-muted)]">Подбор тура</div>
                  <h1 class="mt-2 text-4xl font-semibold leading-tight text-[var(--travel-ink)]">
                    Найдём маршрут под ваш ритм
                  </h1>
                  <p class="mt-3 text-sm leading-6 text-[var(--travel-muted)]">
                    Комбинируйте запрос, категорию, бюджет и длительность, чтобы быстрее сузить выбор.
                  </p>
                </div>

                <div class="space-y-4">
                  <label class="block space-y-2">
                    <span class="text-sm font-medium text-[var(--travel-ink)]">Поиск по каталогу</span>
                    <InputText
                      v-model="filters.search"
                      fluid
                      placeholder="Например: отдых у моря, Байкал, вулканы..."
                      @keyup.enter="applyFilters(1)"
                    />
                  </label>

                  <label class="block space-y-2">
                    <span class="text-sm font-medium text-[var(--travel-ink)]">Категория</span>
                    <Select
                      :model-value="filters.category"
                      :options="categoryOptions"
                      option-label="label"
                      option-value="value"
                      placeholder="Все категории"
                      fluid
                      show-clear
                      @update:model-value="filters.category = categoryValue($event || '')"
                    />
                  </label>

                  <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block space-y-2">
                      <span class="text-sm font-medium text-[var(--travel-ink)]">Длительность от</span>
                      <InputText v-model="filters.duration_min" type="number" min="1" fluid placeholder="3" />
                    </label>
                    <label class="block space-y-2">
                      <span class="text-sm font-medium text-[var(--travel-ink)]">Длительность до</span>
                      <InputText v-model="filters.duration_max" type="number" min="1" fluid placeholder="10" />
                    </label>
                  </div>

                  <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block space-y-2">
                      <span class="text-sm font-medium text-[var(--travel-ink)]">Цена от</span>
                      <InputText v-model="filters.price_min" type="number" min="0" fluid placeholder="30000" />
                    </label>
                    <label class="block space-y-2">
                      <span class="text-sm font-medium text-[var(--travel-ink)]">Цена до</span>
                      <InputText v-model="filters.price_max" type="number" min="0" fluid placeholder="120000" />
                    </label>
                  </div>

                  <label class="block space-y-2">
                    <span class="text-sm font-medium text-[var(--travel-ink)]">Сортировка</span>
                    <Select
                      v-model="filters.sort"
                      :options="sortOptions"
                      option-label="label"
                      option-value="value"
                      fluid
                    />
                  </label>

                  <label class="block space-y-2">
                    <span class="text-sm font-medium text-[var(--travel-ink)]">Карточек на странице</span>
                    <Select
                      v-model="filters.per_page"
                      :options="[
                        { label: '12', value: '12' },
                        { label: '24', value: '24' },
                        { label: '50', value: '50' }
                      ]"
                      option-label="label"
                      option-value="value"
                      fluid
                    />
                  </label>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                  <Button label="Показать туры" class="flex-1" @click="applyFilters(1)" />
                  <Button
                    label="Сбросить"
                    severity="secondary"
                    outlined
                    class="flex-1"
                    @click="resetFilters"
                  />
                </div>
              </div>
            </template>
          </Card>

          <div class="space-y-6">
            <div class="rounded-[2rem] border border-[rgba(24,122,140,0.12)] bg-[linear-gradient(135deg,rgba(255,255,255,0.88),rgba(235,247,245,0.86))] px-6 py-6 shadow-[0_18px_48px_rgba(20,49,59,0.07)]">
              <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                  <div class="text-xs uppercase tracking-[0.24em] text-[var(--travel-muted)]">Результаты</div>
                  <h2 class="mt-2 text-3xl font-semibold leading-tight text-[var(--travel-ink)]">
                    Каталог туров без лишнего шума
                  </h2>
                  <p class="mt-2 max-w-2xl text-sm leading-6 text-[var(--travel-muted)]">
                    Смотрите подборку по запросу, сравнивайте цены и переходите к подробностям тура в один клик.
                  </p>
                </div>

                <div v-if="initialTours" class="rounded-[1.5rem] bg-white/78 px-5 py-4 text-right shadow-[0_12px_28px_rgba(20,49,59,0.05)]">
                  <div class="text-xs uppercase tracking-[0.22em] text-[var(--travel-muted)]">Найдено</div>
                  <div class="mt-1 text-3xl font-semibold text-[var(--travel-ink)]">
                    {{ initialTours.meta.total }}
                  </div>
                  <div class="mt-1 text-sm text-[var(--travel-muted)]">
                    туров в каталоге
                  </div>
                </div>
              </div>
            </div>

            <StateBlock
              v-if="initialError"
              title="Каталог временно недоступен"
              :description="initialError"
            />

            <div v-else-if="isNavigating" class="travel-grid md:grid-cols-2 xl:grid-cols-3">
              <Card
                v-for="cardIndex in 6"
                :key="`skeleton-${cardIndex}`"
                class="overflow-hidden rounded-[2rem] border-0"
              >
                <template #content>
                  <div class="space-y-4">
                    <Skeleton width="100%" height="14rem" borderRadius="1.2rem" />
                    <Skeleton width="35%" height="1.1rem" />
                    <Skeleton width="80%" height="1.4rem" />
                    <Skeleton width="100%" height="4rem" />
                    <div class="flex justify-between gap-4">
                      <Skeleton width="30%" height="1.6rem" />
                      <Skeleton width="28%" height="2.6rem" borderRadius="999px" />
                    </div>
                  </div>
                </template>
              </Card>
            </div>

            <div v-else-if="initialTours?.data.length" class="space-y-6">
              <div class="travel-grid md:grid-cols-2 xl:grid-cols-3">
                <TourCard v-for="tour in initialTours.data" :key="tour.id" :tour="tour" />
              </div>

              <div class="flex flex-wrap items-center justify-between gap-4 rounded-[1.8rem] border border-[rgba(22,52,60,0.08)] bg-white/72 px-5 py-4 shadow-[0_12px_32px_rgba(20,49,59,0.05)]">
                <div class="text-sm text-[var(--travel-muted)]">
                  Страница {{ initialTours.meta.current_page }} из {{ initialTours.meta.last_page }}
                </div>

                <div class="flex flex-wrap items-center gap-2">
                  <Button
                    label="Назад"
                    severity="secondary"
                    outlined
                    :disabled="!initialTours.links.prev"
                    @click="applyFilters(initialTours.meta.current_page - 1)"
                  />

                  <Button
                    v-for="page in pageNumbers"
                    :key="page"
                    :label="String(page)"
                    :severity="page === initialTours.meta.current_page ? 'info' : 'secondary'"
                    :outlined="page !== initialTours.meta.current_page"
                    size="small"
                    @click="applyFilters(page)"
                  />

                  <span
                    v-if="
                      initialTours.meta.last_page > 5 &&
                        !hasPage(initialTours.meta.current_page + 2) &&
                        initialTours.meta.current_page < initialTours.meta.last_page - 2
                    "
                    class="px-2 text-sm text-[var(--travel-muted)]"
                  >
                    ...
                  </span>

                  <Button
                    label="Вперёд"
                    severity="secondary"
                    outlined
                    :disabled="!initialTours.links.next"
                    @click="applyFilters(initialTours.meta.current_page + 1)"
                  />
                </div>
              </div>
            </div>

            <StateBlock
              v-else
              title="Туры не найдены"
              description="Попробуйте ослабить фильтры или изменить поисковый запрос."
            />
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>
