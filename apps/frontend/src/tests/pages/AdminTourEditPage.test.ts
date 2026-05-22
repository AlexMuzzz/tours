import { flushPromises, mount } from '@vue/test-utils';
import { vi } from 'vitest';
import AdminTourEditPage from '@/pages/admin/AdminTourEditPage.vue';
import { createTour } from '@/test/fixtures';
import { LayoutStub, baseGlobalStubs } from '@/test/stubs';

const { toastAddMock, confirmRequireMock, adminTourServiceMock } = vi.hoisted(() => ({
  toastAddMock: vi.fn(),
  confirmRequireMock: vi.fn(),
  adminTourServiceMock: {
    getTour: vi.fn(),
    updateTour: vi.fn(),
    addImage: vi.fn(),
    deleteImage: vi.fn(),
    addDate: vi.fn(),
    updateDate: vi.fn(),
    deleteDate: vi.fn(),
    addRoutePoint: vi.fn(),
    updateRoutePoint: vi.fn(),
    deleteRoutePoint: vi.fn(),
  },
}));

vi.mock('primevue/usetoast', () => ({
  useToast: () => ({
    add: toastAddMock,
  }),
}));

vi.mock('primevue/useconfirm', () => ({
  useConfirm: () => ({
    require: confirmRequireMock,
  }),
}));

vi.mock('@/services/adminTourService', () => ({
  adminTourService: adminTourServiceMock,
}));

describe('AdminTourEditPage', () => {
  beforeEach(() => {
    toastAddMock.mockReset();
    confirmRequireMock.mockReset();
    Object.values(adminTourServiceMock).forEach((mock) => {
      mock.mockReset();
    });

    const tour = createTour({
      id: 17,
      title: 'Казанский гастро-уикенд',
      main_image: '/storage/tours/17/main.jpg',
      images: [],
      dates: [],
      route_points: [],
    });

    adminTourServiceMock.getTour.mockResolvedValue(tour);
    adminTourServiceMock.updateTour.mockResolvedValue(tour);
    adminTourServiceMock.addImage.mockResolvedValue({
      id: 1,
      image_url: '/storage/tours/17/gallery/gallery.png',
      alt_text: 'Gallery image',
      sort_order: 1,
      created_at: null,
      updated_at: null,
    });
  });

  it('normalizes the saved main image URL for preview after loading the tour', async () => {
    const wrapper = mount(AdminTourEditPage, {
      props: {
        id: '17',
      },
      global: {
        stubs: {
          ...baseGlobalStubs,
          AdminLayout: LayoutStub,
          StateBlock: LayoutStub,
        },
      },
    });

    await flushPromises();

    expect(wrapper.get('[data-testid="main-image-preview"]').attributes('src'))
      .toBe('http://localhost:8000/storage/tours/17/main.jpg');
  });

  it('passes the selected cover file to adminTourService.updateTour', async () => {
    const wrapper = mount(AdminTourEditPage, {
      props: {
        id: '17',
      },
      global: {
        stubs: {
          ...baseGlobalStubs,
          AdminLayout: LayoutStub,
          StateBlock: LayoutStub,
        },
      },
    });

    await flushPromises();

    const file = new File(['cover'], 'updated-cover.png', { type: 'image/png' });
    const fileInput = wrapper.findAll('input[type="file"]')[0];

    Object.defineProperty(fileInput.element, 'files', {
      value: [file],
      configurable: true,
    });

    await fileInput.trigger('change');
    await wrapper.findAll('form')[0].trigger('submit.prevent');
    await flushPromises();

    expect(adminTourServiceMock.updateTour).toHaveBeenCalledWith('17', expect.objectContaining({
      main_image_file: file,
      remove_main_image: false,
    }));
  });

  it('passes the selected gallery file to adminTourService.addImage', async () => {
    const wrapper = mount(AdminTourEditPage, {
      props: {
        id: '17',
      },
      global: {
        stubs: {
          ...baseGlobalStubs,
          AdminLayout: LayoutStub,
          StateBlock: LayoutStub,
        },
      },
    });

    await flushPromises();

    const file = new File(['gallery'], 'gallery.png', { type: 'image/png' });
    const fileInput = wrapper.findAll('input[type="file"]')[1];

    Object.defineProperty(fileInput.element, 'files', {
      value: [file],
      configurable: true,
    });

    await fileInput.trigger('change');
    await wrapper.findAll('form')[1].trigger('submit.prevent');
    await flushPromises();

    expect(adminTourServiceMock.addImage).toHaveBeenCalledWith('17', expect.objectContaining({
      image_file: file,
    }));
  });

  it('normalizes relative gallery image URLs in the preview block', async () => {
    const wrapper = mount(AdminTourEditPage, {
      props: {
        id: '17',
      },
      global: {
        stubs: {
          ...baseGlobalStubs,
          AdminLayout: LayoutStub,
          StateBlock: LayoutStub,
        },
      },
    });

    await flushPromises();
    await wrapper.findAll('input[type="url"]')[1].setValue('/storage/tours/17/gallery/gallery.png');

    expect(wrapper.get('[data-testid="gallery-image-preview"]').attributes('src'))
      .toBe('http://localhost:8000/storage/tours/17/gallery/gallery.png');
  });
});
