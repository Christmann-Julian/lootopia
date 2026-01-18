export type UserShowData = {
  id: number;
  firstname: string;
  lastname: string;
  email: string;
  company?: string;
  roles: string[];
  isVerified: boolean;
  createdAt: string;
  updatedAt: string;
};
