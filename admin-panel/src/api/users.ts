import api from './client';
import type { ApiResponse, PaginatedResponse } from './types';

export interface UserStudentInfo {
  id: number;
  grade: number | null;
  school: string | null;
  city: string | null;
}

export interface User {
  id: number;
  name: string;
  surname: string | null;
  phone: string;
  email: string;
  avatar: string | null;
  type: string;
  is_approved: boolean;
  total_stars?: number;
  student?: UserStudentInfo | null;
  created_at: string;
}

export const usersApi = {
  list: (params?: { page?: number; per_page?: number; search?: string; status?: string }) =>
    api.get<PaginatedResponse<User>>('/admin/users', { params }),

  getDetails: (id: number) =>
    api.get<ApiResponse<User>>(`/admin/users/${id}`),

  pending: (params?: { page?: number; per_page?: number }) =>
    api.get<PaginatedResponse<User>>('/admin/users/pending', { params }),

  approve: (id: number) =>
    api.post(`/admin/users/${id}/approve`),

  changePassword: (id: number, password: string) =>
    api.post(`/admin/users/${id}/password`, { password }),
};
