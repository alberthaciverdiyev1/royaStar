import api from './client';
import type { ApiResponse, PaginatedResponse } from './types';

export interface Topic {
  id: number;
  name: string;
  difficulty_level: number;
  difficulty_label: string;
  created_at: string;
  grades?: { id: number; name: string }[];
}

export interface TopicFormData {
  name: string;
  difficulty_level: number;
  grade_ids?: number[];
}

export const topicsApi = {
  list: (params?: { search?: string; page?: number; per_page?: number }) =>
    api.get<PaginatedResponse<Topic>>('/topics', { params }).then((r) => r.data),

  show: (topicId: number) =>
    api.get<ApiResponse<Topic>>(`/topics/${topicId}`).then((r) => r.data),

  create: (data: TopicFormData) =>
    api.post<ApiResponse<Topic>>('/admin/topics', data).then((r) => r.data),

  update: (topicId: number, data: TopicFormData) =>
    api.put<ApiResponse<Topic>>(`/admin/topics/${topicId}`, data).then((r) => r.data),

  delete: (topicId: number) =>
    api.delete<ApiResponse<null>>(`/admin/topics/${topicId}`).then((r) => r.data),
};
