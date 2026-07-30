import api from './client';

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  avatar: string | null;
  type: string;
  roles: string[];
}

export interface LoginResponse {
  success: boolean;
  status_code: number;
  message: string;
  data: {
    user: User;
    token: string;
  };
}

export const authApi = {
  login: (credentials: LoginCredentials) =>
    api.post<LoginResponse>('/auth/admin-login', credentials),

  logout: () => api.post('/auth/logout'),

  me: () => api.get('/profile'),
};
