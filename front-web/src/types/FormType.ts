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

export type ForgotPasswordFormData = {
  email: string;
};

export type ResetPasswordFormData = {
  password: string;
  confirmPassword: string;
};

export type LoginFormData = {
  email: string;
  password: string;
};
