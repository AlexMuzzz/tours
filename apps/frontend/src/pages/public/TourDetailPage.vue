<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import type { Tour } from '@/types/api';
import PublicLayout from '@/layouts/PublicLayout.vue';
import CategoryTag from '@/components/CategoryTag.vue';
import StateBlock from '@/components/StateBlock.vue';
import TourMap from '@/components/TourMap.vue';
import { formatDateRange, formatDuration, formatPrice } from '@/utils/formatters';

const props = defineProps<{
  tour: Tour | null;
  error: string | null;
}>();

const galleryImages = computed(() => {
  if (!props.tour) {
    return [];
  }

  const seen = new Set<string>();
  const images: Array<{ url: string; alt: string }> = [];

  if (props.tour.main_image) {
    seen.add(props.tour.main_image);
    images.push({ url: props.tour.main_image, alt: props.tour.title });
  }

  (props.tour.images ?? []).forEach((image) => {
    if (!seen.has(image.image_url)) {
      seen.add(image.image_url);
      images.push({
        url: image.image_url,
        alt: image.alt_text || props.tour?.title || 'Tour image',
      });
    }
  });

  return images;
});

const routePoints = computed(() =>
  [...(props.tour?.route_points ?? [])].sort((left, right) => left.sort_order - right.sort_order),
);

const activeImage = ref('');

watch(
  galleryImages,
  (images) => {
    if (!images.some((image) => image.url === activeImage.value)) {
      activeImage.value = images[0]?.url ?? '';
    }
  },
  { immediate: true },
);
</script>

<template>
  <PublicLayout>
    <section class="content-wrapper pt-10">
      <StateBlock
        v-if="error || !tour"
        title="Тур недоступен"
        :description="error || 'Похоже, этот тур скрыт или не найден.'"
      />

      <div v-else class="space-y-8">
        <div class="glass-panel rounded-[2.6rem] border-0 p-8 md:p-10">
          <div class="grid gap-8 lg:grid-cols-[1.15fr_0.85fr]">
            <div class="space-y-4">
              <div class="overflow-hidden rounded-[2rem]">
                <img
                  v-if="activeImage"
                  :src="activeImage"
                  :alt="tour.title"
                  class="h-[420px] w-full object-cover"
                />
                <div
                  v-else
                  class="flex h-[420px] items-center justify-center bg-gradient-to-br from-[rgba(42,123,116,0.14)] to-[rgba(229,165,68,0.16)] text-sm text-[var(--travel-muted)]"
                >
                  Галерея появится позже
                </div>
              </div>

              <div v-if="galleryImages.length > 1" class="grid grid-cols-4 gap-3">
                <button
                  v-for="image in galleryImages"
                  :key="image.url"
                  type="button"
                  class="overflow-hidden rounded-[1.25rem] border-2 transition"
                  :class="activeImage === image.url ? 'border-[var(--travel-ocean)]' : 'border-transparent'"
                  @click="activeImage = image.url"
                >
                  <img :src="image.url" :alt="image.alt" class="h-24 w-full object-cover" />
                </button>
              </div>
            </div>

            <div class="space-y-5">
              <div class="flex flex-wrap items-center gap-3">
                <CategoryTag :category="tour.category" />
                <Tag severity="secondary" rounded :value="formatDuration(tour.duration_days)" />
              </div>

              <div class="space-y-4">
                <h1 class="text-5xl font-semibold leading-tight text-[var(--travel-ink)]">
                  {{ tour.title }}
                </h1>
                <p class="text-lg leading-8 text-[var(--travel-muted)]">
                  {{ tour.short_description || 'Короткое описание пока не заполнено.' }}
                </p>
              </div>

              <div class="rounded-[1.8rem] bg-white/70 p-5">
                <div class="text-xs uppercase tracking-[0.24em] text-[var(--travel-muted)]">Цена от</div>
                <div class="mt-2 text-3xl font-semibold text-[var(--travel-ink)]">
                  {{ formatPrice(tour.price_from) }}
                </div>
              </div>

              <Card class="border-0 bg-white/70 shadow-none">
                <template #content>
                  <div class="space-y-3">
                    <div class="text-xs uppercase tracking-[0.24em] text-[var(--travel-muted)]">Описание</div>
                    <p class="whitespace-pre-line text-sm leading-7 text-[var(--travel-muted)]">
                      {{ tour.description || 'Подробное описание тура появится позже.' }}
                    </p>
                  </div>
                </template>
              </Card>
            </div>
          </div>
        </div>

        <div class="grid gap-8 xl:grid-cols-[0.9fr_1.1fr]">
          <Card class="glass-panel rounded-[2rem] border-0">
            <template #content>
              <div class="space-y-5">
                <div>
                  <div class="text-xs uppercase tracking-[0.24em] text-[var(--travel-muted)]">Даты</div>
                  <h2 class="mt-2 text-3xl font-semibold text-[var(--travel-ink)]">
                    Заезды и цены
                  </h2>
                </div>

                <div v-if="tour.dates?.length" class="space-y-4">
                  <article
                    v-for="date in tour.dates"
                    :key="date.id"
                    class="rounded-[1.5rem] border border-[var(--travel-line)] bg-white/70 p-5"
                  >
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                      <div>
                        <div class="text-sm font-semibold text-[var(--travel-ink)]">
                          {{ formatDateRange(date.start_date, date.end_date) }}
                        </div>
                        <div class="mt-1 text-sm text-[var(--travel-muted)]">
                          Доступно мест: {{ date.available_seats }}
                        </div>
                      </div>
                      <div class="text-xl font-semibold text-[var(--travel-ink)]">
                        {{ formatPrice(date.price, date.currency) }}
                      </div>
                    </div>
                  </article>
                </div>

                <StateBlock
                  v-else
                  title="Даты скоро появятся"
                  description="Тур уже в каталоге, но выезды и цены пока не опубликованы."
                />
              </div>
            </template>
          </Card>

          <Card class="glass-panel rounded-[2rem] border-0">
            <template #content>
              <div class="space-y-5">
                <div>
                  <div class="text-xs uppercase tracking-[0.24em] text-[var(--travel-muted)]">Маршрут</div>
                  <h2 class="mt-2 text-3xl font-semibold text-[var(--travel-ink)]">
                    Точки пути
                  </h2>
                </div>

                <div v-if="routePoints.length" class="space-y-4">
                  <article
                    v-for="point in routePoints"
                    :key="point.id"
                    class="rounded-[1.5rem] border border-[var(--travel-line)] bg-white/70 p-5"
                  >
                    <div class="flex items-start gap-4">
                      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[rgba(42,123,116,0.12)] font-semibold text-[var(--travel-ocean)]">
                        {{ point.sort_order }}
                      </div>
                      <div class="space-y-2">
                        <div class="text-lg font-semibold text-[var(--travel-ink)]">
                          {{ point.title }}
                        </div>
                        <p class="text-sm leading-7 text-[var(--travel-muted)]">
                          {{ point.description || 'Описание этой точки пока не добавлено.' }}
                        </p>
                        <div class="text-xs uppercase tracking-[0.18em] text-[var(--travel-muted)]">
                          {{ point.latitude }}, {{ point.longitude }}
                        </div>
                      </div>
                    </div>
                  </article>
                </div>

                <StateBlock
                  v-else
                  title="Маршрут пока не заполнен"
                  description="Frontend готов показать timeline и карту, как только администратор добавит route points."
                />
              </div>
            </template>
          </Card>
        </div>

        <TourMap :route-points="routePoints" />
      </div>
    </section>
  </PublicLayout>
</template>
