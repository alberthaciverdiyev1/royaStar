import api from './client'
import type { ApiResponse, PaginatedResponse } from './types'

export type NotificationType = 'lesson_video' | 'quiz' | 'exam'

export interface AdminNotification {
  id: number
  type: NotificationType | string
  title: string
  body: string | null
  url: string | null
  recipients_count: number
  read_count: number
  created_at: string | null
}

export interface NotificationSettings {
  notify_lesson_video: boolean
  notify_quiz: boolean
  notify_exam: boolean
}

export const notificationsApi = {
  list: (params?: { type?: NotificationType; page?: number; per_page?: number }) =>
    api.get<PaginatedResponse<AdminNotification>>('/admin/notifications', { params }).then((r) => r.data),

  remove: (id: number) =>
    api.delete<ApiResponse<null>>(`/admin/notifications/${id}`).then((r) => r.data),

  getSettings: () =>
    api.get<ApiResponse<NotificationSettings>>('/admin/notification-settings').then((r) => r.data),

  updateSettings: (data: Partial<NotificationSettings>) =>
    api
      .put<ApiResponse<NotificationSettings>>('/admin/notification-settings', data)
      .then((r) => r.data),
}
