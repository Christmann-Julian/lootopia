import {
  Links,
  Meta,
  Outlet,
  Scripts,
  ScrollRestoration,
  isRouteErrorResponse,
  useRouteError,
  useParams,
  Navigate,
} from "react-router";
import React, { useEffect } from "react";
import "./services/i18n";
import "./assets/css/style.css";
import { getLanguage, getAvailableLanguages } from "./utils/i18nUtils";

export function Layout({
  children,
}: {
  children: React.ReactNode;
}) {

  useEffect(() => {
    document.documentElement.lang = getLanguage();
  }, []);

  return (
    <html lang="en">
      <head>
        <meta charSet="UTF-8" />
        <meta
          name="viewport"
          content="width=device-width, initial-scale=1.0"
        />
        <title>My App</title>
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

export default function Root() {
  const { locale } = useParams()

  if (locale && !getAvailableLanguages().includes(locale)) {
    return <Navigate to={`/${getLanguage()}/not-found`} />
  }

  return <Outlet />;
}

export function ErrorBoundary() {
  const error = useRouteError();
  let message = 'Oops!';
  let details = 'An unexpected error occurred.';
  let stack: string | undefined;

  if (isRouteErrorResponse(error)) {
    message = error.status === 404 ? '404' : 'Error';
    details =
      error.status === 404
        ? 'The requested page could not be found.'
        : error.statusText || details;
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