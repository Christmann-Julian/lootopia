export type RegisterFormInputs = {
  firstname: string;
  lastname: string;
  pseudo: string;
  email: string;
  password: string;
  confirmPassword: string;
};

export type PersonalInfoForm = {
  firstname: string;
  lastname: string;
  pseudo: string;
  email: string;
};

export type SecurityForm = {
  currentPassword?: string;
  newPassword?: string;
};
