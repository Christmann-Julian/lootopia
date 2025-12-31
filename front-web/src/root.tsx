import {
  Links,
  Meta,
  Outlet,
  Scripts,
  ScrollRestoration,
  type LinksFunction,
  isRouteErrorResponse,
  useRouteError,
  useParams,
  redirect,
  type LoaderFunctionArgs,
} from "react-router";
import React, { useEffect } from "react";
import i18n from "i18next";
import i18nConfig from "./services/i18n";
import Loading from "./components/Loading";

export const links: LinksFunction = () => [{ rel: "stylesheet", href: "/assets/css/style.css" }];

export function Layout({ children }: { children: React.ReactNode }) {
  const { lang } = useParams();

  useEffect(() => {
    if (lang && i18n.language !== lang) {
      i18n.changeLanguage(lang);
    }
  }, [lang, i18n]);

  return (
    <html lang={lang}>
      <head>
        <meta charSet="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>{i18n.t("appName", { ns: "common" })}</title>
        <Meta />
        <Links />
      </head>
      <body>
        {children}
        <ScrollRestoration />
        <Scripts />
      </body>
    </html>
  );
}

export function HydrateFallback() {
  return <Loading />;
}

export default function Root() {
  return <Outlet />;
}

export async function loader({ request }: LoaderFunctionArgs) {
  const url = new URL(request.url);

  if (url.pathname === "/") {
    return null;
  }

  const segments = url.pathname.split("/");
  const lang = segments[1];

  if (!lang || lang === "") {
    return redirect(`/${i18nConfig.fallbackLng}`);
  }

  if (!i18nConfig.supportedLngs.includes(lang)) {
    throw new Response("Not Found", { status: 404 });
  }

  return { lang };
}

export function ErrorBoundary() {
  const error = useRouteError();
  let message = "Oops!";
  let details = "An unexpected error occurred.";
  let stack: string | undefined;

  if (isRouteErrorResponse(error)) {
    message = error.status === 404 ? "404" : "Error";
    details =
      error.status === 404 ? "The requested page could not be found." : error.statusText || details;
  } else if (error && error instanceof Error) {
    details = error.message;
    stack = error.stack;
  }

  return (
    <main>
      <h1>{message}</h1>
      <p>{details}</p>
      {stack && (
        <pre>
          <code>{stack}</code>
        </pre>
      )}
    </main>
  );
}
