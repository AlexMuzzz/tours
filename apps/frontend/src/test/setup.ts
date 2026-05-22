import { afterEach, beforeAll, vi } from 'vitest';

beforeAll(() => {
  Object.defineProperty(window, 'matchMedia', {
    writable: true,
    value: vi.fn().mockImplementation((query: string) => ({
      matches: false,
      media: query,
      onchange: null,
      addListener: vi.fn(),
      removeListener: vi.fn(),
      addEventListener: vi.fn(),
      removeEventListener: vi.fn(),
      dispatchEvent: vi.fn(),
    })),
  });

  class ResizeObserverMock {
    observe() {}

    unobserve() {}

    disconnect() {}
  }

  class IntersectionObserverMock {
    observe() {}

    unobserve() {}

    disconnect() {}
  }

  vi.stubGlobal('ResizeObserver', ResizeObserverMock);
  vi.stubGlobal('IntersectionObserver', IntersectionObserverMock);
  vi.stubGlobal('scrollTo', vi.fn());
  Object.defineProperty(globalThis.URL, 'createObjectURL', {
    writable: true,
    value: vi.fn(() => 'blob:preview'),
  });
  Object.defineProperty(globalThis.URL, 'revokeObjectURL', {
    writable: true,
    value: vi.fn(),
  });
});

afterEach(() => {
  vi.unstubAllEnvs();
});
