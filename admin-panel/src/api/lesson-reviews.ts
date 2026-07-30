import api from './client';
import type { PaginatedResponse } from './types';

export interface LessonReview {
  id: number;
  rating: number | null;
  review: string | null;
  user: {
    id: number;
    name: string;
    email: string;
    avatar: string | null;
  };
  lesson: {
    id: number;
    name: string;
  };
  created_at: string;
}

export const lessonReviewsApi = {
  list: (params?: { page?: number; per_page?: number }) =>
    api.get<PaginatedResponse<LessonReview>>('/admin/lesson-reviews', { params }).then((r) => r.data),
};
