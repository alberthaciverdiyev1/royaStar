import api from './client';
import type { ApiResponse, PaginatedResponse } from './types';

export interface City {
  id: number;
  name: string;
  created_at: string;
}

export interface CityFormData {
  name: string;
}

export const citiesApi = {
  list: (params?: { search?: string; page?: number; per_page?: number }) =>
    api.get<PaginatedResponse<City>>('/cities', { params }).then((r) => r.data),

  all: () => api.get<ApiResponse<City[]>>('/cities?all=true').then((r) => r.data),

  show: (id: number) => api.get<ApiResponse<City>>(`/cities/${id}`).then((r) => r.data),

  create: (data: CityFormData) =>
    api.post<ApiResponse<City>>('/admin/cities', data).then((r) => r.data),

  update: (id: number, data: CityFormData) =>
    api.put<ApiResponse<City>>(`/admin/cities/${id}`, data).then((r) => r.data),

  delete: (id: number) =>
    api.delete<ApiResponse<null>>(`/admin/cities/${id}`).then((r) => r.data),
};
