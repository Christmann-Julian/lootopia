import {
  Compass,
  Target,
  Star,
  Hexagon,
  Flame,
  Trophy,
  Zap,
  Shield,
  Hamburger,
  Shirt,
  ShoppingBasket,
  Dumbbell,
  Cpu,
  Gamepad2,
  HeartHandshake,
  Landmark,
} from "lucide-react";
import type { JSX } from "react";

export const getBadgeIcon = (
  iconName: string,
  size: number = 24,
  color: string = "currentColor"
) => {
  const icons: Record<string, JSX.Element> = {
    compass: <Compass size={size} color={color} />,
    target: <Target size={size} color={color} />,
    star: <Star size={size} color={color} />,
    flame: <Flame size={size} color={color} />,
    trophy: <Trophy size={size} color={color} />,
    zap: <Zap size={size} color={color} />,
    shield: <Shield size={size} color={color} />,
    hamburger: <Hamburger size={size} color={color} />,
    shirt: <Shirt size={size} color={color} />,
    retail: <ShoppingBasket size={size} color={color} />,
    sport: <Dumbbell size={size} color={color} />,
    tech: <Cpu size={size} color={color} />,
    entertainment: <Gamepad2 size={size} color={color} />,
    charity: <HeartHandshake size={size} color={color} />,
    culture: <Landmark size={size} color={color} />,
  };

  const normalizedName = iconName?.toLowerCase();
  return icons[normalizedName] || <Hexagon size={size} color={color} />;
};
