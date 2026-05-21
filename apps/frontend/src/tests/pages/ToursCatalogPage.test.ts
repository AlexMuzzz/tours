import { flushPromises, mount } from '@vue/test-utils';
import { vi } from 'vitest';
import ToursCatalogPage from '@/pages/public/ToursCatalogPage.vue';
import { createPaginatedTours, createTour } from '@/test/fixtures';
import { LayoutStub, TourCardStub, baseGlobalStubs } from '@/test/stubs';
import type { CatalogFilters } from '@/types/api';

const { navigateMock } = vi.hoisted(() => ({
  navigateMock: vi.fn(),
}));

vi.mock('vike/client/router', () => ({
  navigate: navigateMock,
}));

describe('ToursCatalogPage', () => {
  const baseProps = {
    initialFilters: {
      category: '' as CatalogFilters['category'],
      duration_min: '' as CatalogFilters['duration_min'],
      duration_max: '' as CatalogFilters['duration_max'],
      price_min: '' as CatalogFilters['price_min'],
      price_max: '' as CatalogFilters['price_max'],
      search: '',
      sort: 'newest' as const,
      page: 1,
      per_page: 12,
    },
  };

  beforeEach(() => {
    navigateMock.mockReset();
    navigateMock.mockResolvedValue(undefined);
    window.history.pushState({}, '', '/');
  });

  it('shows error state', () => {
    const wrapper = mount(ToursCatalogPage, {
      props: {
        ...baseProps,
        initialTours: null,
        initialError: 'Каталог недоступен',
      },
      global: {
        stubs: {
          ...baseGlobalStubs,
          PublicLayout: LayoutStub,
          TourCard: TourCardStub,
        },
      },
    });

    expect(wrapper.text()).toContain('Каталог временно недоступен');
    expect(wrapper.text()).toContain('Каталог недоступен');
  });

  it('shows empty state', () => {
    const wrapper = mount(ToursCatalogPage, {
      props: {
        ...baseProps,
        initialTours: createPaginatedTours([]),
        initialError: null,
      },
      global: {
        stubs: {
          ...baseGlobalStubs,
          PublicLayout: LayoutStub,
          TourCard: TourCardStub,
        },
      },
    });

    expect(wrapper.text()).toContain('Туры не найдены');
  });

  it('shows loading skeletons while applying filters', async () => {
    let resolveNavigate: () => void = () => {};

    navigateMock.mockImplementation(
      () =>
        new Promise<void>((resolve) => {
          resolveNavigate = resolve;
        }),
    );

    const wrapper = mount(ToursCatalogPage, {
      props: {
        ...baseProps,
        initialTours: createPaginatedTours([createTour()]),
        initialError: null,
      },
      global: {
        stubs: {
          ...baseGlobalStubs,
          PublicLayout: LayoutStub,
          TourCard: TourCardStub,
        },
      },
    });

    await wrapper
      .findAll('button')
      .find((button) => button.text().includes('Применить'))
      ?.trigger('click');
    await wrapper.vm.$nextTick();

    expect(wrapper.findAll('[data-testid="skeleton-stub"]').length).toBeGreaterThan(0);

    resolveNavigate();
    await flushPromises();
  });

  it('uses unified catalog search in URL together with filters', async () => {
    const wrapper = mount(ToursCatalogPage, {
      props: {
        ...baseProps,
        initialTours: createPaginatedTours([createTour()]),
        initialError: null,
      },
      global: {
        stubs: {
          ...baseGlobalStubs,
          PublicLayout: LayoutStub,
          TourCard: TourCardStub,
        },
      },
    });

    await wrapper.find('input[placeholder*="отдых у моря"]').setValue('отдых у моря');
    await wrapper.find('select').setValue('nature');
    await wrapper
      .findAll('button')
      .find((button) => button.text().includes('Применить'))
      ?.trigger('click');
    await flushPromises();

    expect(navigateMock).toHaveBeenCalledWith(
      '/?category=nature&search=%D0%BE%D1%82%D0%B4%D1%8B%D1%85%20%D1%83%20%D0%BC%D0%BE%D1%80%D1%8F&sort=newest&page=1&per_page=12',
    );
    expect(wrapper.text()).not.toContain('Semantic Search');
  });
});
