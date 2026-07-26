import api from './client';
import type { ApiResponse, PaginatedResponse } from './types';

export interface Grade {
  id: number;
  name: string;
  created_at: string;
}

export interface GradeFormData {
  name: string;
}

export const gradesApi = {
  list: (params?: { search?: string; page?: number; per_page?: number }) =>
    api.get<PaginatedResponse<Grade>>('/grades', { params }).then((r) => r.data),

  all: () => api.get<ApiResponse<Grade[]>>('/grades?all=true').then((r) => r.data),

  show: (id: number) => api.get<ApiResponse<Grade>>(`/grades/${id}`).then((r) => r.data),

  create: (data: GradeFormData) =>
    api.post<ApiResponse<Grade>>('/admin/grades', data).then((r) => r.data),

  update: (id: number, data: GradeFormData) =>
    api.put<ApiResponse<Grade>>(`/admin/grades/${id}`, data).then((r) => r.data),

  delete: (id: number) =>
    api.delete<ApiResponse<null>>(`/admin/grades/${id}`).then((r) => r.data),
};
