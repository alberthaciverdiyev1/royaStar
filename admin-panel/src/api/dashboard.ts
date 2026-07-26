import api from './client';
import type { ApiResponse } from './types';

export interface DashboardStats {
  cities: number;
  grades: number;
  lessons: number;
  students: number;
  teachers: number;
  users: number;
}

export const dashboardApi = {
  stats: () =>
    api.get<ApiResponse<DashboardStats>>('/admin/dashboard/stats').then((r) => r.data),
};
