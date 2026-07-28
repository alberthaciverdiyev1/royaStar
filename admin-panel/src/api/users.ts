import api from './client';
import type { PaginatedResponse } from './types';

export interface User {
  id: number;
  name: string;
  surname: string | null;
  phone: string;
  email: string;
  type: string;
  is_approved: boolean;
  created_at: string;
}

export const usersApi = {
  pending: (params?: { page?: number; per_page?: number }) =>
    api.get<PaginatedResponse<User>>('/admin/users/pending', { params }),

  approve: (id: number) =>
    api.post(`/admin/users/${id}/approve`),
};
