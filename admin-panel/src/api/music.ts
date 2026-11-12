import api from './client'
import type { ApiResponse, PaginatedResponse } from './types'

export interface MusicTrack {
  id: number
  title: string
  artist: string | null
  url: string
  cover: string | null
  thumbnail: string | null
  source_type: 'youtube' | 'audio'
  youtube_id: string | null
  is_active: boolean
  sort_order: number
  created_at: string
  updated_at: string
}

export interface MusicTrackFormData {
  title: string
  artist?: string | null
  url: string
  cover?: string | null
  is_active?: boolean
  sort_order?: number
}

export interface MusicPageOption {
  key: string
  label: string
}

export interface MusicSettings {
  play_on_all: boolean
  pages: string[]
  page_options: MusicPageOption[]
}

export interface MusicMetadata {
  title: string | null
  artist: string | null
  thumbnail: string | null
  youtube_id: string | null
  source_type: 'youtube' | 'audio'
}

export const musicApi = {
  list: (params?: { search?: string; is_active?: number; page?: number; per_page?: number }) =>
    api.get<PaginatedResponse<MusicTrack>>('/admin/music', { params }).then((r) => r.data),

  show: (id: number) =>
    api.get<ApiResponse<MusicTrack>>(`/admin/music/${id}`).then((r) => r.data),

  create: (data: MusicTrackFormData) =>
    api.post<ApiResponse<MusicTrack>>('/admin/music', data).then((r) => r.data),

  update: (id: number, data: MusicTrackFormData) =>
    api.put<ApiResponse<MusicTrack>>(`/admin/music/${id}`, data).then((r) => r.data),

  remove: (id: number) =>
    api.delete<ApiResponse<null>>(`/admin/music/${id}`).then((r) => r.data),

  getSettings: () =>
    api.get<ApiResponse<MusicSettings>>('/admin/music/settings').then((r) => r.data),

  updateSettings: (data: { play_on_all: boolean; pages: string[] }) =>
    api.put<ApiResponse<MusicSettings>>('/admin/music/settings', data).then((r) => r.data),

  metadata: (url: string) =>
    api.get<ApiResponse<MusicMetadata>>('/admin/music/metadata', { params: { url } }).then((r) => r.data),
}
