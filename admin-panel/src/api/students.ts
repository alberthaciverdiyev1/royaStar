import api from './client';
import type { ApiResponse, PaginatedResponse } from './types';

export interface StudentGrade {
  id: number;
  name: string;
}

export interface StudentCity {
  id: number;
  name: string;
}

export interface StudentUser {
  id: number;
  name: string;
  surname: string | null;
  phone: string;
  email: string;
  avatar: string | null;
  is_approved: boolean;
}

export interface Student {
  id: number;
  user_id: number;
  grade_id: number | null;
  city_id: number | null;
  school_name: string | null;
  birth_date: string | null;
  is_active: boolean;
  level: number;
  created_at: string;
  user?: StudentUser | null;
  grade?: StudentGrade | null;
  city?: StudentCity | null;
}

export interface StudentFormData {
  grade_id?: number | null;
  city_id?: number | null;
  school_name?: string | null;
  birth_date?: string | null;
}

export const studentsApi = {
  list: (params?: { search?: string; grade_id?: number; page?: number; per_page?: number }) =>
    api.get<PaginatedResponse<Student>>('/students', { params }).then((r) => r.data),

  show: (id: number) =>
    api.get<ApiResponse<Student>>(`/students/${id}`).then((r) => r.data),

  update: (id: number, data: StudentFormData) =>
    api.put<ApiResponse<Student>>(`/admin/students/${id}`, data).then((r) => r.data),

  remove: (id: number) =>
    api.delete<ApiResponse<null>>(`/admin/students/${id}`).then((r) => r.data),
};
