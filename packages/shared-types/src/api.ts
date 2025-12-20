/**
 * API contract types
 */

export type ApiResponse<T = unknown> = {
  data: T;
  message?: string;
  errors?: ApiError[];
  meta?: ApiMeta;
};

export type ApiError = {
  field?: string;
  message: string;
  code?: string;
};

export type ApiMeta = {
  page?: number;
  perPage?: number;
  total?: number;
  lastPage?: number;
};

export type PaginatedResponse<T> = ApiResponse<T[]> & {
  meta: Required<ApiMeta>;
};

export type ApiRequest<T = unknown> = {
  data?: T;
  params?: Record<string, string | number>;
  query?: Record<string, string | number | boolean>;
};

