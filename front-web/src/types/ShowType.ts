export type UserShowData = {
  id: number;
  firstname: string;
  lastname: string;
  pseudo: string;
  email: string;
  company?: string;
  rank?: {
    level: number;
    experienceMin: number;
    experienceMax: number;
    translations: {
      [lang: string]: {
        name: string;
      };
    };
  };
  badges?: {
    icon: string;
    translations?: {
      [lang: string]: {
        name: string;
      };
    };
  }[];
  roles: string[];
  isVerified: boolean;
  createdAt: string;
  updatedAt: string;
};

export type BadgeShowData = {
  id: number;
  icon: string;
  name?: string;
};

export type CategoryShowData = {
  id: number;
  icon: string;
  name?: string;
};

export type RankShowData = {
  id: number;
  level: number;
  experienceMin: number;
  experienceMax: number;
  name?: string;
};

export type RarityShowData = {
  id: number;
  minExperience: number;
  experienceGain: number;
  name?: string;
};

export type RewardShowData = {
  id: number;
  code: string;
  link: string;
  endDate: string;
  huntId: number;
  title?: string;
};

export type HuntShowData = {
  id: number;
  lat: number;
  lon: number;
  company?: string;
  category?: {
    id: number;
    icon: string;
    name?: string;
  };
  rarity?: {
    id: number;
    minExperience: number;
    experienceGain: number;
    name?: string;
  };
  reward?: {
    id: number;
    code: string;
    link: string;
    endDate: string;
    huntId: number;
    title?: string;
  };
  title?: string;
  description?: string;
  question?: string;
  answer?: string;
  location?: string;
};
