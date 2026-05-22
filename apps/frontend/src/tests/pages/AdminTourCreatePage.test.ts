import { flushPromises, mount } from '@vue/test-utils';
import { vi } from 'vitest';
import AdminTourCreatePage from '@/pages/admin/AdminTourCreatePage.vue';
import { LayoutStub, baseGlobalStubs } from '@/test/stubs';
import { createTour as createTourFixture } from '@/test/fixtures';

const { navigateMock, toastAddMock, adminTourServiceMock } = vi.hoisted(() => ({
  navigateMock: vi.fn(),
  toastAddMock: vi.fn(),
  adminTourServiceMock: {
    createTour: vi.fn(),
  },
}));

vi.mock('vike/client/router', () => ({
  navigate: navigateMock,
}));

vi.mock('primevue/usetoast', () => ({
  useToast: () => ({
    add: toastAddMock,
  }),
}));

vi.mock('@/services/adminTourService', () => ({
  adminTourService: adminTourServiceMock,
}));

describe('AdminTourCreatePage', () => {
  beforeEach(() => {
    navigateMock.mockReset();
    toastAddMock.mockReset();
    adminTourServiceMock.createTour.mockReset();
    adminTourServiceMock.createTour.mockResolvedValue(createTourFixture({
      id: 42,
      title: 'Upload Cover Tour',
    }));
  });

  it('passes the selected cover file to adminTourService.createTour', async () => {
    const wrapper = mount(AdminTourCreatePage, {
      global: {
        stubs: {
          ...baseGlobalStubs,
          AdminLayout: LayoutStub,
        },
      },
    });

    const file = new File(['cover'], 'cover.png', { type: 'image/png' });
    const fileInput = wrapper.find('input[type="file"]');

    Object.defineProperty(fileInput.element, 'files', {
      value: [file],
      configurable: true,
    });

    await fileInput.trigger('change');
    await wrapper.find('form').trigger('submit.prevent');
    await flushPromises();

    expect(adminTourServiceMock.createTour).toHaveBeenCalledWith(expect.objectContaining({
      main_image_file: file,
    }));
    expect(navigateMock).toHaveBeenCalledWith('/admin/tours/42/edit');
  });

  it('normalizes relative main image URLs in the preview block', async () => {
    const wrapper = mount(AdminTourCreatePage, {
      global: {
        stubs: {
          ...baseGlobalStubs,
          AdminLayout: LayoutStub,
        },
      },
    });

    await wrapper.get('input[type="url"]').setValue('/storage/tours/42/main.jpg');

    expect(wrapper.get('[data-testid="main-image-preview"]').attributes('src'))
      .toBe('http://localhost:8000/storage/tours/42/main.jpg');
  });
});
