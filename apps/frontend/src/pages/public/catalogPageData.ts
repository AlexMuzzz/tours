import { ApiClientError } from '@/services/apiClient';
import { tourService } from '@/services/tourService';
import { getErrorMessage } from '@/utils/errors';
import type { CatalogFilters, PaginatedResponse, Tour } from '@/types/api';

export interface CatalogPageContext {
  urlParsed: {
    search: Record<string, string | undefined>;
  };
}

export interface CatalogPageData {
  initialTours: PaginatedResponse<Tour> | null;
  initialError: string | null;
  initialFilters: Required<
    Pick<CatalogFilters, 'sort' | 'page' | 'per_page'>
  > &
    Omit<CatalogFilters, 'sort' | 'page' | 'per_page'>;
}

function asNumber(value: string | undefined): number | '' {
  if (!value) {
    return '';
  }

  const parsed = Number(value);

  return Number.isFinite(parsed) ? parsed : '';
}

export async function loadCatalogPageData(
  pageContext: CatalogPageContext,
): Promise<CatalogPageData> {
  const search = pageContext.urlParsed.search;

  const filters: CatalogPageData['initialFilters'] = {
    category: (search.category as CatalogFilters['category']) ?? '',
    duration_min: asNumber(search.duration_min),
    duration_max: asNumber(search.duration_max),
    price_min: asNumber(search.price_min),
    price_max: asNumber(search.price_max),
    search: search.search ?? '',
    sort: (search.sort as CatalogFilters['sort']) ?? 'newest',
    page: Number(search.page ?? '1') || 1,
    per_page: Number(search.per_page ?? '12') || 12,
  };

  try {
    const tours = await tourService.getTours({
      ...filters,
      category: filters.category || undefined,
      duration_min: filters.duration_min || undefined,
      duration_max: filters.duration_max || undefined,
      price_min: filters.price_min || undefined,
      price_max: filters.price_max || undefined,
      search: filters.search || undefined,
    });

    return {
      initialTours: tours as PaginatedResponse<Tour>,
      initialError: null,
      initialFilters: filters,
    };
  } catch (error) {
    const message = error instanceof ApiClientError && error.status === 422
      ? 'Параметры фильтрации не прошли backend validation.'
      : getErrorMessage(error, 'Не удалось загрузить каталог туров.');

    return {
      initialTours: null,
      initialError: message,
      initialFilters: filters,
    };
  }
}
