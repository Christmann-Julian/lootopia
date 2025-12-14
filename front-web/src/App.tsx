import cookies from "js-cookie";
import { useTranslation } from "react-i18next";

function App() {
  document.documentElement.lang = cookies.get("i18next") || "en";
  const { t } = useTranslation("common");
  return <div>{t("welcome")}</div>;
}

export default App;
