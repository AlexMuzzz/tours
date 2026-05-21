<script setup lang="ts">
import type { Tour } from '@/types/api';
import PublicLayout from '@/layouts/PublicLayout.vue';
import TourCard from '@/components/TourCard.vue';
import StateBlock from '@/components/StateBlock.vue';

defineProps<{
  featuredTours: Tour[];
  error: string | null;
}>();
</script>

<template>
  <PublicLayout>
    <section class="content-wrapper pt-10">
      <div class="glass-panel overflow-hidden rounded-[2.6rem] border-0 p-8 md:p-12">
        <div class="grid items-center gap-10 lg:grid-cols-[1.1fr_0.9fr]">
          <div class="space-y-6">
            <div class="inline-flex items-center gap-3 rounded-full bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-[var(--travel-ocean)]">
              <span class="travel-dot" />
              AI-ready travel catalog
            </div>

            <div class="space-y-5">
              <h1 class="max-w-4xl text-5xl font-semibold leading-tight text-[var(--travel-ink)] md:text-6xl">
                Находите туры по настроению, сезону и смыслу поездки.
              </h1>
              <p class="max-w-2xl text-base leading-8 text-[var(--travel-muted)]">
                Tour Catalog AI показывает живой каталог туров, поддерживает обычный и semantic search,
                а админка уже готова к управлению маршрутами, датами, ценами и описаниями.
              </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
              <a href="/tours" class="travel-link travel-link-primary">
                Перейти в каталог
              </a>
              <a href="/admin/login" class="travel-link travel-link-secondary">
                Открыть admin login
              </a>
            </div>

            <div class="grid gap-4 pt-4 md:grid-cols-3">
              <div class="rounded-[1.5rem] border border-white/70 bg-white/70 p-4">
                <div class="text-xs uppercase tracking-[0.2em] text-[var(--travel-muted)]">Поиск</div>
                <div class="mt-2 text-lg font-semibold text-[var(--travel-ink)]">Фильтры + semantic</div>
              </div>
              <div class="rounded-[1.5rem] border border-white/70 bg-white/70 p-4">
                <div class="text-xs uppercase tracking-[0.2em] text-[var(--travel-muted)]">Маршруты</div>
                <div class="mt-2 text-lg font-semibold text-[var(--travel-ink)]">Точки + карта</div>
              </div>
              <div class="rounded-[1.5rem] border border-white/70 bg-white/70 p-4">
                <div class="text-xs uppercase tracking-[0.2em] text-[var(--travel-muted)]">Управление</div>
                <div class="mt-2 text-lg font-semibold text-[var(--travel-ink)]">Admin CRUD</div>
              </div>
            </div>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-[2rem] bg-[linear-gradient(160deg,rgba(42,123,116,0.92),rgba(23,55,47,0.9))] p-6 text-white shadow-2xl">
              <div class="text-xs uppercase tracking-[0.24em] text-white/70">Сценарии</div>
              <div class="mt-4 text-3xl font-semibold">Публичный каталог</div>
              <p class="mt-3 text-sm leading-7 text-white/80">
                Анонимный пользователь изучает туры, цены, даты и маршрут поездки.
              </p>
            </div>

            <div class="rounded-[2rem] bg-[linear-gradient(160deg,rgba(229,165,68,0.92),rgba(188,116,41,0.92))] p-6 text-white shadow-2xl sm:translate-y-8">
              <div class="text-xs uppercase tracking-[0.24em] text-white/70">Админка</div>
              <div class="mt-4 text-3xl font-semibold">Управление контентом</div>
              <p class="mt-3 text-sm leading-7 text-white/85">
                Тур, даты, изображения и route points редактируются через Laravel API.
              </p>
            </div>

            <div class="rounded-[2rem] border border-white/70 bg-white/80 p-6 text-[var(--travel-ink)] shadow-xl sm:col-span-2">
              <div class="text-xs uppercase tracking-[0.24em] text-[var(--travel-muted)]">Дальше по roadmap</div>
              <div class="mt-4 text-3xl font-semibold">Embeddings и AI generation</div>
              <p class="mt-3 text-sm leading-7 text-[var(--travel-muted)]">
                Архитектура уже готова к подключению реального semantic search и генерации описаний.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="content-wrapper mt-12">
      <div class="mb-6 flex items-end justify-between gap-4">
        <div>
          <div class="text-xs uppercase tracking-[0.24em] text-[var(--travel-muted)]">Подборка</div>
          <h2 class="mt-2 text-4xl font-semibold text-[var(--travel-ink)]">
            Популярные туры
          </h2>
        </div>
        <a href="/tours" class="travel-link travel-link-secondary text-sm">
          Смотреть все
        </a>
      </div>

      <StateBlock
        v-if="error"
        title="Подборка временно недоступна"
        :description="error"
      />

      <div v-else class="travel-grid md:grid-cols-2 xl:grid-cols-4">
        <TourCard v-for="tour in featuredTours" :key="tour.id" :tour="tour" />
      </div>
    </section>
  </PublicLayout>
</template>
