import api from './client';
import type { ApiResponse } from './types';

export interface Star {
  id: number;
  type: string;
  name: string | null;
  description: string | null;
  icon: string | null;
  category: string | null;
  group: string | null;
  point: number;
  point_min: number;
  point_max: number | null;
  point_default: number;
  is_active: boolean;
  max_per_day: number | null;
  sort_order: number;
  created_at: string;
}

export interface StarUpdateData {
  point: number;
  is_active?: boolean;
}

export const starsApi = {
  list: () =>
    api.get<ApiResponse<Star[]>>('/stars').then((r) => r.data),

  show: (id: number) =>
    api.get<ApiResponse<Star>>(`/stars/${id}`).then((r) => r.data),

  update: (id: number, data: StarUpdateData) =>
    api.put<ApiResponse<Star>>(`/admin/stars/${id}`, data).then((r) => r.data),
};

export const CATEGORY_LABELS: Record<string, string> = {
  engagement: 'Nişan',
  learning: 'Öyrənmə',
  achievement: 'Nailiyyət',
};

export const CATEGORY_COLORS: Record<string, string> = {
  engagement: 'bg-purple-50 text-purple-700 border-purple-200',
  learning: 'bg-blue-50 text-blue-700 border-blue-200',
  achievement: 'bg-amber-50 text-amber-700 border-amber-200',
};

export const GROUP_LABELS: Record<string, string> = {
  daily: 'Gündəlik',
  lesson: 'Dərs',
  quiz: 'Test',
  exam: 'İmtahan',
  social: 'Sosial',
  streak: 'Ardıcıllıq',
  onboarding: 'Başlanğıc',
};
