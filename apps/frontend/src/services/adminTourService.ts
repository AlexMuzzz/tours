import { apiClient } from '@/services/apiClient';
import { authService } from '@/services/authService';
import type {
  GeneratedDescriptionResponse,
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

export const adminTourService = {
  getTours(params: Record<string, unknown> = { per_page: 50 }) {
    return apiClient.get<PaginatedResponse<Tour>>('/admin/tours', params, token());
  },

  async getTour(id: number | string) {
    const response = await apiClient.get<ResourceResponse<Tour>>(`/admin/tours/${id}`, undefined, token());

    return response.data;
  },

  async createTour(payload: TourPayload) {
    const response = await apiClient.post<ResourceResponse<Tour>>('/admin/tours', payload, token());

    return response.data;
  },

  async updateTour(id: number | string, payload: Partial<TourPayload>) {
    const response = await apiClient.put<ResourceResponse<Tour>>(`/admin/tours/${id}`, payload, token());

    return response.data;
  },

  deleteTour(id: number | string) {
    return apiClient.delete<void>(`/admin/tours/${id}`, token());
  },

  async addImage(tourId: number | string, payload: TourImagePayload) {
    const response = await apiClient.post<ResourceResponse<TourImage>>(`/admin/tours/${tourId}/images`, payload, token());

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

  generateDescription(tourId: number | string) {
    return apiClient.post<GeneratedDescriptionResponse>(
      `/admin/tours/${tourId}/generate-description`,
      undefined,
      token(),
    );
  },
};
