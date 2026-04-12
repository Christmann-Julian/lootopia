export type RegisterFormData = {
  firstname: string;
  lastname: string;
  pseudo: string;
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
  pseudo: string;
  email: string;
  company?: string;
  roles: string[];
  isVerified: boolean;
};

export type EditSettingsFormData = {
  id: number;
  firstname: string;
  lastname: string;
  pseudo: string;
  email: string;
  company?: string;
};

export type CreateUserFormData = {
  firstname: string;
  lastname: string;
  pseudo: string;
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

export interface CreateBadgeFormData {
  icon: string;
  translations: {
    fr: string;
    en: string;
  };
}

export interface EditBadgeFormData {
  id?: number;
  icon: string;
  translations: {
    fr: string;
    en: string;
  };
}

export interface CreateCategoryFormData {
  icon: string;
  translations: {
    fr: string;
    en: string;
  };
}

export interface EditCategoryFormData {
  id?: number;
  icon: string;
  translations: {
    fr: string;
    en: string;
  };
}

export interface CreateRankFormData {
  level: number;
  experienceMin: number;
  experienceMax: number;
  translations: {
    fr: string;
    en: string;
  };
}

export interface EditRankFormData {
  id?: number;
  level: number;
  experienceMin: number;
  experienceMax: number;
  translations: {
    fr: string;
    en: string;
  };
}

export interface CreateRarityFormData {
  minExperience: number;
  experienceGain: number;
  translations: {
    fr: string;
    en: string;
  };
}

export interface EditRarityFormData {
  id?: number;
  minExperience: number;
  experienceGain: number;
  translations: {
    fr: string;
    en: string;
  };
}

export interface EditRewardFormData {
  id?: number;
  code: string;
  link: string;
  endDate: string;
  translations: {
    fr: string;
    en: string;
  };
}

export interface CreateHuntFormData {
  lat: number;
  lon: number;
  categoryId?: number;
  rarityId: number;
  translations: {
    fr: { title: string; description: string; question: string; answer: string; location: string };
    en: { title: string; description: string; question: string; answer: string; location: string };
  };
  reward: {
    code: string;
    link: string;
    endDate: string;
    translations: {
      fr: string;
      en: string;
    };
  };
}

export interface EditHuntFormData {
  id?: number;
  lat: number;
  lon: number;
  categoryId?: number;
  rarityId: number;
  translations: {
    fr: { title: string; description: string; question: string; answer: string; location: string };
    en: { title: string; description: string; question: string; answer: string; location: string };
  };
}
