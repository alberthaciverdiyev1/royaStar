import api from './client';
import type { ApiResponse, PaginatedResponse } from './types';
import type { Question } from './questions';
import type { Grade } from './grades';

export interface Exam {
  id: number;
  name: string;
  description: string | null;
  grade_id: number;
  duration_minutes: number;
  passing_score: number;
  total_questions: number;
  type: string;
  created_at: string;
  grade?: Grade;
  questions?: Question[];
}

export interface ExamFormData {
  name: string;
  description?: string;
  grade_id: number;
  duration_minutes: number;
  passing_score: number;
  type: string;
  question_ids?: number[];
}

export const examsApi = {
  list: (params?: {
    search?: string;
    page?: number;
    per_page?: number;
    grade_id?: number;
    type?: string;
  }) =>
    api.get<PaginatedResponse<Exam>>('/exams', { params }).then((r) => r.data),

  show: (id: number) =>
    api.get<ApiResponse<Exam>>(`/exams/${id}`).then((r) => r.data),

  create: (data: ExamFormData) =>
    api.post<ApiResponse<Exam>>('/admin/exams', data).then((r) => r.data),

  update: (id: number, data: Partial<ExamFormData>) =>
    api.put<ApiResponse<Exam>>(`/admin/exams/${id}`, data).then((r) => r.data),

  delete: (id: number) =>
    api.delete<ApiResponse<null>>(`/admin/exams/${id}`).then((r) => r.data),
};
