export type ApiErrorResponse = {
  code: number;
  message: string;
  details: {
    [key: string]: string[];
  } | null;
};
