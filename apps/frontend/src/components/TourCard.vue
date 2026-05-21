<script setup lang="ts">
import Card from 'primevue/card';
import type { Tour } from '@/types/api';
import { formatDuration, formatPrice } from '@/utils/formatters';
import CategoryTag from '@/components/CategoryTag.vue';

defineProps<{
  tour: Tour;
}>();
</script>

<template>
  <Card class="glass-panel h-full overflow-hidden rounded-[2rem] border-0">
    <template #content>
      <article class="flex h-full flex-col gap-5">
        <div class="overflow-hidden rounded-[1.4rem]">
          <img
            v-if="tour.main_image"
            :src="tour.main_image"
            :alt="tour.title"
            class="h-56 w-full object-cover transition duration-500 hover:scale-[1.03]"
          />
          <div
            v-else
            class="flex h-56 items-center justify-center bg-gradient-to-br from-[rgba(42,123,116,0.15)] to-[rgba(229,165,68,0.16)] text-sm font-medium text-[var(--travel-muted)]"
          >
            Фото появится позже
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <CategoryTag :category="tour.category" />
          <span class="text-sm text-[var(--travel-muted)]">{{ formatDuration(tour.duration_days) }}</span>
        </div>

        <div class="space-y-3">
          <h3 class="text-2xl font-semibold leading-tight text-[var(--travel-ink)]">
            {{ tour.title }}
          </h3>
          <p class="line-clamp-3 text-sm leading-6 text-[var(--travel-muted)]">
            {{ tour.short_description || 'Описание тура появится немного позже.' }}
          </p>
        </div>

        <div class="mt-auto flex items-end justify-between gap-4">
          <div>
            <div class="text-xs uppercase tracking-[0.2em] text-[var(--travel-muted)]">
              Цена от
            </div>
            <div class="text-xl font-semibold text-[var(--travel-ink)]">
              {{ formatPrice(tour.price_from) }}
            </div>
          </div>

          <a :href="`/tours/${tour.slug}`" class="travel-link travel-link-primary text-sm">
            Подробнее
          </a>
        </div>
      </article>
    </template>
  </Card>
</template>
