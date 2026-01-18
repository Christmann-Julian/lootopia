export type Direction = "ltr" | "rtl";

export type Locale = {
  name: string;
  code: string;
  country_code: string;
  locale_code: string;
  dir: Direction;
};

export const locales: Locale[] = [
  {
    name: "English (US)",
    code: "en",
    country_code: "US",
    locale_code: "en-US",
    dir: "ltr",
  },
  {
    name: "Français",
    code: "fr",
    country_code: "FR",
    locale_code: "fr-FR",
    dir: "ltr",
  },
];
