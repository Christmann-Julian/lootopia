export type TranslatedItem = {
  name?: string;
  translations?: Record<string, string>;
};

export type UserProfileData = {
  id: number;
  email: string;
  firstname: string;
  lastname: string;
  pseudo: string;
  company?: string | null;
  experience: number;
  huntCount: number;
  rewardCount: number;
  rank?: {
    id: number;
    level: number;
    experienceMin: number;
    experienceMax: number;
  } & TranslatedItem;
  badges: Array<
    {
      id: number;
      icon: string;
    } & TranslatedItem
  >;
};
