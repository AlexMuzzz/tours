import { mount } from '@vue/test-utils';
import HomePage from '@/pages/public/HomePage.vue';
import { createTour } from '@/test/fixtures';
import { LayoutStub, TourCardStub } from '@/test/stubs';

describe('HomePage', () => {
  it('renders without crashing', () => {
    const wrapper = mount(HomePage, {
      props: {
        featuredTours: [createTour()],
        error: null,
      },
      global: {
        stubs: {
          PublicLayout: LayoutStub,
          TourCard: TourCardStub,
          StateBlock: true,
        },
      },
    });

    expect(wrapper.text()).toContain('Находите туры по настроению, сезону и смыслу поездки.');
    expect(wrapper.text()).toContain('Популярные туры');
  });
});
