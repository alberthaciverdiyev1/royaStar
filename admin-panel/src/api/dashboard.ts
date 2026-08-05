import api from './client';
import type { ApiResponse } from './types';

export interface WeeklySignup {
  date: string;
  count: number;
}

export interface UserTypeDist {
  type: string;
  count: number;
}

export interface RecentUser {
  id: number;
  name: string;
  email: string;
  type: string;
  created_at: string;
}

export interface TopStudent {
  id: number;
  name: string;
  email: string;
  avatar: string | null;
  total_points: number;
}

export interface DashboardStats {
  cities: number;
  grades: number;
  topics: number;
  lessons: number;
  questions: number;
  quizzes: number;
  exams: number;
  students: number;
  users: number;
  pending_users: number;
  total_quiz_attempts: number;
  total_exam_attempts: number;
  total_reviews: number;
  average_rating: number;
  total_xp: number;
  new_users_today: number;
  new_users_week: number;
  new_users_month: number;
  weekly_signups: WeeklySignup[];
  user_type_distribution: UserTypeDist[];
  recent_users: RecentUser[];
  top_students: TopStudent[];
}

export const dashboardApi = {
  stats: () =>
    api.get<ApiResponse<DashboardStats>>('/admin/dashboard/stats').then((r) => r.data),
};
