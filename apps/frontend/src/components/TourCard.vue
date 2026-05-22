<script setup lang="ts">
import { computed } from 'vue';
import Card from 'primevue/card';
import type { Tour } from '@/types/api';
import { useImageAvailability } from '@/composables/useImageAvailability';
import { formatDuration, formatPrice } from '@/utils/formatters';
import { resolveMediaUrl } from '@/utils/media';
import CategoryTag from '@/components/CategoryTag.vue';

const props = defineProps<{
  tour: Tour;
}>();

const mainImageUrl = computed(() => resolveMediaUrl(props.tour.main_image));
const { isBroken: mainImageBroken } = useImageAvailability(mainImageUrl);

function handleMainImageError() {
  mainImageBroken.value = true;
}
</script>

<template>
  <Card class="glass-panel h-full overflow-hidden rounded-[2.1rem] border-0 transition duration-300 hover:-translate-y-1 hover:shadow-[0_28px_70px_rgba(19,60,70,0.16)]">
    <template #content>
      <a
        :href="`/tours/${tour.slug}`"
        class="group block h-full rounded-[1.7rem] focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(24,122,140,0.35)] focus-visible:ring-offset-4 focus-visible:ring-offset-transparent"
      >
        <article class="flex h-full min-h-[31rem] flex-col gap-5">
          <div class="relative overflow-hidden rounded-[1.5rem]">
            <img
              v-if="mainImageUrl && !mainImageBroken"
              :src="mainImageUrl"
              :alt="tour.title"
              class="h-60 w-full object-cover transition duration-500 group-hover:scale-[1.04]"
              @error="handleMainImageError"
            />
            <div
              v-if="mainImageUrl && !mainImageBroken"
              class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-[rgba(13,33,39,0.26)] via-transparent to-transparent"
            />
            <div
              v-else-if="mainImageUrl"
              class="flex h-60 items-center justify-center bg-gradient-to-br from-[rgba(24,122,140,0.14)] to-[rgba(240,170,102,0.18)] px-5 text-center text-sm font-medium text-[var(--travel-muted)]"
            >
              Изображение временно недоступно
            </div>
            <div
              v-else
              class="flex h-60 items-center justify-center bg-gradient-to-br from-[rgba(24,122,140,0.14)] to-[rgba(240,170,102,0.18)] text-sm font-medium text-[var(--travel-muted)]"
            >
              Фото появится позже
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-3">
            <CategoryTag :category="tour.category" />
            <span class="rounded-full bg-[rgba(24,122,140,0.08)] px-3 py-1 text-sm font-medium text-[var(--travel-muted)]">
              {{ formatDuration(tour.duration_days) }}
            </span>
          </div>

          <div class="space-y-3">
            <h3 class="line-clamp-2 min-h-[3.75rem] text-2xl font-semibold leading-tight text-[var(--travel-ink)]">
              {{ tour.title }}
            </h3>
            <p class="line-clamp-3 min-h-[4.5rem] text-sm leading-6 text-[var(--travel-muted)]">
              {{ tour.short_description || 'Описание тура появится немного позже.' }}
            </p>
          </div>

          <div class="mt-auto border-t border-[var(--travel-line)] pt-4">
            <div class="min-w-0">
              <div class="text-xs uppercase tracking-[0.2em] text-[var(--travel-muted)]">
                Цена от
              </div>
              <div class="text-xl font-semibold text-[var(--travel-ink)]">
                {{ formatPrice(tour.price_from) }}
              </div>
            </div>
          </div>
        </article>
      </a>
    </template>
  </Card>
</template>
