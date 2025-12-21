export type RegisterFormData = {
  firstname: string;
  lastname: string;
  email: string;
  company?: string;
  role: string;
  password: string;
  confirmPassword: string;
  terms: boolean;
};
