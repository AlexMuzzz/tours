import { apiClient } from '@/services/apiClient';
import { authService } from '@/services/authService';
import type {
  PaginatedResponse,
  Tour,
  TourDate,
  TourDatePayload,
  TourImage,
  TourImagePayload,
  TourPayload,
  TourRoutePoint,
  TourRoutePointPayload,
} from '@/types/api';

function token() {
  return authService.getToken();
}

interface ResourceResponse<T> {
  data: T;
}

function appendValue(formData: FormData, key: string, value: boolean | number | string) {
  formData.append(key, typeof value === 'boolean' ? (value ? '1' : '0') : String(value));
}

function appendOptionalString(formData: FormData, key: string, value?: string | null) {
  if (value === undefined || value === null || value === '') {
    return;
  }

  formData.append(key, value);
}

function buildTourFormData(payload: Partial<TourPayload>, method?: 'PUT') {
  const formData = new FormData();

  if (method === 'PUT') {
    formData.append('_method', 'PUT');
  }

  if (payload.title !== undefined && payload.title !== null) {
    appendValue(formData, 'title', payload.title);
  }

  if (payload.short_description !== undefined && payload.short_description !== null) {
    appendValue(formData, 'short_description', payload.short_description);
  }

  if (payload.description !== undefined && payload.description !== null) {
    appendValue(formData, 'description', payload.description);
  }

  if (payload.duration_days !== undefined && payload.duration_days !== null) {
    appendValue(formData, 'duration_days', payload.duration_days);
  }

  if (payload.category !== undefined && payload.category !== null) {
    appendValue(formData, 'category', payload.category);
  }

  if (payload.is_active !== undefined) {
    appendValue(formData, 'is_active', payload.is_active);
  }

  appendOptionalString(formData, 'main_image', payload.main_image);

  if (payload.main_image_file) {
    formData.append('main_image_file', payload.main_image_file);
  }

  if (payload.remove_main_image) {
    formData.append('remove_main_image', '1');
  }

  return formData;
}

function buildTourImageFormData(payload: TourImagePayload) {
  const formData = new FormData();

  appendOptionalString(formData, 'image_url', payload.image_url);

  if (payload.image_file) {
    formData.append('image_file', payload.image_file);
  }

  appendOptionalString(formData, 'alt_text', payload.alt_text);

  if (payload.sort_order !== undefined) {
    appendValue(formData, 'sort_order', payload.sort_order);
  }

  return formData;
}

export const adminTourService = {
  getTours(params: Record<string, unknown> = { per_page: 50 }) {
    return apiClient.get<PaginatedResponse<Tour>>('/admin/tours', params, token());
  },

  async getTour(id: number | string) {
    const response = await apiClient.get<ResourceResponse<Tour>>(`/admin/tours/${id}`, undefined, token());

    return response.data;
  },

  async createTour(payload: TourPayload) {
    const response = await apiClient.post<ResourceResponse<Tour>>('/admin/tours', buildTourFormData(payload), token());

    return response.data;
  },

  async updateTour(id: number | string, payload: Partial<TourPayload>) {
    const response = await apiClient.post<ResourceResponse<Tour>>(`/admin/tours/${id}`, buildTourFormData(payload, 'PUT'), token());

    return response.data;
  },

  deleteTour(id: number | string) {
    return apiClient.delete<void>(`/admin/tours/${id}`, token());
  },

  async addImage(tourId: number | string, payload: TourImagePayload) {
    const response = await apiClient.post<ResourceResponse<TourImage>>(`/admin/tours/${tourId}/images`, buildTourImageFormData(payload), token());

    return response.data;
  },

  deleteImage(imageId: number | string) {
    return apiClient.delete<void>(`/admin/tour-images/${imageId}`, token());
  },

  async addDate(tourId: number | string, payload: TourDatePayload) {
    const response = await apiClient.post<ResourceResponse<TourDate>>(`/admin/tours/${tourId}/dates`, payload, token());

    return response.data;
  },

  async updateDate(dateId: number | string, payload: Partial<TourDatePayload>) {
    const response = await apiClient.put<ResourceResponse<TourDate>>(`/admin/tour-dates/${dateId}`, payload, token());

    return response.data;
  },

  deleteDate(dateId: number | string) {
    return apiClient.delete<void>(`/admin/tour-dates/${dateId}`, token());
  },

  async addRoutePoint(tourId: number | string, payload: TourRoutePointPayload) {
    const response = await apiClient.post<ResourceResponse<TourRoutePoint>>(`/admin/tours/${tourId}/route-points`, payload, token());

    return response.data;
  },

  async updateRoutePoint(routePointId: number | string, payload: Partial<TourRoutePointPayload>) {
    const response = await apiClient.put<ResourceResponse<TourRoutePoint>>(`/admin/tour-route-points/${routePointId}`, payload, token());

    return response.data;
  },

  deleteRoutePoint(routePointId: number | string) {
    return apiClient.delete<void>(`/admin/tour-route-points/${routePointId}`, token());
  },
};
