import type { PaginatedResponse, Tour, User } from '@/types/api';

export function createUser(overrides: Partial<User> = {}): User {
  return {
    id: 1,
    name: 'Admin User',
    email: 'admin@example.com',
    role: 'admin',
    created_at: null,
    updated_at: null,
    ...overrides,
  };
}

export function createTour(overrides: Partial<Tour> = {}): Tour {
  return {
    id: 1,
    title: 'Тур по Алтаю',
    slug: 'tur-po-altayu',
    short_description: 'Короткое описание тура',
    description: 'Подробное описание тура',
    duration_days: 5,
    category: 'nature',
    is_active: true,
    main_image: 'https://example.com/tour.jpg',
    price_from: 49900,
    images: [],
    dates: [],
    route_points: [],
    embedding: null,
    embedding_source_text: null,
    created_at: null,
    updated_at: null,
    ...overrides,
  };
}

export function createPaginatedTours(tours: Tour[]): PaginatedResponse<Tour> {
  return {
    data: tours,
    links: {
      first: null,
      last: null,
      prev: null,
      next: null,
    },
    meta: {
      current_page: 1,
      from: tours.length ? 1 : null,
      last_page: 1,
      links: [],
      path: '/api/tours',
      per_page: 12,
      to: tours.length || null,
      total: tours.length,
    },
  };
}
