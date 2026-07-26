export interface ApiResponse<T> {
  success: boolean;
  status_code: number;
  message: string;
  data: T;
  errors?: Record<string, string[]>;
}

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

export interface PaginatedResponse<T> {
  success: boolean;
  status_code: number;
  message: string;
  data: T[];
  meta: PaginationMeta;
}
