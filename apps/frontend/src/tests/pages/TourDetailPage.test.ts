import { mount } from '@vue/test-utils';
import TourDetailPage from '@/pages/public/TourDetailPage.vue';
import { createTour } from '@/test/fixtures';
import { CardStub, LayoutStub, TagStub } from '@/test/stubs';

describe('TourDetailPage', () => {
  it('shows a fallback when the main image cannot be loaded', async () => {
    const wrapper = mount(TourDetailPage, {
      props: {
        tour: createTour({
          title: 'Плавание по Атлантическому берегу',
          main_image: 'http://localhost:8000/storage/tours/18/main.jpg',
          images: [],
        }),
        error: null,
      },
      global: {
        stubs: {
          PublicLayout: LayoutStub,
          Card: CardStub,
          Tag: TagStub,
          CategoryTag: LayoutStub,
          StateBlock: LayoutStub,
          TourMap: LayoutStub,
        },
      },
    });

    await wrapper.get('img').trigger('error');

    expect(wrapper.text()).toContain('Изображение тура временно недоступно');
  });
});
