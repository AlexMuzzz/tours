import { apiClient } from '@/services/apiClient';
import type { CatalogFilters, PaginatedResponse, Tour } from '@/types/api';

interface ResourceResponse<T> {
  data: T;
}

export const tourService = {
  getTours(filters: CatalogFilters = {}) {
    return apiClient.get<PaginatedResponse<Tour>>('/tours', filters as Record<string, unknown>);
  },

  async getTour(slug: string) {
    const response = await apiClient.get<ResourceResponse<Tour>>(`/tours/${slug}`);

    return response.data;
  },
};
