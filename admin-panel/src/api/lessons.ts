import api from './client';
import type { ApiResponse, PaginatedResponse } from './types';

export interface Video {
  id: number;
  lesson_id: number;
  name: string | null;
  youtube_url: string;
  embed_url: string | null;
  created_at: string;
}

export interface Lesson {
  id: number;
  topic_id: number;
  name: string;
  description: string | null;
  view_count: number;
  created_at: string;
  videos?: Video[];
}

export interface LessonFormData {
  name: string;
  description?: string;
  videos?: { youtube_url: string; name?: string }[];
}

export const lessonsApi = {
  list: (topicId: number, params?: { search?: string; page?: number; per_page?: number }) =>
    api.get<PaginatedResponse<Lesson>>(`/topics/${topicId}/lessons`, { params }).then((r) => r.data),

  show: (topicId: number, lessonId: number) =>
    api.get<ApiResponse<Lesson>>(`/topics/${topicId}/lessons/${lessonId}`).then((r) => r.data),

  create: (topicId: number, data: LessonFormData) =>
    api.post<ApiResponse<Lesson>>(`/admin/topics/${topicId}/lessons`, data).then((r) => r.data),

  update: (topicId: number, lessonId: number, data: LessonFormData) =>
    api.put<ApiResponse<Lesson>>(`/admin/topics/${topicId}/lessons/${lessonId}`, data).then((r) => r.data),

  delete: (topicId: number, lessonId: number) =>
    api.delete<ApiResponse<null>>(`/admin/topics/${topicId}/lessons/${lessonId}`).then((r) => r.data),
};
