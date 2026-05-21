<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import Card from 'primevue/card';
import ProgressSpinner from 'primevue/progressspinner';
import type { TourRoutePoint } from '@/types/api';
import { canUseDOM } from '@/utils/browser';

const props = defineProps<{
  routePoints: TourRoutePoint[];
}>();

const apiKey = import.meta.env.VITE_YANDEX_MAPS_API_KEY;
const mapElement = ref<HTMLElement | null>(null);
const mapLoading = ref(false);
const mapError = ref('');
let mapInstance: any = null;
let scriptPromise: Promise<any> | null = null;

const sortedPoints = computed(() =>
  [...props.routePoints].sort((left, right) => left.sort_order - right.sort_order),
);

function hasConfiguredKey() {
  return Boolean(apiKey) && apiKey !== 'your_yandex_maps_api_key';
}

function getWindowWithYMaps() {
  return window as Window & {
    ymaps?: any;
  };
}

async function loadYandexMaps() {
  if (!canUseDOM) {
    return null;
  }

  const currentWindow = getWindowWithYMaps();

  if (currentWindow.ymaps) {
    return currentWindow.ymaps;
  }

  if (!scriptPromise) {
    scriptPromise = new Promise((resolve, reject) => {
      const existing = document.querySelector<HTMLScriptElement>('script[data-yandex-maps="true"]');

      if (existing) {
        existing.addEventListener('load', () => resolve(currentWindow.ymaps));
        existing.addEventListener('error', () => reject(new Error('Yandex Maps failed to load.')));
        return;
      }

      const script = document.createElement('script');
      script.async = true;
      script.dataset.yandexMaps = 'true';
      script.src = `https://api-maps.yandex.ru/2.1/?apikey=${apiKey}&lang=ru_RU`;
      script.onload = () => resolve(currentWindow.ymaps);
      script.onerror = () => reject(new Error('Yandex Maps failed to load.'));
      document.head.appendChild(script);
    });
  }

  return scriptPromise;
}

async function initializeMap() {
  if (!canUseDOM || !mapElement.value || sortedPoints.value.length === 0 || !hasConfiguredKey()) {
    return;
  }

  if (mapInstance) {
    mapInstance.destroy();
    mapInstance = null;
  }

  mapLoading.value = true;
  mapError.value = '';

  try {
    await nextTick();

    const ymaps = await loadYandexMaps();

    if (!ymaps) {
      return;
    }

    await ymaps.ready();

    const center = [sortedPoints.value[0].latitude, sortedPoints.value[0].longitude];
    mapInstance = new ymaps.Map(mapElement.value, {
      center,
      zoom: 6,
      controls: ['zoomControl', 'fullscreenControl'],
    });

    const coordinates = sortedPoints.value.map((point) => [point.latitude, point.longitude]);

    sortedPoints.value.forEach((point, index) => {
      const placemark = new ymaps.Placemark(
        [point.latitude, point.longitude],
        {
          balloonContentHeader: point.title,
          balloonContentBody: point.description ?? 'Описание точки пока не добавлено.',
          iconCaption: `${index + 1}. ${point.title}`,
        },
        {
          preset: 'islands#darkGreenDotIcon',
        },
      );

      mapInstance.geoObjects.add(placemark);
    });

    if (coordinates.length > 1) {
      const polyline = new ymaps.Polyline(
        coordinates,
        {},
        {
          strokeColor: '#2A7B74',
          strokeWidth: 4,
          strokeOpacity: 0.8,
        },
      );

      mapInstance.geoObjects.add(polyline);
    }

    const bounds = mapInstance.geoObjects.getBounds();

    if (bounds) {
      mapInstance.setBounds(bounds, {
        checkZoomRange: true,
        zoomMargin: 40,
      });
    }
  } catch (error) {
    mapError.value = error instanceof Error ? error.message : 'Не удалось загрузить карту.';
  } finally {
    mapLoading.value = false;
  }
}

onMounted(() => {
  void initializeMap();
});

watch(
  sortedPoints,
  () => {
    if (!canUseDOM) {
      return;
    }

    void initializeMap();
  },
  { deep: true },
);

onUnmounted(() => {
  if (mapInstance) {
    mapInstance.destroy();
    mapInstance = null;
  }
});
</script>

<template>
  <Card class="glass-panel overflow-hidden rounded-[2rem] border-0">
    <template #content>
      <div class="space-y-4">
        <div class="flex items-center justify-between gap-4">
          <div>
            <h3 class="text-2xl font-semibold text-[var(--travel-ink)]">
              Карта маршрута
            </h3>
            <p class="text-sm text-[var(--travel-muted)]">
              Маркеры и линия собираются из route points по sort_order.
            </p>
          </div>
          <ProgressSpinner
            v-if="mapLoading"
            style="width: 2rem; height: 2rem"
            strokeWidth="5"
          />
        </div>

        <div
          v-if="sortedPoints.length === 0"
          class="empty-illustration rounded-[1.5rem] px-6 py-12 text-center text-sm text-[var(--travel-muted)]"
        >
          Маршрут пока не добавлен
        </div>

        <div
          v-else-if="!hasConfiguredKey()"
          class="empty-illustration rounded-[1.5rem] px-6 py-12 text-center text-sm text-[var(--travel-muted)]"
        >
          Yandex Maps API key is not configured
        </div>

        <div
          v-else-if="mapError"
          class="empty-illustration rounded-[1.5rem] px-6 py-12 text-center text-sm text-[var(--travel-muted)]"
        >
          {{ mapError }}
        </div>

        <div
          v-else
          ref="mapElement"
          class="h-[360px] overflow-hidden rounded-[1.5rem] border border-[var(--travel-line)]"
        />
      </div>
    </template>
  </Card>
</template>
