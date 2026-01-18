import { useRef } from "react";
import i18n from "i18next";

export function useLanguageSync(lang: string | undefined) {
  // On utilise une ref pour mémoriser la dernière langue demandée.
  // Cela empêche d'appeler i18n.changeLanguage plusieurs fois pour la même langue
  const isChangingTo = useRef<string | null>(null);

  if (!lang || !i18n.isInitialized) return;

  if (i18n.language !== lang && isChangingTo.current !== lang) {
    isChangingTo.current = lang;
    i18n.changeLanguage(lang).catch((err) => {
      console.error("Critical error during language change", err);
      isChangingTo.current = null;
    });
  }
}
