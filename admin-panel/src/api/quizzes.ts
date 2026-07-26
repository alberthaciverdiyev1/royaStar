import api from './client';
import type { ApiResponse, PaginatedResponse } from './types';
import type { Question } from './questions';

export interface Quiz {
  id: number;
  type: string;
  lesson_id: number;
  name: string;
  created_at: string;
  lesson?: { id: number; name: string; topic_id: number };
  questions?: Question[];
}

export interface QuizFormData {
  name: string;
  type: string;
  lesson_id: number;
  question_ids?: number[];
}

export const quizzesApi = {
  list: (params?: {
    search?: string;
    page?: number;
    per_page?: number;
    lesson_id?: number;
    type?: string;
  }) =>
    api.get<PaginatedResponse<Quiz>>('/quizzes', { params }).then((r) => r.data),

  show: (id: number) =>
    api.get<ApiResponse<Quiz>>(`/quizzes/${id}`).then((r) => r.data),

  create: (data: QuizFormData) =>
    api.post<ApiResponse<Quiz>>('/admin/quizzes', data).then((r) => r.data),

  update: (id: number, data: Partial<QuizFormData>) =>
    api.put<ApiResponse<Quiz>>(`/admin/quizzes/${id}`, data).then((r) => r.data),

  delete: (id: number) =>
    api.delete<ApiResponse<null>>(`/admin/quizzes/${id}`).then((r) => r.data),
};
