import api from './client'
import type { ApiResponse, PaginatedResponse } from './types'

export interface AcceptedStudent {
  id: number
  user_id: number | null
  name: string
  surname: string | null
  image: string | null
  exam_points: number
  is_active: boolean
  created_at: string
}

export interface AcceptedStudentFormData {
  user_id?: number | null
  name?: string
  surname?: string
  image?: string | null
  exam_points?: number | null
  is_active?: boolean
}

export interface PickableStudent {
  id: number
  user_id: number
  name: string
  surname: string | null
  email: string
  avatar: string | null
  grade?: { id: number; name: string } | null
}

export const acceptedStudentsApi = {
  list: (params?: { search?: string; is_active?: number; page?: number; per_page?: number }) =>
    api.get<PaginatedResponse<AcceptedStudent>>('/admin/accepted-students', { params }).then((r) => r.data),

  show: (id: number) =>
    api.get<ApiResponse<AcceptedStudent>>(`/admin/accepted-students/${id}`).then((r) => r.data),

  create: (data: AcceptedStudentFormData) =>
    api.post<ApiResponse<AcceptedStudent>>('/admin/accepted-students', data).then((r) => r.data),

  update: (id: number, data: AcceptedStudentFormData) =>
    api.put<ApiResponse<AcceptedStudent>>(`/admin/accepted-students/${id}`, data).then((r) => r.data),

  remove: (id: number) =>
    api.delete<ApiResponse<null>>(`/admin/accepted-students/${id}`).then((r) => r.data),
}
