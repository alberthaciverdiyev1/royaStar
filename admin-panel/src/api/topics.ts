import api from './client';
import type { ApiResponse, PaginatedResponse } from './types';

export interface Topic {
  id: number;
  subject_id: number;
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
  list: (subjectId: number, params?: { search?: string; page?: number; per_page?: number }) =>
    api.get<PaginatedResponse<Topic>>(`/subjects/${subjectId}/topics`, { params }).then((r) => r.data),

  show: (subjectId: number, topicId: number) =>
    api.get<ApiResponse<Topic>>(`/subjects/${subjectId}/topics/${topicId}`).then((r) => r.data),

  create: (subjectId: number, data: TopicFormData) =>
    api.post<ApiResponse<Topic>>(`/admin/subjects/${subjectId}/topics`, data).then((r) => r.data),

  update: (subjectId: number, topicId: number, data: TopicFormData) =>
    api.put<ApiResponse<Topic>>(`/admin/subjects/${subjectId}/topics/${topicId}`, data).then((r) => r.data),

  delete: (subjectId: number, topicId: number) =>
    api.delete<ApiResponse<null>>(`/admin/subjects/${subjectId}/topics/${topicId}`).then((r) => r.data),
};
