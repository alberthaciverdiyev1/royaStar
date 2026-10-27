import api from './client'
import type { ApiResponse, PaginatedResponse } from './types'

export interface Popup {
  id: number
  title: string | null
  content: string | null
  image: string | null
  is_active: boolean
  created_at: string
  updated_at: string
}

export interface PopupFormData {
  title?: string | null
  content?: string | null
  image?: string | null
  is_active?: boolean
}

export const popupsApi = {
  list: (params?: { search?: string; is_active?: number; page?: number; per_page?: number }) =>
    api.get<PaginatedResponse<Popup>>('/admin/popups', { params }).then((r) => r.data),

  show: (id: number) =>
    api.get<ApiResponse<Popup>>(`/admin/popups/${id}`).then((r) => r.data),

  create: (data: PopupFormData) =>
    api.post<ApiResponse<Popup>>('/admin/popups', data).then((r) => r.data),

  update: (id: number, data: PopupFormData) =>
    api.put<ApiResponse<Popup>>(`/admin/popups/${id}`, data).then((r) => r.data),

  remove: (id: number) =>
    api.delete<ApiResponse<null>>(`/admin/popups/${id}`).then((r) => r.data),
}
