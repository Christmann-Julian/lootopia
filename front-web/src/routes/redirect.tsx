import { useEffect } from "react";
import { useNavigate } from "react-router";
import i18nConfig from "../services/i18n";
import Loading from "../components/Loading";

export default function RedirectToLocale() {
  const navigate = useNavigate();

  useEffect(() => {
    const browserLang = navigator.language.split("-")[0];

    const targetLang = i18nConfig.supportedLngs.includes(browserLang)
      ? browserLang
      : i18nConfig.fallbackLng;

    navigate(`/${targetLang}`);
  }, [navigate]);

  return <Loading />;
}
