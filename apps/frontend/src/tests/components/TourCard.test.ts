import { mount } from '@vue/test-utils';
import TourCard from '@/components/TourCard.vue';
import { createTour } from '@/test/fixtures';
import { baseGlobalStubs } from '@/test/stubs';

describe('TourCard', () => {
  it('shows title, category, duration and price', () => {
    const wrapper = mount(TourCard, {
      props: {
        tour: createTour({
          title: 'Тур по Байкалу',
          category: 'nature',
          duration_days: 7,
          price_from: 65000,
        }),
      },
      global: {
        stubs: baseGlobalStubs,
      },
    });

    expect(wrapper.text()).toContain('Тур по Байкалу');
    expect(wrapper.text()).toContain('Природа');
    expect(wrapper.text()).toContain('7 дней');
    expect(wrapper.text()).toContain('65');
    expect(wrapper.get('a').attributes('href')).toBe('/tours/tur-po-altayu');
    expect(wrapper.text()).not.toContain('Подробнее');
  });

  it('falls back when the image cannot be loaded', async () => {
    const wrapper = mount(TourCard, {
      props: {
        tour: createTour({
          title: 'Тур по Байкалу',
          main_image: 'http://localhost:8000/storage/tours/1/main.jpg',
        }),
      },
      global: {
        stubs: baseGlobalStubs,
      },
    });

    await wrapper.get('img').trigger('error');

    expect(wrapper.text()).toContain('Изображение временно недоступно');
  });
});
