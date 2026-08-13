import api from './client';
import type { ApiResponse } from './types';

export interface WebsiteTextItem {
  key: string;
  fallback: string;
  value: string | null;
}

export interface WebsiteTextGroup {
  key: string;
  label: string;
  icon: string;
  items: WebsiteTextItem[];
}

export interface WebsiteTextDraft {
  [key: string]: string;
}

export const websiteTextsApi = {
  /** GET /api/admin/website-texts — grouped registry + stored overrides */
  list: () => api.get<ApiResponse<WebsiteTextGroup[]>>('/admin/website-texts').then((r) => r.data),

  /** PUT /api/admin/website-texts — save overrides (flushes cache server-side) */
  update: (texts: WebsiteTextDraft) =>
    api.put<ApiResponse<WebsiteTextDraft>>('/admin/website-texts', { texts }).then((r) => r.data),
};
