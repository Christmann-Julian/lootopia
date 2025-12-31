import type { EntryContext } from "react-router";
import { ServerRouter } from "react-router";
import { renderToPipeableStream } from "react-dom/server";
import { isbot } from "isbot";
import { PassThrough } from "node:stream";
import { createReadableStreamFromReadable } from "@react-router/node";
import { createInstance } from "i18next";
import { I18nextProvider, initReactI18next } from "react-i18next";
import Backend from "i18next-fs-backend";
import path from "node:path";
import i18nConfig from "./services/i18n";

const ABORT_DELAY = 5_000;

export default async function handleRequest(
  request: Request,
  responseStatusCode: number,
  responseHeaders: Headers,
  routerContext: EntryContext
) {
  const instance = createInstance();
  const url = new URL(request.url);

  const segments = url.pathname.split("/");
  const lng = i18nConfig.supportedLngs.includes(segments[1]) ? segments[1] : i18nConfig.fallbackLng;

  await instance
    .use(initReactI18next)
    .use(Backend)
    .init({
      ...i18nConfig,
      lng,
      backend: {
        loadPath: path.resolve("./public/locales/{{lng}}/{{ns}}.json"),
      },
    });

  return new Promise((resolve, reject) => {
    let shellRendered = false;
    const userAgent = request.headers.get("user-agent");
    const readyOption = isbot(userAgent) ? "onAllReady" : "onShellReady";

    const { pipe, abort } = renderToPipeableStream(
      <I18nextProvider i18n={instance}>
        <ServerRouter context={routerContext} url={request.url} />
      </I18nextProvider>,
      {
        [readyOption]() {
          shellRendered = true;
          const body = new PassThrough();
          const stream = createReadableStreamFromReadable(body);

          responseHeaders.set("Content-Type", "text/html");

          resolve(
            new Response(stream, {
              status: responseStatusCode,
              headers: responseHeaders,
            })
          );

          pipe(body);
        },
        onShellError(error: unknown) {
          reject(error);
        },
        onError(error: unknown) {
          responseStatusCode = 500;
          if (shellRendered) {
            console.error(error);
          }
        },
      }
    );

    setTimeout(abort, ABORT_DELAY);
  });
}
