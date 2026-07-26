import api from './client';
import type { ApiResponse, PaginatedResponse } from './types';

export interface Subject {
  id: number;
  name: string;
  image: string | null;
  created_at: string;
}

export interface SubjectFormData {
  name: string;
}

export const subjectsApi = {
  list: (params?: { search?: string; page?: number; per_page?: number }) =>
    api.get<PaginatedResponse<Subject>>('/subjects', { params }).then((r) => r.data),

  show: (id: number) => api.get<ApiResponse<Subject>>(`/subjects/${id}`).then((r) => r.data),

  create: (data: SubjectFormData) =>
    api.post<ApiResponse<Subject>>('/admin/subjects', data).then((r) => r.data),

  update: (id: number, data: SubjectFormData) =>
    api.put<ApiResponse<Subject>>(`/admin/subjects/${id}`, data).then((r) => r.data),

  delete: (id: number) =>
    api.delete<ApiResponse<null>>(`/admin/subjects/${id}`).then((r) => r.data),
};
