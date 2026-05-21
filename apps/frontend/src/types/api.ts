export type UserRole = 'admin' | 'user';
export type TourCategory =
  | 'hiking'
  | 'city'
  | 'gastro'
  | 'nature'
  | 'winter'
  | 'adventure'
  | 'culture';

export type CurrencyCode = 'RUB' | 'USD' | 'EUR';

export interface User {
  id: number;
  name: string;
  email: string;
  role: UserRole;
  created_at: string | null;
  updated_at: string | null;
}

export interface TourImage {
  id: number;
  image_url: string;
  alt_text: string | null;
  sort_order: number;
  created_at: string | null;
  updated_at: string | null;
}

export interface TourDate {
  id: number;
  start_date: string | null;
  end_date: string | null;
  price: number;
  currency: CurrencyCode;
  available_seats: number;
  created_at: string | null;
  updated_at: string | null;
}

export interface TourRoutePoint {
  id: number;
  title: string;
  description: string | null;
  latitude: number;
  longitude: number;
  sort_order: number;
  created_at: string | null;
  updated_at: string | null;
}

export interface TourEmbedding {
  embedding: number[] | null;
  source_text: string | null;
}

export interface Tour {
  id: number;
  title: string;
  slug: string;
  short_description: string | null;
  description: string | null;
  duration_days: number;
  category: TourCategory;
  is_active: boolean;
  main_image: string | null;
  price_from: number | null;
  images?: TourImage[];
  dates?: TourDate[];
  route_points?: TourRoutePoint[];
  embedding?: number[] | null;
  embedding_source_text?: string | null;
  created_at: string | null;
  updated_at: string | null;
}

export interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

export interface PaginationMeta {
  current_page: number;
  from: number | null;
  last_page: number;
  links: PaginationLink[];
  path: string;
  per_page: number;
  to: number | null;
  total: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  links: {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
  };
  meta: PaginationMeta;
}

export interface LoginResponse {
  token: string;
  user: User;
}

export interface ApiError {
  status: number;
  message: string;
  errors?: Record<string, string[]>;
}

export interface CatalogFilters {
  category?: TourCategory | '';
  duration_min?: number | '';
  duration_max?: number | '';
  price_min?: number | '';
  price_max?: number | '';
  search?: string;
  sort?: 'newest' | 'price_asc' | 'price_desc' | 'duration_asc' | 'duration_desc';
  page?: number;
  per_page?: number;
}

export interface TourPayload {
  title: string;
  short_description: string;
  description: string;
  duration_days: number;
  category: TourCategory;
  is_active: boolean;
  main_image: string;
}

export interface TourImagePayload {
  image_url: string;
  alt_text?: string;
  sort_order?: number;
}

export interface TourDatePayload {
  start_date: string;
  end_date: string;
  price: number;
  currency: CurrencyCode;
  available_seats: number;
}

export interface TourRoutePointPayload {
  title: string;
  description?: string;
  latitude: number;
  longitude: number;
  sort_order: number;
}

export interface GeneratedDescriptionResponse {
  description: string;
}
