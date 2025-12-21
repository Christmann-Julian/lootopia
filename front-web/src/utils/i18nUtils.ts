import i18nNext from "../services/i18n";

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

/**
 * @returns Liste des codes de langues disponibles (ex: ['en', 'fr'])
 */
export const getAvailableLanguages = (): string[] => locales.map((locale) => locale.code);

/**
 * @returns Liste des noms de langues disponibles (ex: ['English', 'Français'])
 */
export const getAvailableLanguagesNames = (): string[] => locales.map((locale) => locale.name);

/**
 * @return le code de la langue actuelle (ex: 'en', 'fr'), ou 'en' si non défini
 */
export const getLanguage = (): string => i18nNext.language;

/**
 * @param code Le code de la langue (ex: 'en', 'fr')
 * @return le nom de la langue (ex: 'English', 'Français') ou English si le code n'est pas trouvé
 */
export const getLanguageNameByCode = (code: string): string =>
  getLocaleByCode(code)?.name || "English";

/**
 * @param countryCode Le code du pays (ex: 'US', 'FR')
 * @returns Le code du pays de la langue actuelle (ex: 'US', 'FR'), ou null si non trouvé
 */
export const getLanguageByCountryCode = (countryCode: string): string | null => {
  const locale = locales.find(
    (locale) => locale.country_code.toLowerCase() === countryCode.toLowerCase()
  );
  return locale ? locale.code : null;
};

/**
 * @param localeCode Le code de la locale (ex: 'en-US', 'fr-FR')
 * @returns Le code de la langue (ex: 'en', 'fr') à partir du code de la locale, ou null si non trouvé
 */
export const getLanguageByLocaleCode = (localeCode: string): string | null => {
  const locale = locales.find(
    (locale) => locale.locale_code.toLowerCase() === localeCode.toLowerCase()
  );
  return locale ? locale.code : null;
};

/**
 * Change la langue de l'application, met à jour la direction du document et le path de l'URL
 * @param langCode Le code de la langue à changer (ex: 'en', 'fr')
 */
export const changeLanguage = async (langCode: string): Promise<void> => {
  await i18nNext.changeLanguage(langCode);
  document.documentElement.dir = getDirection();

  const url = new URL(window.location.href);
  url.pathname = `/${langCode}${url.pathname.replace(/^\/[^/]+/, "")}`;
  window.history.pushState({}, "", url.toString());

  // window.location.reload();
};

/**
 * Change la langue de l'application en utilisant le code de la locale (ex: 'en-US', 'fr-FR')
 * @param localeCode Le code de la locale à changer (ex: 'en-US', 'fr-FR')
 */
export const changeLanguageByLocaleCode = async (localeCode: string): Promise<void> => {
  const locale = getLocaleByCode(localeCode);
  if (locale) {
    await changeLanguage(locale.code);
  }
};

/**
 * @returns la locale actuelle complète (ex: { name: 'English', code: 'en',  country_code: 'US', locale_code: 'en-US', dir: 'ltr' })
 */
export const getCurrentLocale = (): Locale => {
  return locales.find((locale) => locale.code === i18nNext.language) ?? locales[0];
};

/**
 * @param code Le code de la langue (ex: 'en', 'fr')
 * @returns la locale complète à partir d'un code langue, ou null si non trouvée
 */
export const getLocaleByCode = (code: string): Locale | null =>
  locales.find((locale) => locale.code === code) || null;

/**
 * @return la direction de la langue actuelle ('ltr' ou 'rtl')
 * Si la direction n'est pas définie, retourne 'ltr' par défaut
 */
export const getDirection = (): Direction => {
  return getCurrentLocale().dir ?? "ltr";
};

/**
 * Vérifie si la langue actuelle est écrite de droite à gauche (RTL)
 * @return true si la langue est RTL, false sinon
 */
export const isRTL = (): boolean => getDirection() === "rtl";
