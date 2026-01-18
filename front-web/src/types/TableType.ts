import type { JSX } from "react/jsx-dev-runtime";

export type MetaData = {
  page: number;
  limit: number;
  total: number;
  sort: string;
  direction: "asc" | "desc";
};

export type Column<T = Record<string, unknown>> = {
  key: string;
  label: string;
  sortable?: boolean;
  render?: (value: T[keyof T], row: T) => JSX.Element | string;
};

export type TableRow = {
  id: number;
  [key: string]: unknown;
};

export type TableProps = {
  title: string;
  columns: Column[];
  apiEndpoint: string;
};
