import api from './client';
import type { ApiResponse, PaginatedResponse } from './types';

export interface Question {
  id: number;
  lesson_id: number;
  lesson_name: string | null;
  topic_id: number | null;
  type: 'regular' | 'open';
  answer_type: string | null;
  right_answer: string | null;
  difficulty_level: number;
  created_at: string;
  question: ContentBlocks | null;
  variant_a: ContentBlocks | null;
  variant_b: ContentBlocks | null;
  variant_c: ContentBlocks | null;
  variant_d: ContentBlocks | null;
  variant_e: ContentBlocks | null;
  open_answer: ContentBlocks | null;
  explanation: ContentBlocks | null;
  explanation_video_url: string | null;
}

/** A content-block array, or a locale-keyed map of them (e.g. { az: [...], en: [...] }). */
export type ContentBlocks = { type: string; content: string }[] | Record<string, { type: string; content: string }[]>;

/** Flatten content to a plain block array regardless of storage shape. */
export function flattenBlocks(blocks: ContentBlocks | null | undefined): { type: string; content: string }[] {
  if (!blocks) return [];
  if (Array.isArray(blocks)) return blocks;
  return blocks.az ?? blocks.en ?? blocks.ru ?? [];
}

export interface QuestionFormData {
  question: { type: string; content: string }[];
  type: 'regular' | 'open';
  lesson_id: number;
  difficulty_level: number;
  variant_a?: { type: string; content: string }[];
  variant_b?: { type: string; content: string }[];
  variant_c?: { type: string; content: string }[];
  variant_d?: { type: string; content: string }[];
  variant_e?: { type: string; content: string }[];
  right_answer?: string;
  open_answer?: { type: string; content: string }[];
  answer_type?: string;
  explanation?: { type: string; content: string }[];
  explanation_video_url?: string | null;
}

export function toContentBlock(text: string): { type: string; content: string }[] {
  return text ? [{ type: 'text', content: text }] : [];
}

export function fromContentBlock(blocks: { type: string; content: string }[] | null): string {
  return blocks?.[0]?.content || '';
}

export const questionsApi = {
  list: (params?: {
    search?: string;
    page?: number;
    per_page?: number;
    lesson_id?: number;
    lesson_ids?: string;
    type?: string;
    difficulty_level?: number;
  }) =>
    api.get<PaginatedResponse<Question>>('/admin/questions', { params }).then((r) => r.data),

  show: (id: number) =>
    api.get<ApiResponse<Question>>(`/admin/questions/${id}`).then((r) => r.data),

  create: (data: QuestionFormData) =>
    api.post<ApiResponse<Question>>('/admin/questions', data).then((r) => r.data),

  update: (id: number, data: Partial<QuestionFormData>) =>
    api.put<ApiResponse<Question>>(`/admin/questions/${id}`, data).then((r) => r.data),

  delete: (id: number) =>
    api.delete<ApiResponse<null>>(`/admin/questions/${id}`).then((r) => r.data),
};
