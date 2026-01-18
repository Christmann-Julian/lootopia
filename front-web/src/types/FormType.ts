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

export type EditUserFormData = {
  id: number;
  firstname: string;
  lastname: string;
  email: string;
  company?: string;
  roles: string[];
  isVerified: boolean;
};

export type EditSettingsFormData = {
  id: number;
  firstname: string;
  lastname: string;
  email: string;
  company?: string;
};

export type CreateUserFormData = {
  firstname: string;
  lastname: string;
  email: string;
  company?: string;
  roles: string[];
  password: string;
  confirmPassword: string;
  isVerified: boolean;
};

export type ChangePasswordFormData = {
  currentPassword: string;
  newPassword: string;
};
