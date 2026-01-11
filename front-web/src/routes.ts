import { type RouteConfig, index, prefix, route } from "@react-router/dev/routes";

export default [
  index("routes/redirect.tsx"),
  ...prefix(":lang", [
    route(undefined, "./routes/auth/guest-layout.tsx", [
      index("./routes/auth/login.tsx"),
      route("register", "./routes/auth/register.tsx"),
      route("register-success", "./routes/auth/register-success.tsx"),
      route("forgot-password", "./routes/auth/forgot-password.tsx"),
      route("reset-password", "./routes/auth/reset-password.tsx"),
    ]),

    route("dashboard", "./routes/auth/auth-layout.tsx", [
      index("./routes/dashboard/dashboard.tsx"),
      route("treasure-hunts", "./routes/dashboard/treasure-hunts.tsx"),
      route("settings", "./routes/dashboard/settings.tsx"),
      route("admin/users", "./routes/dashboard/users.tsx"),
    ]),

    ...prefix("action", [route("change-password", "./routes/action/change-password.tsx")]),

    route("*", "./routes/not-found.tsx"),
  ]),
] satisfies RouteConfig;
