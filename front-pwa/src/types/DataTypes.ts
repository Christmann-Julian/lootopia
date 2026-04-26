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

export type CategoryData = {
  id: number;
  icon: string;
  name: string;
};

export type HuntData = {
  id: number;
  title: string;
  company: string;
  location: string;
  category: CategoryData;
  rarity: { name: string };
  reward: { title: string };
};

export type UserStatsData = {
  experience: number;
  level: number | string;
};

export type RewardData = {
  id: number;
  code: string;
  link: string;
  endDate: string;
  title?: string;
  company?: string;
  category?: string;
  rarity?: string;
};

export type UserPseudoData = {
  pseudo: string;
};

export type RankData = {
  name: string;
  level: number;
  experienceMin: number;
  experienceMax: number;
  translations?: Record<string, string>;
};

export type UserHomeData = {
  pseudo: string;
  experience: number;
  level: number | string;
  rankName?: string;
  rank?: RankData;
};

export type RarityData = {
  name: string;
  minExperience: number;
};

export type HuntSponsoredData = {
  id: number;
  title: string;
  description: string;
  company: string;
  location: string;
  category: CategoryData;
  rarity: RarityData;
  reward?: { title: string };
};
