import { mount } from '@vue/test-utils';
import { vi } from 'vitest';
import { baseGlobalStubs } from '@/test/stubs';
import { createTour } from '@/test/fixtures';

describe('TourMap', () => {
  it('shows placeholder when API key is not configured', async () => {
    vi.resetModules();
    vi.stubEnv('VITE_YANDEX_MAPS_API_KEY', '');

    const { default: TourMap } = await import('@/components/TourMap.vue');

    const wrapper = mount(TourMap, {
      props: {
        routePoints: [
          {
            id: 1,
            title: 'Point A',
            description: null,
            latitude: 55.75,
            longitude: 37.61,
            sort_order: 1,
            created_at: null,
            updated_at: null,
          },
        ],
      },
      global: {
        stubs: baseGlobalStubs,
      },
    });

    expect(wrapper.text()).toContain('Yandex Maps API key is not configured');
  });

  it('does not crash and shows empty route placeholder', async () => {
    vi.resetModules();
    vi.stubEnv('VITE_YANDEX_MAPS_API_KEY', 'configured-key');

    const { default: TourMap } = await import('@/components/TourMap.vue');

    const wrapper = mount(TourMap, {
      props: {
        routePoints: createTour().route_points ?? [],
      },
      global: {
        stubs: baseGlobalStubs,
      },
    });

    expect(wrapper.text()).toContain('Маршрут пока не добавлен');
  });
});
